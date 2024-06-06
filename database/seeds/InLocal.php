<?php

use Illuminate\Database\Seeder;

class InLocal extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        foreach (\App\Models\Item::all() as $item) {
            $item->in_local = 1;
            $item->save();
        }
    }
}
