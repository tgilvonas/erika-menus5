<?php

namespace Database\Migrations;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'dishes_mealtimes';

    /**
     * Run the migrations.
     * @table dishes_mealtimes
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('dish_id')->nullable()->default(null);
            $table->integer('mealtime_id')->nullable()->default(null);
            $table->bigInteger('day_id')->nullable()->default(null);

            $table->index(["dish_id"], 'fk_dishes_mealtimes_dishes1_idx');

            $table->index(["mealtime_id"], 'fk_dishes_mealtimes_mealtimes1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
};
