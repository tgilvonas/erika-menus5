<?php

use App\Models\User;
use Database\Seeders\IngredientsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientsTest extends TestCase
{
    use RefreshDatabase;

    public function test_if_unauthenticated_user_can_not_see_ingredients()
    {
        $response = $this->get(route('ingredients.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('ingredients.json_list'));
        $response->assertRedirect(route('login'));
    }

    public function test_if_authenticated_user_can_see_ingredients()
    {
        $user = User::factory()->create();

        $this->seed(IngredientsSeeder::class);

        $response = $this->actingAs($user)->get(route('ingredients.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('ingredients.json_list'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('ingredients.json_list', [
            'order_by_field' => 'title',
            'order_by_direction' => 'asc',
            'page' => 1,
            'paginate_by' => 10,
        ]));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('ingredients.json_list', [
            'order_by_field' => 'title',
            'order_by_direction' => 'asc',
            'page' => 1,
            'paginate_by' => 10,
            'search_text' => 'Duona',
        ]));
        $response->assertStatus(200);
    }
}
