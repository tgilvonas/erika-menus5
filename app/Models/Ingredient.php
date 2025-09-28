<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $table = 'ingredients';
    protected $guarded = ['id'];

    public function translation($lang = 'lt')
    {
        return $this->hasOne(IngredientTranslation::class, 'ingredient_id')->where('lang', $lang);
    }
}
