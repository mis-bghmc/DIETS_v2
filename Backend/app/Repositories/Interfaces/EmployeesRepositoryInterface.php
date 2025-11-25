<?php

namespace App\Repositories\Interfaces;

interface EmployeesRepositoryInterface
{
    public function getEmployee($id);
    public function getAllowedPersonnel();
}