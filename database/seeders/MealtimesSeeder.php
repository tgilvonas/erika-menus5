<?php

namespace Database\Seeders;

use App\Models\Mealtime;
use App\Models\MealtimeTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MealtimesSeeder extends Seeder
{
    public function run(): void
    {
        $mealtime = new Mealtime();
        $mealtime->mealtime_title = 'Pusryčiai';
        $mealtime->percent_from = 20;
        $mealtime->percent_to = 25;
        $mealtime->save();

        $mealtimeTranslation = new MealtimeTranslation();
        $mealtimeTranslation->mealtime_id = $mealtime->id;
        $mealtimeTranslation->lang = 'lt';
        $mealtimeTranslation->translation = 'Pusryčiai';
        $mealtimeTranslation->save();


        $mealtime = new Mealtime();
        $mealtime->mealtime_title = 'Priešpiečiai';
        $mealtime->percent_from = 0;
        $mealtime->percent_to = 10;
        $mealtime->save();

        $mealtimeTranslation = new MealtimeTranslation();
        $mealtimeTranslation->mealtime_id = $mealtime->id;
        $mealtimeTranslation->lang = 'lt';
        $mealtimeTranslation->translation = 'Priešpiečiai';
        $mealtimeTranslation->save();


        $mealtime = new Mealtime();
        $mealtime->mealtime_title = 'Pietūs';
        $mealtime->percent_from = 35;
        $mealtime->percent_to = 40;
        $mealtime->save();

        $mealtimeTranslation = new MealtimeTranslation();
        $mealtimeTranslation->mealtime_id = $mealtime->id;
        $mealtimeTranslation->lang = 'lt';
        $mealtimeTranslation->translation = 'Pietūs';
        $mealtimeTranslation->save();


        $mealtime = new Mealtime();
        $mealtime->mealtime_title = 'Pavakariai';
        $mealtime->percent_from = 0;
        $mealtime->percent_to = 10;
        $mealtime->save();

        $mealtimeTranslation = new MealtimeTranslation();
        $mealtimeTranslation->mealtime_id = $mealtime->id;
        $mealtimeTranslation->lang = 'lt';
        $mealtimeTranslation->translation = 'Pavakariai';
        $mealtimeTranslation->save();


        $mealtime = new Mealtime();
        $mealtime->mealtime_title = 'Vakarienė';
        $mealtime->percent_from = 20;
        $mealtime->percent_to = 25;
        $mealtime->save();

        $mealtimeTranslation = new MealtimeTranslation();
        $mealtimeTranslation->mealtime_id = $mealtime->id;
        $mealtimeTranslation->lang = 'lt';
        $mealtimeTranslation->translation = 'Vakarienė';
        $mealtimeTranslation->save();
    }
}
