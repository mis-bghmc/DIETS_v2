<?php

namespace App\Services;

use App\Services\Interfaces\WardsServiceInterface;
use App\Repositories\Interfaces\WardsRepositoryInterface;
use Illuminate\Support\Facades\DB;


class WardsService implements WardsServiceInterface
{
    protected $wards_repository;

    //  Constructor
    public function __construct(WardsRepositoryInterface $wards_repository)
    {
        $this->wards_repository = $wards_repository;
    }

    //  Fetch active wards
    public function getActiveWards()
    {
        return $this->wards_repository->getActiveWards();
    }
}