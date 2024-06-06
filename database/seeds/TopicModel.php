<?php

use Illuminate\Database\Seeder;

class TopicModel extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        foreach (\App\Models\Topic::all() as $topic) {
            $topic->group_level_topic_id = 1;
            $topic->save();
        }

        $modelIds = \App\Models\CharacterModel::all()->pluck(
            'id'
        )->toArray();

        foreach (\App\Models\Item::all() as $item) {
            $item->models()->sync($modelIds);
            $item->save();
        }
    }
}
