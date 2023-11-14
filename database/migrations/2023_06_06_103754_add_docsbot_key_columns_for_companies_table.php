<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocsbotKeyColumnsForCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add docsbot_key column to companies table
        Schema::table('companies', function (Blueprint $table) {
            $table->string('docsbot_team_id')->nullable();
            $table->string('docsbot_bot_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop docsbot_key column from companies table
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('docsbot_team_id');
            $table->dropColumn('docsbot_bot_id');
        });
    }
}
