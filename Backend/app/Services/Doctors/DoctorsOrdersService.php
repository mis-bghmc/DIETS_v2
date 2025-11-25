<?php

namespace App\Services\Doctors;

use App\Services\Doctors\Interfaces\DoctorsOrdersServiceInterface;
use App\Repositories\Doctors\Interfaces\DoctorsOrdersRepositoryInterface;
use App\Repositories\Dietary\Interfaces\MealsRepositoryInterface;
use App\Repositories\Dietary\Interfaces\DietsRepositoryInterface;
use App\Repositories\Dietary\Interfaces\SNSRepositoryInterface;
use App\Repositories\Interfaces\NotificationsRepositoryInterface;
use App\Repositories\Interfaces\PatientsRepositoryInterface;
use App\Repositories\Interfaces\DietTypesRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\NewDoctorsOrder;
use App\Helpers\HelperFunctions;
use Carbon\Carbon as Date;


class DoctorsOrdersService implements DoctorsOrdersServiceInterface
{
    protected $orders_repository;
    protected $meals_repository;
    protected $diets_repository;
    protected $sns_repository;
    protected $notifications_repository;
    protected $patients_repository;
    protected $diet_types_repository;

    //  Constructor
    public function __construct(
        DoctorsOrdersRepositoryInterface $orders_repository,
        MealsRepositoryInterface $meals_repository,
        DietsRepositoryInterface $diets_repository,
        SNSRepositoryInterface $sns_repository,
        NotificationsRepositoryInterface $notifications_repository,
        PatientsRepositoryInterface $patients_repository,
        DietTypesRepositoryInterface $diet_types_repository
    ) {
        $this->orders_repository = $orders_repository;
        $this->meals_repository = $meals_repository;
        $this->diets_repository = $diets_repository;
        $this->sns_repository = $sns_repository;
        $this->notifications_repository = $notifications_repository;
        $this->patients_repository = $patients_repository;
        $this->diet_types_repository = $diet_types_repository;
    }

    //  Fetch doctor's orders history
    public function getHistory($hpercode)
    {
        return $this->orders_repository->getHistory($hpercode);
    }

    //  Fetch doctor's order for SNS
    public function getSNS($docointkey)
    {
        $formatted_doctors_order = str_replace('@', '/', $docointkey);

        return $this->orders_repository->getSNS($formatted_doctors_order);
    }
    //  Fetch latest doctor's order
    public function getLatestOrder($docointkey)
    {
        return $this->orders_repository->getLatestOrder($docointkey);
    }

    //  Fetch all admitted patients doctor's orders
    public function getDoctorsOrders()
    {
        return $this->orders_repository->getDoctorsOrders();
    }

    //  Fetch total number of doctor's orders
    public function getDoctorsOrdersTotal()
    {
        return $this->orders_repository->getDoctorsOrdersTotal();
    }

    //  Update precautions
    public function updatePrecautions($id, $precaution)
    {
        $this->orders_repository->updatePrecautions($id, $precaution);
    }

    //  Save doctor's orders
    public function save($data)
    {
        $helperFunctions = new HelperFunctions();
        $priority = $helperFunctions->isPriorityDietCode($data->dietCode1, $data->dietCategory, $data->previousDiet);
        $is_within_grace_period = $helperFunctions->isWithinGracePeriod();

        DB::transaction(function () use ($data, $priority, $is_within_grace_period) {
            $today = Date::now();
            $status = 'P';
            $locked = 'N';
            $notification = null;
            $notification_message = 'New';
            $patient = $this->patients_repository->getPatientDiet($data->enccode);

            if ($priority) {
                $status = 'A';
                $locked = 'Y';
                $notification = [
                    'from_diet' => $patient?->dietname ?? '_',
                    'from_ons' => $patient?->ons,
                    'from_ons2' => $patient?->ons2,
                    'to_diet' => $data->dietName,
                    'to_ons' => $data->ons,
                    'to_ons2' => $data->ons2
                ];
                $notification_message = 'Priority';

                $this->orders_repository->lockAndDeactivate($data->hpercode);
                $this->savePriority($data, $today);

            } else if ($is_within_grace_period) {
                $notification = [
                    'from_diet' => $patient?->dietname ?? '_',
                    'from_ons' => $patient?->ons,
                    'from_ons2' => $patient?->ons2,
                    'to_diet' => $data->dietName,
                    'to_ons' => $data->ons,
                    'to_ons2' => $data->ons2
                ];
                $notification_message = 'Late';
            }

            $this->orders_repository->lockAndDeactivatePending($data->hpercode);

            if (is_array($data->precautions)) {
                $precaution = implode(', ', $data->precautions);
            } else {
                $precaution = $data->precautions;
            }

            $this->orders_repository->storeDiet([
                'docointkey' => $data->enccode . $today,
                'hpercode' => $data->hpercode,
                'enccode' => $data->enccode,
                'dietcode' => $data->dietCode1,
                'dietcode2' => $data->dietCode2,
                'ordreas' => $data->remarks,
                'licno' => $data->entryBy,
                'entryby' => $data->entryBy,
                'feedingMode' => $data->feedingMode,
                'feedingDuration' => $data->feedingDuration,
                'feedingFrequency' => $data->feedingFrequency,
                'dodate' => $today,
                'dostatus' => $status,
                'locked' => $locked,
                'precaution' => $precaution ?? null
            ]);

            if ($data->onsType) {
                $ons_frequency = $data->onsFrequency;

                if (is_array($ons_frequency)) {
                    $ons_frequency = implode(', ', $ons_frequency);
                }

                $this->orders_repository->storeSNS([
                    'docointkey' => $data->enccode . $today,
                    'enccode' => $data->enccode,
                    'hpercode' => $data->hpercode,
                    'onsFormula' => $data->onsType,
                    'onsFormula2' => $data->onsType2,
                    'onsFrequency' => $ons_frequency,
                    'onsDescription' => $data->onsDescription,
                    'date_created' => $today,
                ]);
            }

            if ($data->calories || $data->protein || $data->fats || $data->carbohydrates || $data->fiber || $data->dilution) {
                $this->orders_repository->storeNutrients([
                    'docointkey' => $data->enccode . $today,
                    'enccode' => $data->enccode,
                    'calories' => $data->calories,
                    'protein' => $data->protein,
                    'fats' => $data->fats,
                    'carbohydrates' => $data->carbohydrates,
                    'fiber' => $data->fiber,
                    'dilution' => $data->dilution,
                    'volume' => $data->dilution * 1000
                ]);
            }

            if ($data->allergies) {
                $this->patients_repository->updatePatientFoodAllergies($data->hpercode, $data->allergies, $data->entryBy);
            }

            if ($data->reproductiveStatus !== 'NA' || $data->reproductiveStatus !== null) {
                $this->patients_repository->storeMaternalDetails([
                    'docointkey' => $data->enccode . $today,
                    'enccode' => $data->enccode,
                    'hpercode' => $data->hpercode,
                    'status' => $data->reproductiveStatus,
                    'date_created' => $today
                ]);
            }

            if ($notification) {
                $this->notifications_repository->storeNotification([
                    'docointkey' => $data->enccode . $today,
                    'priority' => $locked,
                    'message' => json_encode($notification),
                    'created_at' => $today
                ]);
            }

            try {
                broadcast(new NewDoctorsOrder($notification_message));

            } catch (\Throwable $e) {
                Log::error($e);
            }

        });
    }

    //  Save doctor's orders - priority
    public function savePriority($data, $date)
    {
        DB::transaction(function () use ($data, $date) {
            $history = $this->diets_repository->getLatestMealTimeByDate($date);
            $diet_type = $this->diet_types_repository->getType($data->dietcode1);

            $meal_time = $history->meal_time;
            $created_at = $history->created_at;
            $created_at_ons = $created_at;

            $sns_times = array('BREAKFAST' => 'AM', 'LUNCH' => 'PM', 'DINNER' => 'MN');
            $sns_time = array_key_exists($meal_time, $sns_times) ? $sns_times[$meal_time] : 'AM';
            $sns_frequency = is_array($data->onsFrequency) ? $data->onsFrequency : json_decode($data->onsFrequency);

            if (!array_key_exists($meal_time, $sns_times)) {
                $created_at_ons = date('Y-m-d') . ' 04:30:00';

                if ($diet_type != 'EN') {
                    $meal_time = 'BREAKFAST';
                    $created_at = date('Y-m-d') . ' 04:30:00';
                }
            }

            $this->meals_repository->cancelCurrentMeal('patients_diet_history', $data->enccode, $created_at, $meal_time);
            $this->meals_repository->cancelCurrentMeal('patients_snack_history', $data->enccode, $created_at, $sns_time);

            if ($diet_type === 'EN') {
                for ($i = 1; $i <= $data->feedingFrequency; $i++) {
                    $this->diets_repository->storeHistory([
                        'docointkey' => $data->enccode . $date,
                        'meal_time' => $i,
                        'created_at' => date('Y-m-d'),
                        'updated_at' => $date,
                        'updated_by' => $data->entryBy
                    ]);
                }

            } else {
                $this->diets_repository->storeHistory([
                    'docointkey' => $data->enccode . $date,
                    'meal_time' => $meal_time,
                    'created_at' => $created_at,
                    'updated_at' => $date,
                    'updated_by' => $data->entryBy
                ]);
            }

            if ($sns_frequency != null && in_array($sns_time, $sns_frequency)) {
                $this->sns_repository->storeHistory([
                    'docointkey' => $data->enccode . $date,
                    'meal_time' => $sns_time,
                    'status' => 'REQUESTED',
                    'created_at' => $created_at_ons,
                    'updated_at' => $date,
                    'updated_by' => $data->entryBy
                ]);
            }
        });
    }

    //  Save doctor's order as draft
    public function saveDraft($data)
    {
        $this->orders_repository->saveDraft($data);
    }

}


