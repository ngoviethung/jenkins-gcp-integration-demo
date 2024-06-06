<?php

namespace App\Repositories;

use App\Models\Challenge;

class ChallengeRepository extends BaseRepository
{
    protected $model;

    public function __construct(Challenge $model)
    {
        $this->model = $model;
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

    public function whereBetweenOrder($column, $start, $end, $order_column, $order = 'DESC'){
        
        return $this->model->whereBetween($column, [$start, $end])
                            ->orderBy($order_column, $order)
                            ->get();
            
    }


}
