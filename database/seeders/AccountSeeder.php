<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\User;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all customers
        $customers = User::where('role', 'customer')->get();

        foreach ($customers as $customer) {
            // Create different types of accounts for each customer
            Account::factory()->savings()->create([
                'user_id' => $customer->id,
                'balance' => rand(1000, 50000),
            ]);

            Account::factory()->checking()->create([
                'user_id' => $customer->id,
                'balance' => rand(500, 20000),
            ]);

            // Some customers might have loan accounts
            if (rand(0, 1)) {
                Account::factory()->loan()->create([
                    'user_id' => $customer->id,
                    'balance' => -rand(5000, 50000), // Negative balance for loans
                ]);
            }

            // Some customers might have investment accounts
            if (rand(0, 2) == 0) {
                Account::factory()->investment()->create([
                    'user_id' => $customer->id,
                    'balance' => rand(10000, 100000),
                ]);
            }
        }
    }
}
