<?php

namespace App\Repositories\Interfaces;

interface PatientsRepositoryInterface
{
    public function getAdmittedPatients();
    public function getMyPatients($id);
    public function getPatientDetails($enccode);
    public function getPatientDiet($enccode);
    public function getPatientMeasurements($enccode);
    public function getAllergies();
    public function getPrecautions();
    public function updatePatientFoodAllergies($hpercode, $allergies, $licno);
    public function storeMaternalDetails($data);
    public function findAdmEncounter($enccode);
}