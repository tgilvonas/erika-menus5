<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mealtime extends Model
{
    protected $table = 'mealtimes';
    protected $guarded = ['id'];

    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'dishes_mealtimes');
    }

    public function translation($lang = 'lt')
    {
        return $this->hasOne(MealtimeTranslation::class, 'mealtime_id')->where('lang', $lang);
    }
}
