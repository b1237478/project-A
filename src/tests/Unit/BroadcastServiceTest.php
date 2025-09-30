<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BroadcastService;
use App\Models\Note;
use Illuminate\Support\Facades\Redis;

class BroadcastServiceTest extends TestCase
{
    public function test_publishMessage()
    {
        $note = new Note([
            'id' => 1,
            'title' => 'notetitle',
            'content' => 'notecontent',
            'updated_at' => '2025-01-01 00:00:00'
        ]);

        // mock redis確認參數是否正確
        Redis::shouldReceive('publish')
            ->once()
            ->withArgs(function ($channel, $payload) use ($note) {
                $data = json_decode($payload, true);

                return $channel === 'note-change'
                    && $data['method'] === 'create'
                    && $data['id'] === $note->id
                    && $data['title'] === $note->title
                    && $data['content'] === $note->content
                    && $data['updated_at'] === $note->updated_at;
            });

        (new BroadcastService())->publishMessage($note, 'create');
    }
}
