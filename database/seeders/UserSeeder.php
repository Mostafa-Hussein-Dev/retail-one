<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default manager user
        User::create([
            'name' => 'Manager',
            'username' => 'Manager',
            'password' => Hash::make('manager123'),
            'role' => 'manager',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Cashier',
            'username' => 'cashier',
            'password' => Hash::make('cashier123'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $this->command->info('Default manager user created successfully!');
        $this->command->line('Manager: admin / 123456');
    }
}
