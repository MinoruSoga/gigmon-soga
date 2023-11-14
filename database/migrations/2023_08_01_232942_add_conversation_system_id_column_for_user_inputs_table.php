<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConversationSystemIdColumnForUserInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = Schema::table('user_inputs', function (Blueprint $table) {
            $table->integer('conversation_system_id')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = Schema::table('user_inputs', function (Blueprint $table) {
            $table->dropColumn('conversation_system_id');
        });
    }
}
