<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    const DAY = 86400;

    /**
     * 设置表名
     * @param $tableName
     */
    public function setTableName($tableName)
    {
        if ($tableName != null) {
            $this->setTable($tableName);
        }
    }

    /**
     * 设置表名
     * @param $tableName
     */
    public static function tableName($tableName, $connection = 'mysql')
    {
        $instance = new static;
        $instance->setTableName($tableName);
        $instance->setConnection($connection);
        return $instance->newQuery();
    }

    /**
     * 排序取最新的
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('updated_at', 'desc')->orderBy('created_at', 'desc');
    }

    /**
     * 取某时间之后的数据
     */
    public function scopeCreatedAfter($query, $time)
    {
        return $query->where('created_at', '>=', $time);
    }

    /**
     * 取某时间之前的数据
     */
    public function scopeCreatedBefore($query, $time)
    {
        return $query->where('created_at', '<=', $time);
    }

    /**
     * 根据ID修改
     */
    public function scopeUpdateById($query, $id, $data)
    {
        return $query->whereId($id)->update($data);
    }

    /**
     * 搜索
     */
    public function scopeSearch($query, $data, $orwhere = null)
    {
        if ($orwhere) {
            return $query->where($data)->orWhere($orwhere);
        } else {
            return $query->where($data);
        }
    }

    /*
     * 取多条数据ids
     */
    public function scopeIds($query, $ids, $notIn = false)
    {
        if ($notIn) {
            return $query->whereNotIn('id', $ids);
        } else {
            return $query->whereIn('id', $ids);
        }
    }
}