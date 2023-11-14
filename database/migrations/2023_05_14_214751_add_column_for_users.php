<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnForUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add company_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('company_id')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove company_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }
}
