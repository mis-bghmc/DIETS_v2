<?php

namespace App\Services;

use App\Services\Interfaces\PatientsServiceInterface;
use App\Repositories\Interfaces\PatientsRepositoryInterface;
use Illuminate\Support\Facades\DB;


class PatientsService implements PatientsServiceInterface
{
    protected $patients_repository;

    //  Constructor
    public function __construct(PatientsRepositoryInterface $patients_repository)
    {
        $this->patients_repository = $patients_repository;
    }

    //  Fetch active patients
    public function getAdmittedPatients()
    {
        return $this->patients_repository->getAdmittedPatients();
    }

    //  Fetch my patients
    public function getMyPatients($id)
    {
        return $this->patients_repository->getMyPatients($id);
    }

    //  Fetch patient details
    public function getPatientDetails($enccode)
    {
        if (str_starts_with($enccode, 'ER')) {
            $data = $this->patients_repository->findAdmEncounter($enccode);
            $enccode = $data[0]->enccode ?? $enccode;
        }
        return $this->patients_repository->getPatientDetails($enccode);
    }

    //  Fetch patient diet
    public function getPatientDiet($enccode)
    {
        return $this->patients_repository->getPatientDiet($enccode);
    }

    //  Fetch patient height and weight
    public function getPatientMeasurements($enccode)
    {
        return $this->patients_repository->getPatientMeasurements($enccode);
    }

    //  Fetch allergies
    public function getAllergies()
    {
        return $this->patients_repository->getAllergies();
    }

    //  Fetch precautions
    public function getPrecautions()
    {
        return $this->patients_repository->getPrecautions();
    }

    //  Update patient food allergies
    public function updatePatientFoodAllergies($hpercode, $allergies, $licno)
    {
        $this->patients_repository->updatePatientFoodAllergies($hpercode, $allergies, $licno);
    }


}
