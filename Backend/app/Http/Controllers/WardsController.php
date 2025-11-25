<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Interfaces\WardsServiceInterface;

class WardsController extends Controller
{

    protected $wards_service;

    //  Constructor
    public function __construct(WardsServiceInterface $wards_service)
    {
        $this->wards_service = $wards_service;
    }

    //  Fetch active wards
    public function active()
    {
        return response()->json($this->wards_service->getActiveWards());
    }
}
