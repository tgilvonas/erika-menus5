<?php

namespace App\Http\Controllers;

use App\Http\Requests\IngredientRequest;
use App\Models\Ingredient;
use App\Models\IngredientTranslation;
use App\Repositories\IngredientsRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Ai\AiManager;
use Laravel\Ai\AnonymousAgent;

class IngredientsController
{
    public function index(): Response
    {
        return Inertia::render('Ingredients');
    }

    public function getJsonList()
    {
        return IngredientsRepository::getListWithTranslations(
            'lt',
            request('order_by_field'),
            request('order_by_direction'),
            request('search_text'),
            request('paginate_by')
        );
    }

    public function getIngredient(Request $request, $id)
    {
        return IngredientsRepository::getIngredientWithTranslation($id);
    }

    public function save(IngredientRequest $request)
    {
        if (is_numeric(request('id'))) {
            $ingredient = Ingredient::find(request('id'));
            $ingredientTranslation = IngredientTranslation::query()->where('ingredient_id', '=', request('id'))->first();
            $msgPart = __('general.updated');
        } else {
            $ingredientExists = IngredientTranslation::query()->where('translation', request('title'))->get();
            if (count($ingredientExists)>0) {
                return [
                    'result'=>'ingredient_exists',
                    'message'=>__('general.ingredient').' "'.request('title').'" '.__('general.already_exists')
                ];
            }

            $ingredient = new Ingredient();
            $ingredientTranslation = new IngredientTranslation();
            $msgPart = __('general.created');
        }

        $ingredient->ingredient_title = $request->title;
        $ingredient->proteins = $request->proteins;
        $ingredient->fat = $request->fat;
        $ingredient->carbohydrates = $request->carbohydrates;
        $ingredient->calories = $request->calories;
        $ingredient->save();

        $ingredientTranslation->ingredient_id = $ingredient->id;
        $ingredientTranslation->translation = $request->title;
        $ingredientTranslation->lang = 'lt'; // temporarily
        $ingredientTranslation->save();

        return [
            'result' => 'Success',
            'message' => __('general.ingredient').' "'.$ingredientTranslation->translation.'" '.$msgPart,
            'ingredient' => $ingredient,
        ];
    }

    public function delete()
    {
        $ingredientTranslation = IngredientTranslation::query()->where('ingredient_id', request('id'))
            ->where('lang', 'lt')
            ->first();
        $ingredientTitle = $ingredientTranslation->translation;

        $ingredientExistsInDishes = DB::table('dishes_ingredients')->where('ingredient_id', request('id'))->get();
        if (count($ingredientExistsInDishes)>0) {
            return [
                'result' => 'error',
                'message' => __('general.ingredient').' "'. $ingredientTitle.'" '.__('general.included_into_meals_delete_impossible'),
            ];
        }

        IngredientTranslation::query()->where('ingredient_id', request('id'))->delete();
        Ingredient::query()->where('id', request('id'))->delete();

        return [
            'result' => 'success',
            'message' => __('general.ingredient').' "'.$ingredientTitle.'" '.__('general.deleted'),
        ];
    }

    public function askForNutritionValues(AiManager $aiManager)
    {
        $title = trim((string) request('title'));

        if ($title === '') {
            return response()->json([
                'result' => 'error',
                'message' => __('general.ingredient_title_is_required'),
            ], 422);
        }

        $user = request()->user();
        $apiKey = $user?->google_gemini_api_key;

        if (! filled($apiKey)) {
            return response()->json([
                'result' => 'error',
                'message' => 'Google Gemini API key is not configured in your profile.',
            ], 422);
        }

        config()->set('ai.providers.gemini.key', $apiKey);
        $aiManager->forgetInstance('gemini');

        $agent = new AnonymousAgent(
            'You provide nutritional data for food ingredients. Return only one valid JSON object and no markdown or explanations. Use numeric values with grams for proteins, fat, and carbohydrates and kilocalories for calories, all for exactly 1 gram of the ingredient. Use these keys only: proteins, fat, carbohydrates, calories.',
            [],
            [],
        );

        try {
            $response = $agent->prompt(
                'What are the nutritional values per 1 gram of this ingredient: '.$title.'. Return the most representative typical value.',
                provider: 'gemini',
            );

            $json = trim($response->text);
            $json = preg_replace('/^```(?:json)?\s*/i', '', $json) ?? $json;
            $json = preg_replace('/\s*```$/', '', $json) ?? $json;
            $nutrition = json_decode($json, true);

            foreach (['proteins', 'fat', 'carbohydrates', 'calories'] as $field) {
                if (! is_array($nutrition) || ! array_key_exists($field, $nutrition) || ! is_numeric($nutrition[$field])) {
                    return response()->json([
                        'result' => 'error',
                        'message' => 'Google Gemini returned an invalid nutrition response.',
                    ], 422);
                }

                $nutrition[$field] = (float) $nutrition[$field];

                if ($nutrition[$field] < 0) {
                    return response()->json([
                        'result' => 'error',
                        'message' => 'Google Gemini returned invalid negative nutrition values.',
                    ], 422);
                }
            }

            if ($nutrition['proteins'] + $nutrition['fat'] + $nutrition['carbohydrates'] > 1.0001) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'Google Gemini returned nutrition values whose macronutrients exceed 1 gram.',
                ], 422);
            }

            return [
                'result' => 'success',
                'nutrition' => $nutrition,
            ];
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'result' => 'error',
                'message' => 'Could not retrieve nutrition values from Google Gemini.',
            ], 502);
        }
    }
}
