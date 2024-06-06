<?php

use Illuminate\Database\Seeder;

class TopicCountry extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $topics = \App\Models\Topic::all();

        $countries = \App\Models\Topic::countries();

        foreach ($countries as $code => $countryName) {
            $country = new \App\Country();
            $country->fill([
                'country_code' => $code,
                'country_name' => $countryName
            ])->save();
        }

        foreach ($topics as $topic) {
            $countryCode = $topic->country_code;
            $countryModel = \App\Country::where('country_code', '=', $countryCode)->first();
            $topic->country_models()->attach($countryModel->id);
            $topic->save();
        }
    }
}
