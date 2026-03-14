<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class EatersTest extends TestCase
{
    public function test_unauthenticated_user_can_not_see_eaters()
    {
        $response = $this->get(route('eaters.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('eaters.json_list'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_see_eaters()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('eaters.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('eaters.json_list'));
        $response->assertStatus(200);
    }
}
