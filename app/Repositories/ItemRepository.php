<?php

namespace App\Repositories;

use App\Models\Item;

class ItemRepository extends BaseRepository
{
    protected $model;

    public function __construct(Item $model)
    {
        $this->model = $model;
    }

    public function countWhere($column, $value)
    {
        return $this->model->where($column, $value)->count();
    }

    public function countWhereIn($column, array $value)
    {
        return $this->model->whereIn($column, $value)->count();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function firstOrCreate(array $condition, array $data)
    {
        return $this->model->firstOrCreate($condition, $data);
    }
    public function first(array $condition)
    {
        return $this->model->where($condition)->first();
    }


}
