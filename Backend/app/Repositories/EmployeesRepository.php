<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\EmployeesRepositoryInterface;

class EmployeesRepository implements EmployeesRepositoryInterface
{
    //  Fetch employee details
    public function getEmployee($id)
    {
        return DB::table('hospital.dbo.hpersonal')
                ->select('employeeid', 'lastname', 'firstname', 'middlename', 'postitle')
                ->where('employeeid', '=', $id)
                ->get();
    }

    //  Fetch allowed personnels
    public function getAllowedPersonnel()
    {
        return DB::table('allowed_personnel')->get();
    }
}