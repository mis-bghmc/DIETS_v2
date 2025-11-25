<?php

namespace App\Repositories\Interfaces;

interface UsersRepositoryInterface
{
    public function getUserDetails($id);
    public function getUserLevels();
    public function searchEmployeeUsername($employeeid);
}
