<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GPTFunction;

class FunctionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GPTFunction::truncate();

        GPTFunction::create([
            'name' => 'Web ブラウジング',
            'description' => 'Web ブラウジングを行う',
            'gpt_name' => 'web_browsing',
            'gpt_description' => 'Get the content of a web page',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'url' => [
                        'type' => 'string',
                        'description' => 'URL for web browsing',
                    ]
                ],
                'required' => [
                    'url'
                ]
            ]
        ]);
    }
}