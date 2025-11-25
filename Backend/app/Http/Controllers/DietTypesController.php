<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Interfaces\DietTypesServiceInterface;

class DietTypesController extends Controller
{
    protected $diet_types_service;

    //  Constructor
    public function __construct(DietTypesServiceInterface $diet_types_service)
    {
        $this->diet_types_service = $diet_types_service;
    }

    //  Fetch all active diet types
    public function active()
    {
        return response()->json($this->diet_types_service->getAllActive());
    }

    //  Fetch all enteral diet types
    public function enteral()
    {
        return response()->json($this->diet_types_service->getEnteralAll());
    }

    //  Fetch all enteral feeding modes
    public function modes()
    {
        return response()->json($this->diet_types_service->getEnteralFeedingModes());
    }

    //  Update diet type status
    public function updateStatus(Request $request)
    {
        $this->diet_types_service->updateStatus($request->dietcode, $request->status, $request->updated_by);

        return response()->json([
            'message' => 'Diet type status updated successfully!'
        ]);
    }
}
