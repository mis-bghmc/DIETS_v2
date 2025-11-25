<?php

namespace App\Repositories\Dietary;

use Illuminate\Support\Facades\DB;
use App\Repositories\Dietary\Interfaces\FoodRepositoryInterface;

class FoodRepository implements FoodRepositoryInterface
{
    //  Food Service
    public function getHistory($date, $ward, $meal_time)
    {
        return DB::select("SELECT * FROM getFoodService(?) WHERE wardname = ? AND meal_time = ?", [$date, $ward, $meal_time]);
    }

    //  Insert into food_service
    public function storeHistory($data)
    {
        DB::table('food_service')->insert($data);
    }
}