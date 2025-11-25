<?php

namespace App\Services\Interfaces;

interface PatientsServiceInterface
{
    public function getAdmittedPatients();
    public function getMyPatients($id);
    public function getPatientDetails($enccode);
    public function getPatientDiet($enccode);
    public function getPatientMeasurements($enccode);
    public function getAllergies();
    public function getPrecautions();
    public function updatePatientFoodAllergies($hpercode, $allergies, $licno);
}