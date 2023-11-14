<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddStripePriceIdsToPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->json('stripe_price_ids')->nullable();
        });

        DB::table('plans')->get()->each(function ($plan) {
            DB::table('plans')->where('id', $plan->id)->update([
                'stripe_price_ids' => json_encode([
                    'gpt-4' => $plan->stripe_metered_price_id,
                    'gpt-3.5-turbo' => 'price_1NhZ3BFwP2mbkTrb9A3bHvRo',
                    'base_plan' => $plan->stripe_flat_rate_price_id,
                ]),
            ]);
        });

       
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('stripe_metered_price_id');
            $table->dropColumn('stripe_flat_rate_price_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('stripe_price_ids');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('stripe_flat_rate_price_id')->nullable();
            $table->string('stripe_metered_price_id')->nullable();
        });
        
    }
}