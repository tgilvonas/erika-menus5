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

    public function edit($id)
    {
        $day = Day::getDayWithAllRelations($id);

        return Inertia::render('Days/Edit', [
            'day' => $day,
            'eaters' => Eater::query()->orderBy('name', 'asc')->get(),
            'dietTypes' => DietType::query()->orderBy('name', 'asc')->get(),
            'mealtimes' => Mealtime::query()->orderBy('time', 'asc')->get(),
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

    public function exportPdf($id)
    {
        $day = Day::getDayWithAllRelations($id);

        abort_if($day === null, 404);

        $day->append('sum_nutrition_values');

        $date = $day->date instanceof \Carbon\Carbon
            ? $day->date->format('Y-m-d')
            : (string) $day->date;

        $pdf = Pdf::loadView('day_pdf', ['day' => $day]);

        return $pdf->stream("day-{$date}.pdf");
    }
}
