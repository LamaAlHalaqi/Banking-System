<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\User;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all accounts
        $accounts = Account::all();

        foreach ($accounts as $account) {
            // Create various types of transactions for each account
            Transaction::factory()->deposit()->create([
                'account_id' => $account->id,
                'amount' => rand(100, 5000),
                'initiated_by' => $account->user_id,
            ]);

            Transaction::factory()->withdrawal()->create([
                'account_id' => $account->id,
                'amount' => rand(50, 2000),
                'initiated_by' => $account->user_id,
            ]);

            // Some accounts might have transfer transactions
            if (rand(0, 1)) {
                $destinationAccount = Account::where('id', '!=', $account->id)->inRandomOrder()->first();
                if ($destinationAccount) {
                    Transaction::factory()->transfer()->create([
                        'account_id' => $account->id,
                        'destination_account_id' => $destinationAccount->id,
                        'amount' => rand(100, 3000),
                        'initiated_by' => $account->user_id,
                    ]);
                }
            }

            // Some accounts might have payment transactions
            if (rand(0, 2) == 0) {
                Transaction::factory()->payment()->create([
                    'account_id' => $account->id,
                    'amount' => rand(50, 1000),
                    'initiated_by' => $account->user_id,
                ]);
            }
        }
    }
}
