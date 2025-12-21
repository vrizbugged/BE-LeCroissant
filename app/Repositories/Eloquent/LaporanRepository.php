<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\LaporanRepositoryInterface;

class LaporanRepository implements LaporanRepositoryInterface
{
    protected $model;

    public function __construct(Laporan $model)
    {
        $this->model = $model;
    }
}
