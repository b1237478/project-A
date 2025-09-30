<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\Notes;

class NotesRepository
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
        $out = DB::table('notes')
            ->orderBy('id', 'desc');

        if (isset($data['title'])) {
            $out->where('title', 'like', "%{$data['title']}%");
        }

        return $out;
    }

    /**
     * 取得note的清單
     *
     * @param array $data 搜尋條件
     *
     * @return collection
     */
    public function getlist($data)
    {
        $out = $this->_queryBuilder($data);

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