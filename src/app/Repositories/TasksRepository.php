<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class TasksRepository
{
    /**
     * 建立搜尋條件
     *
     * @param array $data 搜尋條件
     *
     * @return collection
     */
    private function _queryBuilder($data)
    {
        $out = DB::table('tasks')
            ->orderBy('id', 'desc');

        if (isset($data['title'])) {
            $out->where('title', 'like', "%{$data['title']}%");
        }

        return $out;
    }

    /**
     * 取得task的清單
     *
     * @param array $data 搜尋條件
     *
     * @return collection
     */
    public function getList($data)
    {
        $out = $this->_queryBuilder($data);
        $out->where('user_id', $data['user_id'])
            ->orWhere('assignee_id', $data['user_id']);

        if (isset($data['first'])) {
            $out->skip($data['first']);
        }

        if (isset($data['limit'])) {
            $out->take($data['limit']);
        }

        return $out->get();
    }

    /**
     * 計算總筆數
     *
     * @param array $data 搜尋條件
     *
     * @return int
     */
    public function countTotal($data)
    {
        $out = $this->_queryBuilder($data);

        return $out->count();
    }
}