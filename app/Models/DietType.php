<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DietType extends Model
{
    protected $table = 'diet_types';
    protected $guarded = ['id'];

    /** TODO: fix repeated relations of translations */
    public function translation($lang = 'lt')
    {
        return $this->hasOne(DietTypeTranslation::class, 'diet_types_id')
            ->where('lang', $lang);
    }

    public function dietTypeTranslation()
    {
        return $this->hasOne(DietTypeTranslation::class, 'diet_types_id')
            ->where('lang', 'lt');
    }
}
