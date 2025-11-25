<?php

namespace App\Services\Dietary;

use App\Events\MealStatusUpdated;
use App\Services\Dietary\Interfaces\FoodServiceInterface;
use App\Repositories\Dietary\Interfaces\FoodRepositoryInterface;
use App\Repositories\Dietary\Interfaces\DietsRepositoryInterface;
use App\Repositories\Dietary\Interfaces\SNSRepositoryInterface;
use Illuminate\Support\Facades\DB;


class FoodService implements FoodServiceInterface
{
    protected $food_repository;
    protected $diets_repository;
    protected $sns_repository;

    //  Constructor
    public function __construct(FoodRepositoryInterface $food_repository, DietsRepositoryInterface $diets_repository, SNSRepositoryInterface $sns_repository)
    {
        $this->food_repository = $food_repository;
        $this->diets_repository = $diets_repository;
        $this->sns_repository = $sns_repository;
    }

    //  Get patient's diet history
    public function getHistory($date, $ward, $meal_time)
    {
        return $this->food_repository->getHistory($date, $ward, $meal_time);
    }

    //  Verify served meals
    public function verify($ward_name, $meal_time, $total, $served, $not_served, $not_given, $unlisted_patients, $remarks, $server_id, $nurse_id, $nurse_signature)
    {
        DB::transaction(function () use ($ward_name, $meal_time, $total, $served, $not_served, $not_given, $unlisted_patients, $remarks, $server_id, $nurse_id, $nurse_signature)
        {
            $this->food_repository->storeHistory([
                'wardname' => $ward_name,
                'meal_time' => $meal_time,
                'total' => $total,
                'served' => $served,
                'not_served' => $not_served,
                'not_given' => $not_given,
                'unlisted_patients' => $unlisted_patients,
                'remarks' => $remarks,
                'server' => $server_id,
                'nurse' => $nurse_id,
                'nurse_signature' => $nurse_signature
            ]);
    
            $ons_times = array('AM', 'PM', 'MN');
    
            if(in_array($meal_time, $ons_times))
            {
                $this->sns_repository->updateVerifiedStatus($ward_name, $meal_time);
            }
            else
            {
                $this->diets_repository->updateVerifiedStatus($ward_name, $meal_time);
            }
    
            $updated = [
                'wardname' => $ward_name,
                'meal_time' => $meal_time,
                'verified' => 'Y',
            ];
    
            broadcast(new MealStatusUpdated($updated))->toOthers();
        });
    }
}