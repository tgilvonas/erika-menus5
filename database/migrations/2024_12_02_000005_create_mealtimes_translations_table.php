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
    public $tableName = 'mealtimes_translations';

    /**
     * Run the migrations.
     * @table mealtimes_translations
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('mealtime_id')->nullable()->default(null);
            $table->string('lang', 3)->nullable()->default(null);
            $table->string('translation', 250)->nullable()->default(null);

            $table->index(["mealtime_id"], 'fk_mealtimes_translations_mealtimes1_idx');
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
