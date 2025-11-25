<?php

namespace App\Http\Controllers\Dietary;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Dietary\Interfaces\DietsServiceInterface;

class DietsController extends Controller
{
    protected $diets_service;

    //  Constructor
    public function __construct(DietsServiceInterface $diets_service)
    {
        $this->diets_service = $diets_service;
    }

    //  Update meal status
    public function update(Request $request)
    {
        $this->diets_service->updateMealStatus($request->id, $request->meal_status, $request->meal_time, $request->updated_by);

        return response()->json([
            'message' => 'Meal status updated successfully.'
        ]);
    }

    public function updateStatusDischarge($hpercode)
    {

        $this->diets_service->updateMealStatusAfterDischarge($hpercode);

        return response()->json([
            'message' => 'Diet status updated successfully.'
        ]);
    }
}
