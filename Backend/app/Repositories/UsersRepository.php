<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\UsersRepositoryInterface;

class UsersRepository implements UsersRepositoryInterface
{
    //  Fetch user details
    public function getUserDetails($id)
    {
        return DB::table('hospital.dbo.user_acc')
            ->join('hospital.dbo.hpersonal', 'hospital.dbo.user_acc.employeeid', '=', 'hospital.dbo.hpersonal.employeeid')
            ->where('hospital.dbo.user_acc.employeeid', '=', $id)
            ->get([
                'hospital.dbo.user_acc.user_name',
                'hospital.dbo.user_acc.user_level',
                'hospital.dbo.hpersonal.postitle',
                'hospital.dbo.hpersonal.lastname',
                'hospital.dbo.hpersonal.firstname',
                'hospital.dbo.hpersonal.middlename'
            ])
            ->first();
    }

    public function getUserLevels()
    {

        return DB::table('user_levels')->get();
    }

    public function getEmployeeDetails($id)
    {
        return DB::table('hospital.dbo.hpersonal')
            ->select('employeeid', 'lastname', 'firstname', 'middlename', 'postitle')
            ->where('employeeid', '=', $id)
            ->get();
    }

    public function searchEmployeeUsername($employeeid)
    {

        return DB::table('hospital.dbo.user_acc')
            ->where('employeeid', '=', $employeeid)
            ->value('user_name');
    }
}