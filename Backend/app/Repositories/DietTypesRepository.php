<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\DietTypesRepositoryInterface;

class DietTypesRepository implements DietTypesRepositoryInterface
{
    //  Fetch all active diet types
    public function getAllActive()
    {
        return DB::table('diet')->where('dietstat', '=', 'A')->orderBy('dietname')->get();
    }

    //  Fetch all enteral diet types
    public function getEnteralAll()
    {
        return DB::table('diet')->where('diettype', '=', 'EN')->orderBy('dietname')->get();
    }

    //  Fetch all enteral feeding modes
    public function getEnteralFeedingModes()
    {
        return DB::table('feeding_modes')->get();
    }

    //  Fetch diet type
    public function getType($dietcode)
    {
        return DB::table('diet')->where('dietcode', $dietcode)->value('diettype');
    }

    //  Update diet type status
    public function updateStatus($dietcode, $status, $updated_by)
    {
        DB::table('diet')
            ->where('dietcode', '=', $dietcode)
            ->update([
                'dietstat' => $status,
                'updated_by' => $updated_by,
                'updated_at' => date("Y-m-d H:i:s")
            ]);
    }
}