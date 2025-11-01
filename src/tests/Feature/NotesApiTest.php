<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Date;
use Illuminate\Http\Request;
use App\Services\OperationLogService;
use App\Services\BroadcastService;
use App\Repositories\NotesRepository;
use Tests\TestCase;
use App\Models\Note;

class NotesApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Note::factory()->createMany([
            [
                'title' => '測試標題1',
                'content' => '測試內容1',
                'created_at' => '2025-01-01 00:00:00'
            ],
            [
                'title' => '測試標題2',
                'content' => '測試內容2',
                'created_at' => '2025-01-02 00:00:00'
            ],
        ]);
    }

    /**
     * 測試取得note的清單,且成功回傳
     */
    public function test_index_notes_get_list()
    {
        $response = $this->getJson('/api/notes?first=0&limit=2');

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertEquals('ok', $data['result']);
        $this->assertEquals('測試標題2', $data['ret'][0]['title']);
        $this->assertEquals('測試內容2', $data['ret'][0]['content']);
        $this->assertEquals('2025-01-02 00:00:00', $data['ret'][0]['created_at']);

        $this->assertEquals('測試標題1', $data['ret'][1]['title']);
        $this->assertEquals('測試內容1', $data['ret'][1]['content']);
        $this->assertEquals('2025-01-01 00:00:00', $data['ret'][1]['created_at']);
    }

    /**
     * 測試取得note的清單api未帶入first參數
     */
    public function test_index_notes_without_first_param()
    {
        $response = $this->getJson('/api/notes?limit=2');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('first');
    }

    /**
     * 測試取得note的清單api未帶入limit參數
     */
    public function test_index_notes_without_limit_param()
    {
        $response = $this->getJson('/api/notes?first=0');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('limit');
    }


    /**
     * 測試新增note,且成功回傳
     */
    public function test_store_notes_create()
    {
        Date::setTestNow('2025-01-01 09:00:00');

        $mockLog = \Mockery::mock(operationLogService::class);
        $mockLog->shouldReceive('recordLog')
             ->once()
             ->with('create', 'operation_logs', \Mockery::type('array'))
             ->andReturn(true);

        $this->app->instance(OperationLogService::class, $mockLog);

        $mockBroad = \Mockery::mock(BroadcastService::class);
        $mockBroad->shouldReceive('publishMessage')
             ->once()
             ->with(\Mockery::type(Note::class), 'create')
             ->andReturn(true);

        $this->app->instance(BroadcastService::class, $mockBroad);

        $response = $this->postJson('/api/notes', [
            'title' => '測試新title',
            'content' => '測試新內容'
        ]);

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertEquals('ok', $data['result']);
        $this->assertEquals('測試新title', $data['ret']['title']);
        $this->assertEquals('測試新內容', $data['ret']['content']);
        $this->assertEquals(now()->toJSON(), $data['ret']['created_at']);
    }

    /**
     * 測試編輯note,但找不到資料
     */
    public function test_update_notes_with_not_find()
    {
        $mockLog = \Mockery::mock(operationLogService::class);
        $mockBroad = \Mockery::mock(BroadcastService::class);
        $mockNoteRep = \Mockery::mock(NotesRepository::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not find note');

        $request = new Request([
            'title' => '編輯標題',
            'content' => '編輯內容',
        ]);

        $note = new NoteController($mockNoteRep, $mockBroad, $mockLog);
        $note->update($request, 100);
    }
}
