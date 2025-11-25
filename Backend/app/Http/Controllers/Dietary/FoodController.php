<?php

namespace App\Http\Controllers\Dietary;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Dietary\Interfaces\FoodServiceInterface;

class FoodController extends Controller
{
    protected $food_service;

    //  Constructor
    public function __construct(FoodServiceInterface $food_service)
    {
        $this->food_service = $food_service;
    }

    //  History
    public function history(Request $request)
    {
        return response()->json($this->food_service->getHistory($request->date, $request->ward, $request->meal_time));
    }

    //  Verify served meals
    public function verify(Request $request)
    {
        $this->food_service->verify(
            $request->wardname,
            $request->meal_time,
            $request->total,
            $request->served,
            $request->not_served,
            $request->not_given,
            $request->unlisted_patients,
            $request->remarks,
            $request->server_id,
            $request->nurse_id,
            $request->nurse_signature
        );

        return response()->json([
            'message' => 'Food service verified.'
        ]);
    }
}
