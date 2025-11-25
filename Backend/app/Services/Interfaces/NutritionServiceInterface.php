<?php

namespace App\Services\Interfaces;

interface NutritionServiceInterface
{
    public function getAdmittedPatientsNutrition();
    public function getPatientNutrition($enccode);
    public function getPatientNutritionAssessment($enccode);
    public function storePatientNutritionScreening($data);
    public function storePatientNutritionAssessment($data);
    public function deletePatientNutritionScreening($enccode);
}
