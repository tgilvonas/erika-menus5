<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DishesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_can_not_see_dishes()
    {
        $response = $this->get(route('dishes.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('dishes.json_list'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_see_dishes()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dishes.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('dishes.json_list'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('dishes.json_list', [
            'order_by_field' => 'title',
            'order_by_direction' => 'asc',
            'page' => 1,
            'paginate_by' => 10,
        ]));
        $response->assertStatus(200);
    }
}
