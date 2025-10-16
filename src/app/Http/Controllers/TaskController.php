<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\TasksRepository;
use App\Services\CheckTaskPermissionService;
use App\Models\Task;

class TaskController extends Controller
{
    private $tasksRepository;
    private $checkTaskPermissionService;

    public function __construct(
        TasksRepository $tasksRepository,
        CheckTaskPermissionService $checkTaskPermissionService
    )
    {
        $this->tasksRepository = $tasksRepository;
        $this->checkTaskPermissionService = $checkTaskPermissionService;
    }

    /**
     * 任務清單
     * 
     * 請求欄位：
     *  int first 起始筆數
     *  int limit 搜尋筆數
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $request->validate([
            'first' => 'nullable|integer',
            'limit' => 'nullable|integer'
        ]);

        $data = [
            'first' => $request->first,
            'user_id' => Auth::id()
        ];

        if ($request->has('limit')) {
            $data['limit'] = $request->limit;
        }

        if ($request->has('title')) {
            $data['title'] = $request->title;
        }

        $out = $this->tasksRepository->getList($data);

        return [
            'result' => 'ok',
            'ret' => $out
        ];
    }

    /**
     * 建立任務
     *
     * 請求欄位：
     *  string title 標題
     *  string description 內容描述
     *  string status 狀態(pending,in-progress,completed)
     *  int assignee_id 分派user編號
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required|string',
            'status' => 'in:pending,in-progress,completed',
            'assignee_id' => 'nullable|exists:users,id'
        ]);

        $validated['user_id'] = Auth::id();
        $validated['created_at'] = now()->format('Y-m-d H:i:s');

        $out = Task::create($validated);

        return [
            'result' => 'ok',
            'ret' => $out
        ];
    }

    /**
     * 編輯任務
     * 
     *  請求欄位：
     *  string title 標題
     *  string description 內容描述
     *  string status 狀態(pending,in-progress,completed)
     *  int assignee_id 分派user編號
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @param int id 任務編號
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
         $validated = $request->validate([
            'title' => 'nullable|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in-progress,completed',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $task = Task::find($id);

        if (!$task) {
            throw new \RuntimeException('Not find task');
        }

        $userId = Auth::id();

        // 檢查是否有權限編輯
        $permissionCheck = $this->checkTaskPermissionService->checkPermission(
            'update',
            $task,
            $userId
        );

        if (!$permissionCheck) {
            throw new \RuntimeException("You don't have permission!");
        }

        $task->update($validated);
        $task->refresh();
        
        return [
            'result' => 'ok',
            'ret' => $task
        ];
    }

    /**
     * 刪除任務
     * 
     * @param int id 編號
     * 
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            throw new \RuntimeException('Not find task');
        }

        $userId = Auth::id();

        // 檢查是否有權限編輯
        $permissionCheck = $this->checkTaskPermissionService->checkPermission(
            'delete',
            $task,
            $userId
        );

        if (!$permissionCheck) {
            throw new \RuntimeException("You don't have permission!");
        }

        $task->delete();

        return [
            'result' => 'ok',
            'ret' => [
                'id' => $task->id,
                'title' => $task->title
            ]
        ];
    }
}
