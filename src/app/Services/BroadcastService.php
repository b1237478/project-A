<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use App\Models\Note;

class BroadcastService
{
    /**
     * 推送Note訊息
     * 
     * @param object $note 筆記物件
     * @param string $method create、update、delete
     */
    public function publishMessage(Note $note, $method)
    {
        Redis::publish('note-change', json_encode([
            'method' => $method,
            'id' => $note->id,
            'title' => $note->title,
            'content' => $note->content,
            'updated_at' => $note->updated_at,
        ]));
    }
}
