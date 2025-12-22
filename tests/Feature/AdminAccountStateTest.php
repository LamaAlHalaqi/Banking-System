<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Account;

class AdminAccountStateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_list_all_user_accounts()
    {
        // Create admin user
        $admin = User::factory()->create(['role' => 'admin']);

        // Create some regular users with accounts
        $user1 = User::factory()->create(['role' => 'customer']);
        $account1 = Account::factory()->create([
            'user_id' => $user1->id,
            'balance' => 1000,
        ]);

        $user2 = User::factory()->create(['role' => 'customer']);
        $account2 = Account::factory()->create([
            'user_id' => $user2->id,
            'balance' => 2000,
        ]);

        // Make request as admin
        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/accounts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'account_number',
                    'account_type',
                    'balance',
                    'state',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role'
                    ]
                ]
            ],
            'links',
            'meta'
        ]);

        // Should contain both accounts
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function non_admin_cannot_list_all_accounts()
    {
        // Create a regular user
        $user = User::factory()->create(['role' => 'customer']);

        // Try to access admin endpoint
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/accounts');

        // Should be forbidden
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_freeze_account()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $account = Account::factory()->create([
            'user_id' => $customer->id,
            'balance' => 1000,
            'state' => 'active'
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/accounts/{$account->id}/state", [
                'state' => 'frozen'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'state' => 'frozen'
        ]);
    }

    /** @test */
    public function admin_can_suspend_account()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $account = Account::factory()->create([
            'user_id' => $customer->id,
            'balance' => 1000,
            'state' => 'active'
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/accounts/{$account->id}/state", [
                'state' => 'suspended'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'state' => 'suspended'
        ]);
    }

    /** @test */
    public function admin_can_unfreeze_account()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $account = Account::factory()->create([
            'user_id' => $customer->id,
            'balance' => 1000,
            'state' => 'frozen'
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/accounts/{$account->id}/state", [
                'state' => 'active'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'state' => 'active'
        ]);
    }
}
