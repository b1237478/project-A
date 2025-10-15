<?php

namespace App\Services;

Class CheckTaskPermissionService
{
    /**
     * 檢查權限
     * 
     * @param string $method (update, delete)
     * @param object $task
     * @param int $userId 使用者ID
     * 
     * @return boolean 
     */
    public function checkPermission($method, $task, $userId)
    {
        if ($method === 'update'
            && ($userId == $task->user_id
            || $userId == $task->assignee_id)
        ) {
            return true;
        }

        if ($method === 'delete' && $userId == $task->user_id) {
            return true;
        }

        return false;   
    }
}
