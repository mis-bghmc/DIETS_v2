<?php

namespace App\Services\Dietary;

use App\Events\NewDoctorsOrder;
use App\Services\Dietary\Interfaces\MealsServiceInterface;
use App\Repositories\Dietary\Interfaces\MealsRepositoryInterface;
use App\Repositories\Dietary\Interfaces\DietsRepositoryInterface;
use App\Repositories\Dietary\Interfaces\SNSRepositoryInterface;
use App\Repositories\Doctors\Interfaces\DoctorsOrdersRepositoryInterface;
use App\Repositories\Interfaces\NotificationsRepositoryInterface;
use Illuminate\Support\Facades\DB;


class MealsService implements MealsServiceInterface
{
    protected $meals_repository;
    protected $diets_repository;
    protected $sns_repository;
    protected $orders_repository;
    protected $notifications_repository;

    //  Constructor
    public function __construct(
        MealsRepositoryInterface $meals_repository, 
        DietsRepositoryInterface $diets_repository, 
        SNSRepositoryInterface $sns_repository, 
        DoctorsOrdersRepositoryInterface $orders_repository,
        NotificationsRepositoryInterface $notifications_repository)
    {
        $this->meals_repository = $meals_repository;
        $this->diets_repository = $diets_repository;
        $this->sns_repository = $sns_repository;
        $this->orders_repository = $orders_repository;
        $this->notifications_repository = $notifications_repository;
    }

    //  Get patient's diet history
    public function getHistory($enccode)
    {
        return $this->meals_repository->getHistory($enccode);
    }
    
    //  Get latest patients diets
    public function getList()
    {
        return [
            'O' => $this->diets_repository->getListOral(),
            'E' => $this->diets_repository->getListEnteral(),
            'ONS' => $this->sns_repository->getList()
        ];
    }

    // Get patients diet by specific date, mealtime, and ward
    public function getListPrintable($date, $mealtime, $ward)
    {
        $diet = $this->diets_repository->getListPrintable($date, $mealtime, $ward);
        $sns = $this->sns_repository->getListPrintable($date, $mealtime, $ward);
        
        return array_merge($diet, $sns);
    }

    // Get patients diet by specific date
    public function getListByDate($date)
    {
        $diet = $this->diets_repository->getListByDate($date);
        $sns = $this->sns_repository->getListByDate($date);

        return array_merge($diet, $sns);
    }

    //  Get diet tags
    public function getTags($group, $option, $wards)
    {
        return $group == 'Oral' ? $this->diets_repository->getTagsOral($option, $wards) 
        : ($group == 'Enteral' ?  $this->diets_repository->getTagsEnteral($option, $wards) 
        : $this->sns_repository->getTags($option, $wards));
    }

    //  Get meal census
    public function getCensus($date)
    {
        $diet = $this->diets_repository->getCensus($date);
        $sns = $this->sns_repository->getCensus($date);

        return array_merge($diet, $sns);
    }

    //  Accept late doctor's order
    public function acceptLateOrder($ons_frequency, $enccode, $hpercode, $docointkey, $updated_by)
    {
        DB::transaction(function () use ($ons_frequency, $enccode, $hpercode, $docointkey, $updated_by) {
            $history = $this->diets_repository->getLatestMealTime();

            $sns_times = array('BREAKFAST' => 'AM', 'LUNCH' => 'PM', 'DINNER' => 'MN');
            $sns_time = $sns_times[$history->meal_time];

            $sns_frequency = json_decode($ons_frequency);

            $this->meals_repository->cancelCurrentMeal('patients_diet_history', $enccode, $history->created_at, $history->meal_time);
            $this->meals_repository->cancelCurrentMeal('patients_snack_history', $enccode, $history->created_at, $sns_time);

            $this->diets_repository->storeHistory([
                'docointkey' => $docointkey,
                'meal_time' => $history->meal_time,
                'meal_status' => 'P',
                'created_at' => $history->created_at,
                'updated_at' => date("Y-m-d H:i:s"),
                'updated_by' => $updated_by
            ]);

            if ($sns_frequency != null && in_array($sns_time, $sns_frequency)){
                $this->sns_repository->storeHistory([
                    'docointkey' => $docointkey,
                    'meal_time' => $sns_time,
                    'status' => 'ACKNOWLEDGED',
                    'created_at' => $history->created_at,
                    'updated_at' => date("Y-m-d H:i:s"),
                    'updated_by' => $updated_by
                ]);
            }
            
            $this->orders_repository->lockAndDeactivate($hpercode);
            $this->orders_repository->activate($docointkey);
            $this->notifications_repository->acknowledge($docointkey, $updated_by);

            broadcast(new NewDoctorsOrder("Updated"))->toOthers();
        });
    }
}