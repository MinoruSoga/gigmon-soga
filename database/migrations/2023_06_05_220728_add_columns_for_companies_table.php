<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsForCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add agency_code and staff_code for companies
        Schema::table('companies', function (Blueprint $table) {
            $table->string('postal_code')->nullable();
            $table->string('prefecture')->nullable();
            $table->string('city')->nullable();
            $table->string('building')->nullable();
            $table->string('agency_code')->nullable();
            $table->string('staff_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove agency_code and staff_code for companies
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('postal_code');
            $table->dropColumn('prefecture');
            $table->dropColumn('city');
            $table->dropColumn('building');
            $table->dropColumn('agency_code');
            $table->dropColumn('staff_code');
        });
    }
}
