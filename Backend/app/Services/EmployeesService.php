<?php

namespace App\Services;

use App\Services\Interfaces\EmployeesServiceInterface;
use App\Repositories\Interfaces\EmployeesRepositoryInterface;
use Illuminate\Support\Facades\DB;


class EmployeesService implements EmployeesServiceInterface
{
    protected $employees_repository;

    //  Constructor
    public function __construct(EmployeesRepositoryInterface $employees_repository)
    {
        $this->employees_repository = $employees_repository;
    }

    //  Fetch employee details
    public function getEmployee($id)
    {
        return $this->employees_repository->getEmployee($id);
    }

    //  Fetch allowed personnels
    public function getAllowedPersonnel()
    {
        return $this->employees_repository->getAllowedPersonnel();
    }
}