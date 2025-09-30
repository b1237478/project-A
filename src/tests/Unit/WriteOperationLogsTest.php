<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Jobs\WriteOperationLogs;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WriteOperationLogsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Redis::flushdb();
    }

    public function test_handle()
    {
        Redis::rpush('operation_logs', json_encode([
            'action' => 'create',
            'table' => 'notes',
            'changes' => [
                'id' => 1,
                'title' => '新標題',
                'content' => '新內容',
                'created_at' => '2025-01-01 00:00:00'
            ],
            'operator' => 123
        ]));

        (new WriteOperationLogs())->handle();


        $logs = DB::table('operation_logs')->get();
        $logs = $logs->map(function ($item) {
            return (array) $item;
        })->toArray();
        $logsChagesData = json_decode($logs[0]['changes'], true);

        $this->assertEquals('create', $logs[0]['action']);
        $this->assertEquals('notes', $logs[0]['table']);
        $this->assertEquals(1, $logsChagesData['id']);
        $this->assertEquals('新標題', $logsChagesData['title']);
        $this->assertEquals('新內容', $logsChagesData['content']);
        $this->assertEquals('2025-01-01 00:00:00', $logsChagesData['created_at']);
        $this->assertEquals(123, $logs[0]['operator']);
    }
}
