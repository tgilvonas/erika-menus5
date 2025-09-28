<?php

namespace Database\Seeders;

use App\Models\DietType;
use App\Models\DietTypeTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DietTypesSeeder extends Seeder
{
    public function run(): void
    {
        $dietType = new DietType();
        $dietType->diet_type = 'Vaikams 1-3 metų';
        $dietType->calories_min = 900;
        $dietType->calories_max = 1200;
        $dietType->proteins_min = 27;
        $dietType->proteins_max = 66;
        $dietType->fat_min = 30;
        $dietType->fat_max = 52;
        $dietType->carbohydrates_min = 122;
        $dietType->carbohydrates_max = 198;
        $dietType->save();

        $dietTypeTranslation = new DietTypeTranslation();
        $dietTypeTranslation->diet_types_id = $dietType->id;
        $dietTypeTranslation->lang = 'lt';
        $dietTypeTranslation->translation = 'Vaikams 1-3 metų';
        $dietTypeTranslation->save();

        $dietTypeTranslation = new DietTypeTranslation();
        $dietTypeTranslation->diet_types_id = $dietType->id;
        $dietTypeTranslation->lang = 'en';
        $dietTypeTranslation->translation = 'For children, from 1 to 3 years old';
        $dietTypeTranslation->save();


        $dietType = new DietType();
        $dietType->diet_type = 'Vaikams 4-6 metų';
        $dietType->calories_min = 1200;
        $dietType->calories_max = 1500;
        $dietType->proteins_min = 34;
        $dietType->proteins_max = 83;
        $dietType->fat_min = 38;
        $dietType->fat_max = 64;
        $dietType->carbohydrates_min = 152;
        $dietType->carbohydrates_max = 245;
        $dietType->save();

        $dietTypeTranslation = new DietTypeTranslation();
        $dietTypeTranslation->diet_types_id = $dietType->id;
        $dietTypeTranslation->lang = 'lt';
        $dietTypeTranslation->translation = 'Vaikams 4-6 metų';
        $dietTypeTranslation->save();

        $dietTypeTranslation = new DietTypeTranslation();
        $dietTypeTranslation->diet_types_id = $dietType->id;
        $dietTypeTranslation->lang = 'en';
        $dietTypeTranslation->translation = 'For children, from 4 to 6 years old';
        $dietTypeTranslation->save();
    }
}
