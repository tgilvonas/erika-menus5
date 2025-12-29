<?php

namespace App\Http\Controllers;

use App\Http\Requests\MealtimeRequest;
use App\Models\Mealtime;
use App\Models\MealtimeTranslation;
use App\Repositories\MealtimesRepository;
use Inertia\Inertia;
use Inertia\Response;

class MealtimesController
{
    public function index(): Response
    {
        return Inertia::render('Mealtimes');
    }

    public function getJsonList()
    {
        return MealtimesRepository::getPaginatedListOfMealtimes(
            'lt',
            request('order_by_field'),
            request('order_by_direction'),
            request('search_text'),
            request('paginate_by')
        );
    }

    public function save(MealtimeRequest $request)
    {
        if (is_numeric($request->id)) {
            $mealtime = Mealtime::query()->find($request->id);
            $mealtimeTranslation = MealtimeTranslation::query()->where('mealtime_id', $mealtime->id)->first();
        } else {
            $mealtime = new Mealtime();
            $mealtimeTranslation = new MealtimeTranslation();
        }

        $mealtime->fill($request->except('_token', 'id', 'title'));
        $mealtime->save();

        $mealtimeTranslation->mealtime_id = $mealtime->id;
        $mealtimeTranslation->lang = 'lt'; // temporarily
        $mealtimeTranslation->translation = $request->title;
        $mealtimeTranslation->save();

        return [
            'result' => 'Success',
            'message' => __('general.mealtime').' "'.$request->title.'" '.__('general.saved'),
            'mealtime' => $mealtime,
        ];
    }

    /**
     * @TODO: check if mealtime belongs to days
     */
    public function delete()
    {
        $id = request('id');

        $mealtimeTranslation = MealtimeTranslation::query()
            ->where('mealtime_id', $id)
            ->where('lang', 'lt')
            ->first();

        Mealtime::query()->where('id', $id)->delete();
        MealtimeTranslation::query()->where('mealtime_id', $id)->delete();

        return [
            'result' => 'success',
            'message' => __('general.mealtime').' "'.$mealtimeTranslation->translation.'" '.__('general.deleted'),
        ];
    }
}
