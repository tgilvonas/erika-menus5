<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    protected $table = 'dishes';
    protected $guarded = ['id'];

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'dishes_ingredients');
    }

    public function translation($lang = 'lt')
    {
        return $this->hasOne(DishTranslation::class, 'dish_id')->where('lang', $lang);
    }

    public static function getIngredients($dishId, $language='lt')
    {
        return self::query()
            ->selectRaw('mass_brutto, mass_netto, ingredients.id AS id, calories, proteins, fat, carbohydrates, translation AS title')
            ->join('dishes_ingredients', 'dishes.id', '=', 'dishes_ingredients.dish_id')
            ->join('ingredients', 'dishes_ingredients.ingredient_id', '=', 'ingredients.id')
            ->join('ingredients_translations', 'ingredients.id', '=', 'ingredients_translations.ingredient_id')
            ->where('ingredients_translations.lang', $language)
            ->where('dishes.id', $dishId)
            ->orderBy('ord', 'asc')
            ->get();
    }
}
