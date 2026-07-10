<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    protected $guarded = ['id'];

    public function eater()
    {
        return $this->belongsTo(Eater::class);
    }

    public function mealtimes()
    {
        return $this->belongsToMany(Mealtime::class, 'days_mealtimes');
    }
}
