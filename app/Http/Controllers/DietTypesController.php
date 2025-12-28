<?php

namespace App\Http\Controllers;

use App\Http\Requests\DietTypeRequest;
use App\Models\DietType;
use App\Models\DietTypeTranslation;
use App\Models\Eater;
use App\Repositories\DietTypesRepository;
use Inertia\Inertia;
use Inertia\Response;

class DietTypesController
{
    public function index(): Response
    {
        return Inertia::render('DietTypes');
    }

    public function getJsonList()
    {
        return DietTypesRepository::getPaginatedListOfDietTypes(
            'lt',
            request('order_by_field'),
            request('order_by_direction'),
            request('search_text'),
            request('paginate_by')
        );
    }

    public function save(DietTypeRequest $request)
    {
        if (is_numeric($request->id)) {
            $dietType = DietType::find($request->id);
            $dietTypeTranslation = DietTypeTranslation::where('diet_types_id', $dietType->id)->first();
        } else {
            $dietType = new DietType();
            $dietTypeTranslation = new DietTypeTranslation();
        }

        $dietType->fill($request->except('_token', 'id', 'title'));
        $dietType->save();

        $dietTypeTranslation->diet_types_id = $dietType->id;
        $dietTypeTranslation->lang = 'lt'; // temporarily
        $dietTypeTranslation->translation = $request->title;
        $dietTypeTranslation->save();

        return [
            'result' => 'Success',
            'message' => __('general.diet').' "'.$request->title.'" '.__('general.saved'),
            'diet_type_id' => $dietType->id,
        ];
    }

    public function delete()
    {
        $id = request('id');

        $dietTypeTranslation = DietTypeTranslation::where('diet_types_id', $id)->where('lang', 'lt')->first();

        $dietTypeIsInUse = Eater::where('diet_type_id', request('id'))->get();
        if (count($dietTypeIsInUse)>0) {
            return [
                'result' => 'error',
                'message' => __('general.diet').' "'.$dietTypeTranslation->translation.'" '.__('general.is_assigned_to_eater_impossible_to_delete')
            ];
        }

        DietType::where('id', $id)->delete();
        DietTypeTranslation::where('diet_types_id', $id)->delete();

        return [
            'result' => 'success',
            'message' => __('general.diet').' "'.$dietTypeTranslation->translation.'" '.__('general.deleted'),
        ];
    }
}
