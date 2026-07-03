<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Repositories\DietTypesRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard(): InertiaResponse
    {
        return Inertia::render('Dashboard');
    }

    public function getDietTypesStats(): Collection
    {
        return DietTypesRepository::getDietTypesStats();
    }

    public function getIngredientsUsageStats()
    {
        $idsOfIngredients = DB::table('dishes_ingredients')
            ->select('ingredient_id')
            ->distinct()
            ->get()
            ->pluck('ingredient_id')
            ->toArray();
        
        // in case we have "dead" records in dishes_ingredients table, we need to check if the ingredients exist in the ingredients table, for reliability of the stats:
        $usedIngredientsCount = Ingredient::query()->whereIn('id', $idsOfIngredients)->get()->count();
        
        $unusedIngredientsCount = Ingredient::query()->whereNotIn('id', $idsOfIngredients)->get()->count();

        return [
            'used' => $usedIngredientsCount,
            'unused' => $unusedIngredientsCount,
        ];
    }
}
