<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\DietType;
use App\Models\Eater;
use App\Models\Mealtime;
use App\Repositories\DietTypesRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DaysController extends Controller
{
    public function index(Request $request)
    {
        $query = Day::query()
            ->select('days.*', 'eaters.name as eater_name')
            ->leftJoin('eaters', 'eaters.id', '=', 'days.eater_id')
            ->orderBy('days.date', 'desc')
            ->orderBy('days.id', 'desc');

        if ($request->filled('eater_id')) {
            $query->where('days.eater_id', $request->integer('eater_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('days.date', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('days.date', '<=', $request->date('date_to'));
        }

        return Inertia::render('Days', [
            'days' => $query->paginate(10)->withQueryString(),
            'eaters' => Eater::query()->orderBy('name', 'asc')->get(),
            'filters' => $request->only('eater_id', 'date_from', 'date_to'),
        ]);
    }

    public function edit($id)
    {
        $day = Day::getDayWithAllRelations($id);

        if (is_numeric($id) && $day === null) {
            abort(404);
        }

        return Inertia::render('DayEdit', [
            'day' => $day,
            'eaters' => Eater::query()->orderBy('name', 'asc')->get(),
            'dietTypes' => DietTypesRepository::getListOfDietTypes(),
            'mealtimes' => Mealtime::query()
                ->with('translation')
                ->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'day_id' => ['nullable'],
            'date' => ['required', 'date'],
            'eater_id' => ['required', 'integer', 'exists:eaters,id'],
            'mealtimes' => ['array'],
            'mealtimes.*.id' => ['required', 'integer', 'exists:mealtimes,id'],
            'mealtimes.*.dishes' => ['array'],
            'mealtimes.*.dishes.*.id' => ['required', 'integer', 'exists:dishes,id'],
        ]);

        $dayId = $validated['day_id'] ?? null;
        $day = is_numeric($dayId)
            ? Day::query()->findOrFail($dayId)
            : new Day();

        $day->date = $validated['date'];
        $day->eater_id = $validated['eater_id'];
        $day->save();

        DB::table('days_mealtimes')->where('day_id', $day->id)->delete();
        DB::table('dishes_mealtimes')->where('day_id', $day->id)->delete();

        foreach ($validated['mealtimes'] ?? [] as $mealtime) {
            DB::table('days_mealtimes')->insert([
                'day_id' => $day->id,
                'mealtime_id' => $mealtime['id'],
            ]);

            foreach ($mealtime['dishes'] ?? [] as $dish) {
                DB::table('dishes_mealtimes')->insert([
                    'day_id' => $day->id,
                    'dish_id' => $dish['id'],
                    'mealtime_id' => $mealtime['id'],
                ]);
            }
        }

        $date = $day->date instanceof \Carbon\Carbon
            ? $day->date->format('Y-m-d')
            : (string) $day->date;

        return redirect()->route('days.index')->with(
            'message',
            __('general.day') . ' "' . $date . '" ' . __('general.saved_female')
        );
    }

    public function delete(Request $request)
    {
        $day = Day::query()->findOrFail($request->integer('id'));

        $date = $day->date instanceof \Carbon\Carbon
            ? $day->date->format('Y-m-d')
            : (string) $day->date;

        $successMessage = __('general.day') .' "' . $date . '" ' . __('general.deleted_female');

        DB::table('days_mealtimes')->where('day_id', $day->id)->delete();
        DB::table('dishes_mealtimes')->where('day_id', $day->id)->delete();
        $day->delete();

        return [
            'result' => 'success',
            'message' => $successMessage,
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
