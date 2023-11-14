<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeDetailsToPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_flat_rate_price_id')->nullable();
            $table->string('stripe_metered_price_id')->nullable();
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
            $table->dropColumn('stripe_product_id');
            $table->dropColumn('stripe_flat_rate_price_id');
            $table->dropColumn('stripe_metered_price_id');
        });
    }
}
