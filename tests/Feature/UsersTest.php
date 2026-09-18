<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_user_can_be_updated_without_password(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'roles' => User::ROLE_SUPERADMIN,
        ]);

        $user = User::factory()->create([
            'name' => 'Jane User',
            'email' => 'jane@example.com',
            'roles' => User::ROLE_DIETOLOGIST,
        ]);

        $response = $this
            ->actingAs($admin)
            ->postJson(route('users.save'), [
                'id' => $user->id,
                'name' => 'Jane Updated',
                'email' => 'jane.updated@example.com',
                'roles' => User::ROLE_DIETOLOGIST,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('result', 'success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Jane Updated',
            'email' => 'jane.updated@example.com',
        ]);
    }

    public function test_new_user_requires_matching_password_confirmation(): void
    {
        $admin = User::factory()->create([
            'roles' => User::ROLE_SUPERADMIN,
        ]);

        $response = $this
            ->actingAs($admin)
            ->postJson(route('users.save'), [
                'name' => 'New User',
                'email' => 'new.user@example.com',
                'roles' => User::ROLE_DIETOLOGIST,
                'password' => 'password123',
                'password_confirmation' => 'different-password',
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
