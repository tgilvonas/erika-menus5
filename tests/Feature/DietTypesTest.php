<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DietTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DietTypesTest extends TestCase
{
    use RefreshDatabase;

    protected array $testDietTypeData = [
        'title' => 'Test Diet Type',
        'proteins_min' => 10,
        'proteins_max' => 20,
        'fat_min' => 30,
        'fat_max' => 40,
        'carbohydrates_min' => 50,
        'carbohydrates_max' => 60,
        'calories_min' => 1500,
        'calories_max' => 2000,
    ];

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

    public function test_unauthenticated_user_can_not_save_diet_type()
    {
        $response = $this->post(route('ingredients.save'), $this->testDietTypeData);
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_save_diet_type()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('diet_types.save'), $this->testDietTypeData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('diet_types', [
            'proteins_min' => 10,
            'proteins_max' => 20,
            'fat_min' => 30,
            'fat_max' => 40,
            'carbohydrates_min' => 50,
            'carbohydrates_max' => 60,
            'calories_min' => 1500,
            'calories_max' => 2000,
        ]);
        $this->assertDatabaseHas('diet_types_translations', ['translation' => 'Test Diet Type']);
    }
}
