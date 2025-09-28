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
    public $tableName = 'diet_types';

    /**
     * Run the migrations.
     * @table diet_types
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('diet_type', 250)->nullable()->default(null);
            $table->double('calories_min')->nullable()->default(null);
            $table->double('calories_max')->nullable()->default(null);
            $table->double('proteins_min')->nullable()->default(null);
            $table->double('proteins_max')->nullable()->default(null);
            $table->double('fat_min')->nullable()->default(null);
            $table->double('fat_max')->nullable()->default(null);
            $table->double('carbohydrates_min')->nullable()->default(null);
            $table->double('carbohydrates_max')->nullable()->default(null);
            $table->nullableTimestamps();
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
