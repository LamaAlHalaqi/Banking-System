<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;

class TransactionApprovalTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_initiate_transaction_but_cannot_approve_it()
    {
        // Create a customer
        $customer = User::factory()->create(['role' => 'customer']);

        // Create an account for the customer
        $account = Account::factory()->create([
            'user_id' => $customer->id,
            'balance' => 1000,
        ]);

        // Customer initiates a withdrawal
        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/accounts/{$account->id}/withdraw", [
                'amount' => 100,
                'description' => 'Test withdrawal'
            ]);

        $response->assertStatus(200);

        // Check that transaction was created with pending status
        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->id,
            'amount' => 100,
            'type' => 'withdrawal',
            'status' => 'pending'
        ]);

        // Get the transaction
        $transaction = Transaction::first();

        // Customer should not be able to approve their own transaction
        $response = $this->actingAs($customer, 'sanctum')
            ->postJson("/api/admin/approve-transaction/{$transaction->id}");

        $response->assertStatus(403); // Forbidden

        // Balance should not have changed yet
        $this->assertEquals(1000, $account->fresh()->balance);
    }

    /** @test */
    public function admin_can_approve_pending_transaction()
    {
        // Create a customer and admin
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Create an account for the customer
        $account = Account::factory()->create([
            'user_id' => $customer->id,
            'balance' => 1000,
        ]);

        // Customer initiates a withdrawal
        $this->actingAs($customer, 'sanctum')
            ->postJson("/api/accounts/{$account->id}/withdraw", [
                'amount' => 100,
                'description' => 'Test withdrawal'
            ]);

        // Get the transaction
        $transaction = Transaction::first();

        // Admin approves the transaction
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/approve-transaction/{$transaction->id}");

        $response->assertStatus(200);

        // Check that transaction status was updated
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'completed',
            'approved_by' => $admin->id
        ]);

        // Balance should have been updated
        $this->assertEquals(900, $account->fresh()->balance);
    }

    /** @test */
    public function manager_can_approve_pending_transaction()
    {
        // Create a customer and manager
        $customer = User::factory()->create(['role' => 'customer']);
        $manager = User::factory()->create(['role' => 'manager']);

        // Create an account for the customer
        $account = Account::factory()->create([
            'user_id' => $customer->id,
            'balance' => 1000,
        ]);

        // Customer initiates a deposit
        $this->actingAs($customer, 'sanctum')
            ->postJson("/api/accounts/{$account->id}/deposit", [
                'amount' => 200,
                'description' => 'Test deposit'
            ]);

        // Get the transaction
        $transaction = Transaction::first();

        // Manager approves the transaction
        $response = $this->actingAs($manager, 'sanctum')
            ->postJson("/api/manager/approve-transaction/{$transaction->id}");

        $response->assertStatus(200);

        // Check that transaction status was updated
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'completed',
            'approved_by' => $manager->id
        ]);

        // Balance should have been updated
        $this->assertEquals(1200, $account->fresh()->balance);
    }

    /** @test */
    public function transaction_approval_updates_balances_correctly_for_transfer()
    {
        // Create customers, admin
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Create accounts
        $account1 = Account::factory()->create([
            'user_id' => $customer1->id,
            'balance' => 1000,
        ]);

        $account2 = Account::factory()->create([
            'user_id' => $customer2->id,
            'balance' => 500,
        ]);

        // Customer initiates a transfer
        $response = $this->actingAs($customer1, 'sanctum')
            ->postJson("/api/accounts/transfer", [
                'from_account_id' => $account1->id,
                'to_account_id' => $account2->id,
                'amount' => 300,
                'description' => 'Test transfer'
            ]);

        $response->assertStatus(200);

        // Get the transaction
        $transaction = Transaction::first();

        // Admin approves the transaction
        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/approve-transaction/{$transaction->id}");

        // Balances should have been updated correctly
        $this->assertEquals(700, $account1->fresh()->balance);
        $this->assertEquals(800, $account2->fresh()->balance);
    }
}
