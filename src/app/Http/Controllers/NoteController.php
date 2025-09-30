<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\NotesRepository;
use App\Services\BroadcastService;
use App\Services\OperationLogService;
use App\Models\Note;
use App\Enums\TableConstant;


class NoteController extends Controller
{
    private $notesRepository;
    private $broadcastService;
    private $operationLogService;

    public function __construct(
        NotesRepository $notesRepository, 
        BroadcastService $broadcastService,
        OperationLogService $operationLogService,
    )
    {
        $this->notesRepository = $notesRepository;
        $this->broadcastService = $broadcastService;
        $this->operationLogService = $operationLogService;
    }

    /**
     * 筆記清單
     * 
     * 請求欄位：
     *  string int 起始筆數
     *  string int 搜尋筆數
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $request->validate([
            'first' => 'required|integer',
            'limit' => 'required|integer',
        ]);


        $data = [
            'first' => $request->first,
            'limit' => $request->limit
        ];

        $out = $this->notesRepository->getlist($data);

        return [
            'result' => 'ok',
            'ret' => $out
        ];
    }

    /**
     * 建立筆記
     *
     * 請求欄位：
     *  string title 標題
     *  string content 內容
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $request->validate([
            'title' => 'required|max:255'
        ]);

        //$out = Note::create($request->all());

        $note = new Note;
        $note->title = $request->title;

        if ($request->has('content')) {
            $note->content = $request->content;
        }

        $note->created_at = now()->format('Y-m-d H:i:s');
        // $note->operator

        // 寫操作紀錄並推播
        if ($note->save()) {
            $changeData = [
                'id' => $note->id,
                'title' => $note->title,
                'content' => $note->content,
                'created_at' => $note->created_at
            ];

            $this->operationLogService->recordLog(
                'create',
                TableConstant::OPERATION_LOGS->value,
                $changeData
            );
            $this->broadcastService->publishMessage($note, 'create');
        }

        return [
            'result' => 'ok',
            'ret' =>[
                'title' => $note->title,
                'content' => $note->content,
                //'user' => 
                'created_at' => $note->created_at
            ]
        ];
    }

    /**
     * 編輯筆記
     * 
     * 請求欄位：
     *  string title 標題
     *  string content 內容
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @param int id 編號
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $note = Note::find($id);

        if (!$note) {
            throw new \RuntimeException('Not find note');
        }

        if ($request->has('title') && !trim($request->title)) {
            throw new \InvalidArgumentException('Invalid title');
        }

        $changeData = ['id' => $note->id];

        // 判斷是否有修改
        if ($request->has('title') && $note->title !== $request->title) {
            $note->title = $request->title;
            $changeData['title'] = $note->title;
        }

        if ($request->has('content') && $note->content !== $request->content) {
            $note->content = $request->content;
            $changeData['content'] = $note->content;
        }

        // $note->operator

        // 寫操作紀錄並推播
        if ($note->save()) {
            $this->operationLogService->recordLog(
                'update',
                TableConstant::OPERATION_LOGS->value,
                $changeData);
            $this->broadcastService->publishMessage($note, 'update');
        }

        return [
            'result' => 'ok',
            'ret' => $note
        ];
    }

    /**
     * 刪除筆記
     * 
     * @param int id 編號
     * 
     * @return \Illuminate\Http\Response
     */
    public function destory($id)
    {
        $note = Note::find($id);

        if (!$note) {
            throw new \RuntimeException('Not find note');
        }

        // 寫操作紀錄並推播
        if ($note->delete()) {
            $changeData = [
                'id' => $note->id,
                'title' => $note->title,
                'content' => $note->content
            ];

            $this->operationLogService->recordLog(
                'delete',
                TableConstant::OPERATION_LOGS->value,
                $changeData);
            $this->broadcastService->publishMessage($note, 'delete');
        }

        return [
            'result' => 'ok',
            'ret' => [
                'id' => $note->id,
                'title' => $note->title,
                'content' => $note->content
            ]
        ];
    }
}
