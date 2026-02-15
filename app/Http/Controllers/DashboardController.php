<?php

namespace App\Http\Controllers;

use App\Repositories\DietTypesRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

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
}
