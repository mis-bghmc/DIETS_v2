<?php

namespace App\Services\Interfaces;

interface EmployeesServiceInterface
{
    public function getEmployee($id);
    public function getAllowedPersonnel();
}