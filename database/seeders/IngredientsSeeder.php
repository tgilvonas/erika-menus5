<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\IngredientTranslation;
use Illuminate\Database\Seeder;

class IngredientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run(): void
    {
        $fileContents = file_get_contents(public_path('ingredients.html'));

        // pavadinimas, baltymai, riebalai, angliavandeniai
        $xmlIterator = new \SimpleXMLIterator($fileContents);
        for ( $xmlIterator->rewind(); $xmlIterator->valid(); $xmlIterator->next()) {
            $tableCells = $xmlIterator->getChildren();
            if ($tableCells->td[0] === 'Produkto pavadinimas') {
                continue;
            }

            $ingredient = new Ingredient();
            $ingredient->ingredient_title = $tableCells->td[0];

            $proteins = str_replace(',', '.', $tableCells->td[1]);
            if (is_numeric($proteins)) {
                $ingredient->proteins = $proteins / 100;
            } else {
                $ingredient->proteins = 0;
            }

            $fat = str_replace(',', '.', $tableCells->td[2]);
            if (is_numeric($fat)) {
                $ingredient->fat = $fat / 100;
            } else {
                $ingredient->fat = 0;
            }

            $carbohydrates = str_replace(',', '.', $tableCells->td[3]);
            if (is_numeric($carbohydrates)) {
                $ingredient->carbohydrates = $carbohydrates / 100;
            } else {
                $ingredient->carbohydrates = 0;
            }

            $calories = str_replace(',', '.', $tableCells->td[4]);
            if (is_numeric($calories)) {
                $ingredient->calories = $calories / 100;
            } else {
                $ingredient->calories = 0;
            }

            $ingredient->save();

            $translation = new IngredientTranslation();
            $translation->ingredient_id = $ingredient->id;
            $translation->lang = 'lt';
            $translation->translation = $ingredient->ingredient_title;
            $translation->save();
        }
    }
}
