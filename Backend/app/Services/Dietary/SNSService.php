<?php

namespace App\Services\Dietary;

use App\Events\MealStatusUpdated;
use App\Events\SnackStatusUpdated;
use App\Services\Dietary\Interfaces\SNSServiceInterface;
use App\Repositories\Dietary\Interfaces\SNSRepositoryInterface;
use Illuminate\Support\Facades\Log;


class SNSService implements SNSServiceInterface
{

    protected $sns_repository;

    //  Constructor
    public function __construct(SNSRepositoryInterface $sns_repository)
    {
        $this->sns_repository = $sns_repository;
    }

    //  Get SNS for specific date
    public function getSNS($date)
    {
        return $this->sns_repository->getSNS($date);
    }
    
    //  Update meal status
    public function updateMealStatus($id, $meal_status, $meal_time, $updated_by)
    {
        $this->sns_repository->updateMealStatus($id, $meal_status, $updated_by);

        Log::channel('sns')->info("[" . $id . "] [" . $meal_time . "] - [" . $updated_by . "] updated meal status to [" . $meal_status . "]");

        $updated = [
            'id' => $id,
            'meal_status' => $meal_status
        ];

        broadcast(new MealStatusUpdated($updated))->toOthers();
    }

    //  Update status
    public function updateStatus($id, $status, $user, $justification, $meal_time)
    {
        $meal_status = $status == 'ACCEPTED' ? 'P' : NULL;
        
        $this->sns_repository->updateStatus($id, $status, $user, $justification, $meal_status);

        Log::channel('sns')->info("[" . $id . "] [" . $meal_time . "] [" . $status . "] " . $user);

        $updated = [
            'id' => $id,
            'meal_status' => $meal_status,
            'status' => $status
        ];

        broadcast(new SnackStatusUpdated($updated))->toOthers();
    }

    //  Update SNS status from REQUESTED to ACKNOWLEDGED
    public function updateStatusToAcknowledged($user, $meal_time)
    {
        $this->sns_repository->updateStatusToAcknowledged($user, $meal_time);

        Log::channel('sns')->info("[ALL] [" . $meal_time . "] [ACKNOWLEDGED] " . $user);
            
        $updated = [
            'id' => 'ALL',
        ];

        broadcast(new SnackStatusUpdated($updated))->toOthers();
    }
}