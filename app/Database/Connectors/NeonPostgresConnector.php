<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector;

class NeonPostgresConnector extends PostgresConnector
{
    /**
     * Get the PDO options based on the configuration.
     * Ensures string options (like Neon endpoint options from DATABASE_URL) do not cause TypeError in array_diff_key.
     *
     * @return array
     */
    public function getOptions(array $config)
    {
        $options = $config['options'] ?? [];

        if (is_string($options)) {
            $options = [];
        }

        return array_diff_key($this->options, $options) + $options;
    }

    /**
     * Create a DSN string from a configuration, ensuring Neon pooler endpoint options are included.
     *
     * @return string
     */
    protected function getDsn(array $config)
    {
        $dsn = parent::getDsn($config);

        // If options are already present in DSN, do not duplicate
        if (str_contains($dsn, 'options=')) {
            return $dsn;
        }

        $endpointOption = null;

        if (! empty($config['endpoint'])) {
            $endpointOption = 'endpoint='.(string) $config['endpoint'];
        } elseif (! empty($config['options']) && is_string($config['options'])) {
            $endpointOption = (string) $config['options'];
        } elseif (! empty($config['host']) && str_contains($config['host'], 'neon.tech')) {
            $parts = explode('.', $config['host']);
            if (! empty($parts[0])) {
                $endpointOption = 'endpoint='.$parts[0];
            }
        }

        if ($endpointOption !== null && $endpointOption !== '') {
            $dsn .= ";options='".str_replace("'", "\'", $endpointOption)."'";
        }

        return $dsn;
    }
}
