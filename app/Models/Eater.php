<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eater extends Model
{
    protected $table = 'eaters';
    protected $guarded = ['id'];

    public function dietType()
    {
        return $this->belongsTo(DietType::class, 'diet_type_id');
    }


}
