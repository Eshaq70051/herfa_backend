<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserClientAccountsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('phone');
            $table->string('password');
            $table->string('role');
            $table->string('current_mode');
            $table->timestamps();
        });
    }

    public function test_client_accounts_include_dual_mode_craftsmen_but_exclude_admins(): void
    {
        $customer = User::create([
            'full_name' => 'Customer',
            'phone' => '0500000001',
            'password' => 'password',
            'role' => 'customer',
            'current_mode' => 'craftsman',
        ]);
        $craftsmanInClientMode = User::create([
            'full_name' => 'Dual-mode craftsman',
            'phone' => '0500000002',
            'password' => 'password',
            'role' => 'craftsman',
            'current_mode' => 'customer',
        ]);
        User::create([
            'full_name' => 'Craftsman',
            'phone' => '0500000003',
            'password' => 'password',
            'role' => 'craftsman',
            'current_mode' => 'craftsman',
        ]);
        User::create([
            'full_name' => 'Admin',
            'phone' => '0500000004',
            'password' => 'password',
            'role' => 'admin',
            'current_mode' => 'customer',
        ]);

        $clientIds = User::clientAccounts()->orderBy('id')->pluck('id')->all();

        $this->assertSame([$customer->id, $craftsmanInClientMode->id], $clientIds);
    }
}
