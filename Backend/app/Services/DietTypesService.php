<?php

namespace App\Services;

use App\Services\Interfaces\DietTypesServiceInterface;
use App\Repositories\Interfaces\DietTypesRepositoryInterface;
use Illuminate\Support\Facades\DB;


class DietTypesService implements DietTypesServiceInterface
{
    protected $diet_types_repository;

    //  Constructor
    public function __construct(DietTypesRepositoryInterface $diet_types_repository)
    {
        $this->diet_types_repository = $diet_types_repository;
    }

    //  Fetch all active diet types
    public function getAllActive()
    {
        return $this->diet_types_repository->getAllActive();
    }

    //  Fetch all enteral diet types
    public function getEnteralAll()
    {
        return $this->diet_types_repository->getEnteralAll();
    }

    //  Fetch all enteral feeding modes
    public function getEnteralFeedingModes()
    {
        return $this->diet_types_repository->getEnteralFeedingModes();
    }

    //  Update diet type status
    public function updateStatus($dietcode, $status, $updated_by)
    {
        $this->diet_types_repository->updateStatus($dietcode, $status, $updated_by);
    }
}