<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\OperationLogService;
use Illuminate\Support\Facades\Redis;

class OperationLogServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Redis::flushdb();
    }

    public function test_recordLog()
    {
        $changeData = [
            'id' => 1,
            'title' => '標題',
            'content' => '內容',
            'created_at' => '2025-01-01 10:00:00'
        ];

        (new OperationLogService())->recordLog('create', 'notes', $changeData);

        $logs = Redis::lrange('operation_logs', 0, -1);
        $logs = array_map(fn($log) => json_decode($log, true), $logs);

        $this->assertEquals('create', $logs[0]['action']);
        $this->assertEquals('notes', $logs[0]['table']);
        $this->assertEquals(1, $logs[0]['changes']['id']);
        $this->assertEquals('標題', $logs[0]['changes']['title']);
        $this->assertEquals('內容', $logs[0]['changes']['content']);
        $this->assertEquals('2025-01-01 10:00:00', $logs[0]['changes']['created_at']);
    }
}
