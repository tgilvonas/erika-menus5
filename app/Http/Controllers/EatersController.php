<?php

namespace App\Http\Controllers;

use App\Http\Requests\EaterRequest;
use App\Models\Eater;
use App\Repositories\EatersRepository;
use Inertia\Inertia;
use Inertia\Response;

class EatersController
{
    public function index(): Response
    {
        return Inertia::render('Eaters');
    }

    public function getJsonList()
    {
        return EatersRepository::getListWithDietTypes(
            'lt',
            request('order_by_field'),
            request('order_by_direction'),
            request('search_text'),
            request('paginate_by')
        );
    }

    public function save(EaterRequest $request)
    {
        if (is_numeric(request('id'))) {
            $eater = Eater::query()->find($request->id);
            $msgPart = __('general.updated');
        } else {
            $eater = new Eater();
            $msgPart = __('general.created');
        }

        $eater->name = $request->name;
        $eater->diet_type_id = $request->diet_type_id;
        $eater->save();

        return [
            'result' => 'success',
            'message' => __('general.eater').' "'.$request->name.'" '.$msgPart.'.',
            'eater' => $eater,
        ];
    }

    public function delete()
    {
        $eater = Eater::query()->findOrFail(request('id'));

        Eater::query()->where('id', request('id'))->delete();

        return [
            'result' => 'success',
            'message' => __('general.eater').' "'.$eater->name.'" '.__('general.deleted')
        ];
    }
}
