<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateConversationInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversation_infos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('conversation_system_id');
            $table->string('conversation_token');
            $table->string('title')->nullable();
            $table->boolean('is_visible');
            $table->timestamps();
        });

        DB::statement(
            ' INSERT INTO conversation_infos (user_id, conversation_system_id, conversation_token, title, is_visible, created_at, updated_at) ' .
            '      SELECT user_id, conversation_system_id, conversation_token, NULL, true, MIN(created_at), MIN(updated_at) ' .
            '        FROM conversations ' .
            '    GROUP BY user_id, conversation_system_id, conversation_token'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversation_infos');
    }
}
