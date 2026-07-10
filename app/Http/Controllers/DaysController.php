<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\DietType;
use App\Models\Eater;
use App\Models\Mealtime;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DaysController extends Controller
{
    public function index()
    {
        return Inertia::render('Days', [
            'eaters' => Eater::query()->orderBy('name', 'asc')->get(),
        ]);
    }

    public function delete()
    {
        $day = Day::query()->find(request('id'));

        $successMessage = __('general.day') .' "' . $day->date->format('Y-m-d') . '" ' . __('general.deleted_female');

        DB::table('days_mealtimes')->where('day_id', $day->id)->delete();
        DB::table('dishes_mealtimes')->where('day_id', $day->id)->delete();
        $day->delete();

        return [
            'result' => 'success',
            'message' => $successMessage
        ];
    }
}
