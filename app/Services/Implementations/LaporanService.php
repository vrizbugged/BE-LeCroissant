<?php

namespace App\Services\Implementations;

use App\Services\Contracts\LaporanServiceInterface;
use App\Repositories\Contracts\LaporanRepositoryInterface;

class LaporanService implements LaporanServiceInterface
{
    protected $repository;

    public function __construct(LaporanRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    // Implementasi metode dari LaporanServiceInterface
}
