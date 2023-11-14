<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('plans')->truncate();

        DB::table('plans')->insert([
            'id' => 1,
            'name' => 'トライアル',
            'max_users' => 20,
            'max_prompts' => 100,
            'knowledge_base_enabled' => true,
            'stripe_product_id' => null,
            'stripe_price_ids' => null,
            'created_at' => date_create()
        ]);

        DB::table('plans')->insert([
            'id' => 2,
            'name' => 'スターター',
            'max_users' => 5,
            'max_prompts' => 20,
            'knowledge_base_enabled' => false,
            'stripe_product_id' => 'prod_OUGEmg3aIIJSGR',
            'stripe_price_ids' => json_encode([
                'gpt-4' => 'price_1NepWGFwP2mbkTrb8z2WPmWl',
                'gpt-3.5-turbo' => 'price_1NhZ3BFwP2mbkTrb9A3bHvRo',
                'base_plan' => 'price_1NhHiNFwP2mbkTrbadjarmwX',
            ]),
            'created_at' => date_create()
        ]);

        DB::table('plans')->insert([
            'id' => 3,
            'name' => 'プロ',
            'max_users' => 20,
            'max_prompts' => 100,
            'knowledge_base_enabled' => true,
            'stripe_product_id' => 'prod_OUKUpbKKUmy61V',
            'stripe_price_ids' => json_encode([
                'gpt-4' => 'price_1NepWGFwP2mbkTrb8z2WPmWl',
                'gpt-3.5-turbo' => 'price_1NhZ3BFwP2mbkTrb9A3bHvRo',
                'base_plan' => 'price_1NhLp4FwP2mbkTrbwQd4YS1u',
            ]),
            'created_at' => date_create()
        ]);

        DB::table('plans')->insert([
            'id' => 4,
            'name' => 'ビジネス',
            'max_users' => 100,
            'max_prompts' => 9999,
            'knowledge_base_enabled' => true,
            'stripe_product_id' => 'prod_OUKUrMKDHaIBlA',
            'stripe_price_ids' => json_encode([
                'gpt-4' => 'price_1NepWGFwP2mbkTrb8z2WPmWl',
                'gpt-3.5-turbo' => 'price_1NhZ3BFwP2mbkTrb9A3bHvRo',
                'base_plan' => 'price_1NhLpkFwP2mbkTrbtUf4iuZj',
            ]),
            'created_at' => date_create()
        ]);

        DB::table('plans')->insert([
            'id' => 5,
            'name' => 'エンタープライズ',
            'max_users' => 9999,
            'max_prompts' => 9999,
            'knowledge_base_enabled' => true,
            'stripe_product_id' => null,
            'stripe_price_ids' => null,
            'created_at' => date_create()
        ]);
    }
}
