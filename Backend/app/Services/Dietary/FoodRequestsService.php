<?php

namespace App\Services\Dietary;

use App\Events\FoodRequests;
use App\Services\Dietary\Interfaces\FoodRequestsServiceInterface;
use App\Repositories\Dietary\Interfaces\FoodRequestsRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon as Date;

class FoodRequestsService implements FoodRequestsServiceInterface
{
    protected $food_requests_repository;

    //  Constructor
    public function __construct(FoodRequestsRepositoryInterface $food_requests_repository)
    {
        $this->food_requests_repository = $food_requests_repository;
    }

    //  Fetch requests
    public function getFoodRequests($date)
    {
        return $this->food_requests_repository->getFoodRequests($date);
    }

    //  Insert
    public function storeFoodRequests($data)
    {
        DB::transaction(function () use ($data)
        {
            foreach ($data->meals as $meal) 
            {
                $this->food_requests_repository->storeFoodRequests([
                    'requesting' => $data->requesting,
                    'meal' => $meal,
                    'qnty' => $data->qnty,
                    'remarks' => $data->remarks,
                    'request_date' => $data->request_date,
                    'updated_by' => $data->updated_by
                ]);
            }

            broadcast(new FoodRequests(Date::now()))->toOthers();

            Log::channel('food_requests')->info("[" . $data->requesting . "] [" . implode(",", $data->meals) . "] [" . $data->qnty . "] " . $data->updated_by);
        });
    }

    //  Update
    public function updateFoodRequests($data)
    {
        $this->food_requests_repository->updateFoodRequests($data);

        broadcast(new FoodRequests(Date::now()))->toOthers();

        Log::channel('food_requests')->info("[" . $data->id . "] [" . $data->status . "] " . $data->updated_by);
    }
}