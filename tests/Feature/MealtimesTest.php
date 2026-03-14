<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\MealtimesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealtimesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_can_not_see_mealtimes()
    {
        $response = $this->get(route('mealtimes.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('mealtimes.json_list'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_see_mealtimes()
    {
        $user = User::factory()->create();

        $this->seed(MealtimesSeeder::class);

        $response = $this->actingAs($user)->get(route('mealtimes.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('mealtimes.json_list'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('mealtimes.json_list', [
            'order_by_field' => 'title',
            'order_by_direction' => 'asc',
            'page' => 1,
            'paginate_by' => 10,
        ]));
        $response->assertStatus(200);
    }
}
