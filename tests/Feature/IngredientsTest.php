<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\User;
use Database\Seeders\IngredientsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientsTest extends TestCase
{
    use RefreshDatabase;

    protected array $testIngredientData = [
        'proteins' => 0.1,
        'fat' => 0.1,
        'carbohydrates' => 0.1,
        'calories' => 9,
        'title' => 'Test ingredient',
    ];

    public function test_unauthenticated_user_can_not_see_ingredients()
    {
        $response = $this->get(route('ingredients.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('ingredients.json_list'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_see_ingredients()
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

    public function test_unauthenticated_user_can_not_save_ingredient()
    {
        $response = $this->post(route('ingredients.save'), $this->testIngredientData);
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_save_and_delete_ingredient()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('ingredients.save'), $this->testIngredientData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('ingredients', [
            'proteins' => 0.1,
            'fat' => 0.1,
            'carbohydrates' => 0.1,
            'calories' => 9,
        ]);
        $this->assertDatabaseHas('ingredients_translations', ['translation' => 'Test ingredient']);

        $ingredient = Ingredient::query()->where('proteins', '=', 0.1)
            ->where('fat', '=', 0.1)
            ->where('carbohydrates', '=', 0.1)
            ->where('calories', '=', 9)
            ->first();

        $response = $this->actingAs($user)->post(route('ingredients.delete'), ['id' => $ingredient->id]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('ingredients', [
            'proteins' => 0.1,
            'fat' => 0.1,
            'carbohydrates' => 0.1,
            'calories' => 9,
        ]);
        $this->assertDatabaseMissing('ingredients_translations', ['translation' => 'Test ingredient']);
    }
}
