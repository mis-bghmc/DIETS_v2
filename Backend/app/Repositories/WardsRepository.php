<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\WardsRepositoryInterface;

class WardsRepository implements WardsRepositoryInterface
{
    //  Fetch active wards
    public function getActiveWards()
    {
        return DB::table('hospital.dbo.hward')->where('wardstat', '=', 'A')->orderBy('wardname')->select('wardcode', 'wardname')->get();
    }
}