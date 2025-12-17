<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'lamaalhalaqi372@gmail.com',
            'password' => bcrypt('123456789'),
        ]);

        // Create manager user
        User::factory()->manager()->create([
            'name' => 'Managerlama',
            'email' => 'manager@gmail.com',
            'password' => bcrypt('password123'),
        ]);

        // Create teller user
        User::factory()->teller()->create([
            'name' => 'Tellerloura',
            'email' => 'teller@gmail.com',
            'password' => bcrypt('password123'),
        ]);

        // Create regular customers
        User::factory(10)->create();

        // Create accounts for some users
        $users = User::where('role', 'customer')->take(5)->get();
        foreach ($users as $user) {
            Account::factory(2)->create(['user_id' => $user->id]);
        }

        // Create some transactions
        $accounts = Account::take(10)->get();
        foreach ($accounts as $account) {
            Transaction::factory(5)->create(['account_id' => $account->id]);
        }
    }
}
