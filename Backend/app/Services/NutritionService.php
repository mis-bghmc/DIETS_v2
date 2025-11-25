<?php

namespace App\Services;

use App\Services\Interfaces\NutritionServiceInterface;
use App\Repositories\Interfaces\NutritionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon as Date;


class NutritionService implements NutritionServiceInterface
{
    protected $nutrition_repository;

    //  Constructor
    public function __construct(NutritionRepositoryInterface $nutrition_repository)
    {
        $this->nutrition_repository = $nutrition_repository;
    }

    //  Fetch admitted patients nutrition
    public function getAdmittedPatientsNutrition()
    {
        return $this->nutrition_repository->getAdmittedPatientsNutrition();
    }

    //  Fetch patient nutrition
    public function getPatientNutrition($enccode)
    {
        return $this->nutrition_repository->getPatientNutrition($enccode);
    }

    //  Fetch patient nutrition assessment
    public function getPatientNutritionAssessment($enccode)
    {
        return $this->nutrition_repository->getPatientNutritionAssessment($enccode);
    }

    //  Store patient nutrition screening
    public function storePatientNutritionScreening($data)
    {

        $this->nutrition_repository->storePatientNutritionScreening([
            'enccode' => $data->enccode,
            'question1' => $data->answers[0],
            'question2' => $data->answers[1],
            'question3' => $data->answers[2],
            'question4' => $data->answers[3],
            'height' => $data->height,
            'weight' => $data->weight,
            'bmi' => $data->bmi,
            'datepost' => Date::now(),
            'entry_by' => $data->entryBy,
            'riskIndicator' => $data->riskIndicator
        ]);
    }

    //  Store patient nutrition assessment
    public function storePatientNutritionAssessment($data)
    {
        $this->nutrition_repository->storePatientNutritionAssessment([
            'enccode' => $data->enccode,
            'assessment' => $data->assessment,
            'assessed_by' => $data->assessed_by
        ]);
    }


    public function deletePatientNutritionScreening($id)
    {
        $this->nutrition_repository->deletePatientNutritionScreening($id);
    }

}
