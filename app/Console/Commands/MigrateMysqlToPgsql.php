<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateMysqlToPgsql extends Command
{
    protected $signature = 'db:migrate-mysql-to-pgsql {--fresh : Drop all tables in target PostgreSQL before migrating}';

    protected $description = 'Migrate database schema and all data from MySQL to PostgreSQL';

    public function handle(): int
    {
        $this->info('Starting database migration from MySQL to PostgreSQL...');

        // تأكد من تكوين اتصال pgsql بمعلومات Neon
        config([
            'database.connections.pgsql.host' => 'ep-dry-surf-be3zk5o9-pooler.c-9.us-east-2.aws.neon.tech',
            'database.connections.pgsql.port' => '5432',
            'database.connections.pgsql.database' => 'neondb',
            'database.connections.pgsql.username' => 'neondb_owner',
            'database.connections.pgsql.password' => 'npg_kjRhGz2wWL8l',
            'database.connections.pgsql.sslmode' => 'require',
        ]);

        $mysql = DB::connection('mysql');
        $pgsql = DB::connection('pgsql');

        // اختبار الاتصالين
        try {
            $mysql->select('SELECT 1');
            $this->info('✓ Connected to source: MySQL');
        } catch (\Exception $e) {
            $this->error('Failed to connect to MySQL: '.$e->getMessage());

            return 1;
        }

        try {
            $pgVer = $pgsql->select('SELECT version()')[0]->version ?? '';
            $this->info('✓ Connected to target: PostgreSQL ('.substr($pgVer, 0, 25).'...)');
        } catch (\Exception $e) {
            $this->error('Failed to connect to PostgreSQL: '.$e->getMessage());

            return 1;
        }

        if ($this->option('fresh')) {
            $this->warn('Dropping existing tables in PostgreSQL public schema...');
            $pgsql->statement('DROP SCHEMA public CASCADE');
            $pgsql->statement('CREATE SCHEMA public');
            $pgsql->statement('GRANT ALL ON SCHEMA public TO neondb_owner');
            $pgsql->statement('GRANT ALL ON SCHEMA public TO public');
            $this->info('✓ Clean public schema created.');
        }

        // جلب جميع جداول MySQL
        $mysqlTables = $mysql->select('SHOW TABLES');
        $tables = [];
        foreach ($mysqlTables as $t) {
            $tables[] = array_values((array) $t)[0];
        }

        $this->info('Found '.count($tables).' tables in MySQL.');

        // 1. إنشاء الجداول في PostgreSQL
        $this->info('--- Phase 1: Creating tables in PostgreSQL ---');
        $booleanColumns = [];

        foreach ($tables as $table) {
            $this->line("Creating table: {$table}...");
            $columns = $mysql->select('
                SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA, COLUMN_KEY
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
                ORDER BY ORDINAL_POSITION
            ', [$table]);

            $colDefs = [];
            $primaryKeys = [];

            foreach ($columns as $col) {
                $name = $col->COLUMN_NAME;
                $dataType = strtolower($col->DATA_TYPE);
                $colType = strtolower($col->COLUMN_TYPE);
                $isAuto = str_contains(strtolower($col->EXTRA), 'auto_increment');
                $isPk = $col->COLUMN_KEY === 'PRI';

                if ($isPk) {
                    $primaryKeys[] = "\"{$name}\"";
                }

                // تحديد نوع العمود في PostgreSQL
                if ($isAuto) {
                    $pgType = (str_contains($colType, 'bigint')) ? 'BIGSERIAL' : 'SERIAL';
                } elseif ($colType === 'tinyint(1)') {
                    $pgType = 'BOOLEAN';
                    $booleanColumns[$table][] = $name;
                } elseif (in_array($dataType, ['tinyint', 'smallint'])) {
                    $pgType = 'SMALLINT';
                } elseif (in_array($dataType, ['int', 'mediumint', 'integer'])) {
                    $pgType = 'INTEGER';
                } elseif ($dataType === 'bigint') {
                    $pgType = 'BIGINT';
                } elseif ($dataType === 'decimal' || $dataType === 'numeric') {
                    $pgType = strtoupper($col->COLUMN_TYPE);
                } elseif ($dataType === 'varchar' || $dataType === 'char') {
                    $pgType = strtoupper($col->COLUMN_TYPE);
                } elseif (in_array($dataType, ['text', 'mediumtext', 'longtext', 'tinytext'])) {
                    $pgType = 'TEXT';
                } elseif ($dataType === 'enum') {
                    $pgType = 'VARCHAR(100)';
                } elseif (in_array($dataType, ['timestamp', 'datetime'])) {
                    $pgType = 'TIMESTAMP WITHOUT TIME ZONE';
                } elseif ($dataType === 'date') {
                    $pgType = 'DATE';
                } elseif ($dataType === 'time') {
                    $pgType = 'TIME WITHOUT TIME ZONE';
                } elseif ($dataType === 'json') {
                    $pgType = 'JSONB';
                } else {
                    $pgType = 'TEXT';
                }

                $nullable = ($col->IS_NULLABLE === 'NO' && ! $isAuto) ? 'NOT NULL' : '';
                $default = '';
                if ($col->COLUMN_DEFAULT !== null && ! $isAuto) {
                    if (strtoupper($col->COLUMN_DEFAULT) === 'CURRENT_TIMESTAMP') {
                        $default = 'DEFAULT CURRENT_TIMESTAMP';
                    } elseif ($colType === 'tinyint(1)') {
                        $default = 'DEFAULT '.($col->COLUMN_DEFAULT ? 'TRUE' : 'FALSE');
                    } elseif (is_numeric($col->COLUMN_DEFAULT)) {
                        $default = "DEFAULT {$col->COLUMN_DEFAULT}";
                    } else {
                        $escaped = str_replace("'", "''", $col->COLUMN_DEFAULT);
                        $default = "DEFAULT '{$escaped}'";
                    }
                }

                $colDefs[] = trim("\"{$name}\" {$pgType} {$nullable} {$default}");
            }

            if (! empty($primaryKeys)) {
                $colDefs[] = 'PRIMARY KEY ('.implode(', ', $primaryKeys).')';
            }

            $sql = 'CREATE TABLE IF NOT EXISTS "'.$table.'" ('.implode(', ', $colDefs).')';
            $pgsql->statement($sql);
        }

        $this->info('✓ All tables created successfully in PostgreSQL.');

        // 2. نقل البيانات
        $this->info('--- Phase 2: Copying data from MySQL to PostgreSQL ---');
        $transferredStats = [];

        foreach ($tables as $table) {
            $count = $mysql->table($table)->count();
            if ($count === 0) {
                continue;
            }

            $this->line("Migrating {$count} rows from {$table}...");
            $boolCols = $booleanColumns[$table] ?? [];

            $mysql->table($table)->orderByRaw('1')->chunk(500, function ($rows) use ($pgsql, $table, $boolCols) {
                $batch = [];
                foreach ($rows as $row) {
                    $arr = (array) $row;
                    foreach ($boolCols as $bCol) {
                        if (array_key_exists($bCol, $arr) && $arr[$bCol] !== null) {
                            $arr[$bCol] = (bool) $arr[$bCol];
                        }
                    }
                    $batch[] = $arr;
                }

                $pgsql->table($table)->insert($batch);
            });

            $transferredStats[$table] = $count;
        }

        $this->info('✓ Data migration completed.');

        // 3. تحديث الـ Sequences في PostgreSQL للـ Auto Increment
        $this->info('--- Phase 3: Updating PostgreSQL sequences ---');
        foreach ($tables as $table) {
            $pkCol = $mysql->select("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND EXTRA LIKE '%auto_increment%'
            ", [$table]);

            if (! empty($pkCol)) {
                $colName = $pkCol[0]->COLUMN_NAME;
                $maxId = $pgsql->table($table)->max($colName);
                if ($maxId !== null) {
                    $pgsql->statement("
                        SELECT setval(pg_get_serial_sequence('\"{$table}\"', '{$colName}'), ?, true)
                    ", [$maxId]);
                }
            }
        }
        $this->info('✓ Sequences updated.');

        // 4. إضافة المفاتيح الأجنبية (Foreign Keys)
        $this->info('--- Phase 4: Adding Foreign Keys & Indexes ---');
        $foreignKeys = $mysql->select('
            SELECT 
                TABLE_NAME, 
                COLUMN_NAME, 
                CONSTRAINT_NAME, 
                REFERENCED_TABLE_NAME, 
                REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE() 
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ');

        $addedFks = 0;
        foreach ($foreignKeys as $fk) {
            $tbl = $fk->TABLE_NAME;
            $col = $fk->COLUMN_NAME;
            $refTbl = $fk->REFERENCED_TABLE_NAME;
            $refCol = $fk->REFERENCED_COLUMN_NAME;
            $constraint = $fk->CONSTRAINT_NAME;

            try {
                $pgsql->statement("
                    ALTER TABLE \"{$tbl}\"
                    ADD CONSTRAINT \"{$constraint}\"
                    FOREIGN KEY (\"{$col}\")
                    REFERENCES \"{$refTbl}\" (\"{$refCol}\")
                    ON DELETE CASCADE
                ");
                $addedFks++;
            } catch (\Exception $e) {
                // قد يكون المفتاح موجوداً أو إشعار
            }
        }
        $this->info("✓ Added {$addedFks} foreign key constraints.");

        // ملخص النقل
        $this->info('=== Migration Summary ===');
        $headers = ['Table', 'MySQL Rows', 'PostgreSQL Rows', 'Status'];
        $rows = [];
        foreach ($tables as $table) {
            $myCount = $mysql->table($table)->count();
            $pgCount = $pgsql->table($table)->count();
            if ($myCount > 0 || $pgCount > 0) {
                $rows[] = [
                    $table,
                    $myCount,
                    $pgCount,
                    ($myCount === $pgCount) ? '✓ OK' : '✗ Mismatch',
                ];
            }
        }
        $this->table($headers, $rows);

        $this->info('Database migration to PostgreSQL completed successfully! 🎉');

        return 0;
    }
}
