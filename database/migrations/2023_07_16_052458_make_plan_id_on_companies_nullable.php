<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakePlanIdOnCompaniesNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->bigInteger('plan_id')->unsigned()->default(1)->nullable()->change();
        });
        DB::statement('UPDATE companies SET plan_id = NULL WHERE plan_id = 0');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->bigInteger('plan_id')->unsigned()->default(1)->change();
        });
    }
}
