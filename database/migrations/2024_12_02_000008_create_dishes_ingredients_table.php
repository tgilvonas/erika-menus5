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
    public $tableName = 'dishes_ingredients';

    /**
     * Run the migrations.
     * @table dishes_ingredients
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('dish_id')->nullable()->default(null);
            $table->integer('ingredient_id')->nullable()->default(null);
            $table->double('mass_brutto')->nullable()->default(null);
            $table->double('mass_netto')->nullable()->default(null);
            $table->integer('ord')->nullable()->default(null);

            $table->index(["ingredient_id"], 'fk_dishes_ingredients_ingredients1_idx');

            $table->index(["dish_id"], 'fk_dishes_ingredients_dishes1_idx');
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
