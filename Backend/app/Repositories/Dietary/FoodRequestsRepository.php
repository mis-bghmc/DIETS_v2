<?php

namespace App\Repositories\Dietary;

use Illuminate\Support\Facades\DB;
use App\Repositories\Dietary\Interfaces\FoodRequestsRepositoryInterface;
use Carbon\Carbon as Date;

class FoodRequestsRepository implements FoodRequestsRepositoryInterface
{
    //  Fetch requests
    public function getFoodRequests($date)
    {
        return DB::table('food_requests AS fr')
                ->leftJoin('hospital.dbo.hpersonal AS emp', 'emp.employeeid', '=', 'fr.updated_by')
                ->select('fr.*', 'emp.lastname', 'emp.firstname')
                ->whereRaw("CONVERT(varchar, created_at, 120) like '$date%'")
                ->orderByRaw("CASE WHEN status = 'CREATED' THEN 0 ELSE 1 END")
                ->orderBy('fr.updated_at', 'desc')
                ->get();
    }

    //  Insert
    public function storeFoodRequests($data)
    {
        DB::table('food_requests')->insert($data);
    }

    //  Update 
    public function updateFoodRequests($data)
    {
        DB::table('food_requests')
            ->where('id', '=', $data->id)
            ->update([
                'status' => $data->status,
                'update_remarks' => $data->update_remarks,
                'updated_by' => $data->updated_by,
                'updated_at' => Date::now()
            ]);
    }
}