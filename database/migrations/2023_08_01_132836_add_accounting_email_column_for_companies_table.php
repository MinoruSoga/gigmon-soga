<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountingEmailColumnForCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = Schema::table('companies', function (Blueprint $table) {
            $table->string('accounting_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('accounting_email');
        });
    }
}
