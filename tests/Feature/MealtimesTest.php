<?php

namespace Tests\Feature;

use App\Models\Mealtime;
use App\Models\User;
use Database\Seeders\MealtimesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealtimesTest extends TestCase
{
    use RefreshDatabase;

    protected array $testMealtimeData = [
        'title' => 'Brunch',
        'percent_from' => 10,
        'percent_to' => 20,
    ];

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

    public function test_unauthenticated_user_can_not_save_mealtime()
    {
        $response = $this->post(route('mealtimes.save'), $this->testMealtimeData);
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_save_and_delete_mealtime()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('mealtimes.save'), $this->testMealtimeData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('mealtimes', [
            'percent_from' => 10,
            'percent_to' => 20,
        ]);
        $this->assertDatabaseHas('mealtimes_translations', ['translation' => 'Brunch']);

        $mealtime = Mealtime::query()->where('percent_from', '=', 10)
            ->where('percent_to', '=', 20)
            ->first();

        $response = $this->actingAs($user)->post(route('mealtimes.delete'), ['id' => $mealtime->id]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('mealtimes', [
            'percent_from' => 10,
            'percent_to' => 20,
        ]);
        $this->assertDatabaseMissing('mealtimes_translations', ['translation' => 'Brunch']);
    }
}
