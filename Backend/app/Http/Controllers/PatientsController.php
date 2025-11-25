<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Interfaces\PatientsServiceInterface;

class PatientsController extends Controller
{
    protected $patients_service;

    //  Constructor
    public function __construct(PatientsServiceInterface $patients_service)
    {
        $this->patients_service = $patients_service;
    }

    //  Fetch admitted patients
    public function admitted()
    {
        return response()->json($this->patients_service->getAdmittedPatients());
    }

    //  Fetch my patients
    public function myPatients(Request $request)
    {
        $id = $request->route('id');

        return response()->json($this->patients_service->getMyPatients($id));
    }

    //  Fetch patient details
    public function details(Request $request)
    {
        $enccode = $request->route('enccode');

        return response()->json($this->patients_service->getPatientDetails($enccode));
    }

    //  Fetch patient height and weight
    public function measurements(Request $request)
    {
        $enccode = $request->route('enccode');

        return response()->json($this->patients_service->getPatientMeasurements($enccode));
    }

    //  Fetch allergies
    public function allergies()
    {
        return response()->json($this->patients_service->getAllergies());
    }

    //  Fetch precautions
    public function precautions()
    {
        return response()->json($this->patients_service->getPrecautions());
    }

    //  Update patient food allergies
    public function updateFoodAllergies(Request $request)
    {
        $this->patients_service->updatePatientFoodAllergies($request->hpercode, $request->allergies, $request->licno);

        return response()->json([
            'message' => 'Patient food allergies updated successfully.'
        ]);
    }
}
