<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Interfaces\NutritionServiceInterface;

class NutritionController extends Controller
{
    protected $nutrition_service;

    //  Constructor
    public function __construct(NutritionServiceInterface $nutrition_service)
    {
        $this->nutrition_service = $nutrition_service;
    }

    //  Fetch admitted patients nutrition
    public function admittedNutrition()
    {
        return response()->json($this->nutrition_service->getAdmittedPatientsNutrition());
    }

    //  Fetch patient nutrition
    public function patientNutrition(Request $request)
    {
        $enccode = $request->route('enccode');

        return response()->json($this->nutrition_service->getPatientNutrition($enccode));
    }

    //  Fetch patient nutrition assessment
    public function assessment(Request $request)
    {
        $enccode = $request->route('enccode');

        return response()->json($this->nutrition_service->getPatientNutritionAssessment($enccode));
    }

    //  Save patient nutrition screening
    public function saveScreening(Request $request)
    {
        $this->nutrition_service->storePatientNutritionScreening($request);

        return response()->json([
            'message' => 'Nutrition screening saved successfully.'
        ]);
    }

    //  Save patient nutrition assessment
    public function saveAssessment(Request $request)
    {
        $this->nutrition_service->storePatientNutritionAssessment($request);

        return response()->json([
            'message' => 'Nutrition assessment saved successfully.'
        ]);
    }

    public function deleteScreening(Request $request)
    {
        $id = $request->route('id');
        $this->nutrition_service->deletePatientNutritionScreening($id);

        return response()->json([
            'message' => 'Nutrition screening deleted successfully.'
        ]);
    }
}
