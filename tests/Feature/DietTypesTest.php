<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DietTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DietTypesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_can_not_see_diet_types()
    {
        $response = $this->get(route('diet_types.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('diet_types.json_list'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_see_diet_types()
    {
        $user = User::factory()->create();

        $this->seed(DietTypesSeeder::class);

        $response = $this->actingAs($user)->get(route('diet_types.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('diet_types.json_list'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('diet_types.json_list', [
            'order_by_field' => 'title',
            'order_by_direction' => 'asc',
            'page' => 1,
            'paginate_by' => 10,
        ]));
        $response->assertStatus(200);
    }
}
