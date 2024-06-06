<?php

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = [
            [
                'name' => 'Vietnam',
                'symbol' => 'vi',
            ],
            [
                'name' => 'English',
                'symbol' => 'en',
            ]
        ];
        foreach ($languages as $language) {
            Language::firstOrCreate([
                'name' => $language['name'],
                'symbol' => $language['symbol'],
            ], $language);
        }
    }
}
