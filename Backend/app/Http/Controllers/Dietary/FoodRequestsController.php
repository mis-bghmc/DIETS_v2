<?php

namespace App\Http\Controllers\Dietary;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Dietary\Interfaces\FoodRequestsServiceInterface;

class FoodRequestsController extends Controller
{
    protected $food_requests_service;

    //  Constructor
    public function __construct(FoodRequestsServiceInterface $food_requests_service)
    {
        $this->food_requests_service = $food_requests_service;
    }

    //  Fetch requests
    public function index(Request $request)
    {
        $date = $request->route('date');
        
        return $this->food_requests_service->getFoodRequests($date);
    }

    //  Insert
    public function create(Request $request)
    {
        $this->food_requests_service->storeFoodRequests($request);

        return response()->json([
            'message' => 'Food requests created successfully.'
        ]);
    }

    //  Update
    public function update(Request $request)
    {
        $this->food_requests_service->updateFoodRequests($request);

        return response()->json([
            'message' => 'Food requests updated successfully.'
        ]);
    }
}
