<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Resources\Admin\Api\TopicDetailResource;
use App\Http\Resources\Admin\Api\TopicResource;
use App\Http\Resources\Admin\Api\TypeForTopic;
use App\Models\Topic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TopicController extends Controller
{
    public function getTopics()
    {
        $topics = Topic::all();
        $topicsResource = TopicResource::collection($topics);
        return $topicsResource;
    }

    public function getTopicDetails(int $topic_id)
    {
        $topic = Topic::find($topic_id);
        $topicDetails = $topic->topicDetails;
        $topicDetailsResource = TopicDetailResource::collection($topicDetails);
        return $topicDetailsResource;
    }

    public function getTypesById(int $id)
    {
        $task = Topic::findOrFail($id);
        $types = $task->types;

        return TypeForTopic::collection($types);
    }
}
