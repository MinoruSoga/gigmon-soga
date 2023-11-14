<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned();
            $table->string('name');
            $table->timestamps();
        });

        $db = \DB::table('categories');
        $insert = [
            [
                'id' => 0,
                'name' => 'None',
            ],
            [
                'id' => 1,
                'name' => 'Writing, interpretation, summarization',
            ],
            [
                'id' => 2,
                'name' => 'Feedback',
            ],
            [
                'id' => 3,
                'name' => 'Brainstorming',
            ],
            [
                'id' => 4,
                'name' => 'Marketing',
            ],
            [
                'id' => 5,
                'name' => 'Learning',
            ],
            [
                'id' => 99,
                'name' => 'Other',
            ]
        ];
        $db->insert($insert);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
