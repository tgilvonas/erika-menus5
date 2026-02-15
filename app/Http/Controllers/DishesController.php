<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DishesController
{
    public function index()
    {
        return Inertia::render('Dishes');
    }
}
