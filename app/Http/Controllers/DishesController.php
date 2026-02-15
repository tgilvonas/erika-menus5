<?php

namespace App\Http\Controllers;

use App\Repositories\DishesRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DishesController
{
    public function index()
    {
        return Inertia::render('Dishes');
    }

    public function getJsonList()
    {
        DishesRepository::getList(
            request('paginate_by'),
            request('search_text'),
        );
    }
}
