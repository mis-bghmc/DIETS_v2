<?php

namespace App\Services\Dietary;

use App\Events\MealStatusUpdated;
use App\Services\Dietary\Interfaces\DietsServiceInterface;
use App\Repositories\Dietary\Interfaces\DietsRepositoryInterface;
use Illuminate\Support\Facades\Log;

class DietsService implements DietsServiceInterface
{

    protected $diets_repository;

    //  Constructor
    public function __construct(DietsRepositoryInterface $diets_repository)
    {
        $this->diets_repository = $diets_repository;
    }

    //  Get latest meal time
    public function getLatestMealTime()
    {
        return $this->diets_repository->getLatestMealTime();
    }

    //  Update meal status
    public function updateMealStatus($id, $meal_status, $meal_time, $updated_by)
    {
        $this->diets_repository->updateMealStatus($id, $meal_status, $updated_by);

        Log::channel('diet')->info("[" . $id . "] [" . $meal_time . "] [" . $meal_status . "] " . $updated_by);

        $updated = [
            'id' => $id,
            'meal_status' => $meal_status
        ];

        broadcast(new MealStatusUpdated($updated))->toOthers();
    }

    public function updateMealStatusAfterDischarge($hpercode)
    {

        $this->diets_repository->updateMealStatusAfterDischarge($hpercode);

        Log::channel('diet')->info("[" . $hpercode . "] Updated doctors orders status to I " . $hpercode);

    }
}