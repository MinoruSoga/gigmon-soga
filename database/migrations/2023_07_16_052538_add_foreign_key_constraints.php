<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyConstraints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('company_histories', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('conversation_infos', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
        });
        Schema::table('conversation_infos', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('prohibited_words', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->change();
        });
        Schema::table('prohibited_words', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('prompts', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('user_inputs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
        });
        Schema::table('company_histories', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
        Schema::table('conversation_infos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['company_id']);
        });
        Schema::table('prohibited_words', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
        Schema::table('prompts', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('user_inputs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
    }
}