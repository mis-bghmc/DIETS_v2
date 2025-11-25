<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\PatientsRepositoryInterface;
use Carbon\Carbon as Date;

class PatientsRepository implements PatientsRepositoryInterface
{
    //  Fetch admitted patients
    public function getAdmittedPatients()
    {
        return DB::select("SELECT * FROM hospital_dietary.dbo.fn_searchpatients_v2()");
    }

    //  Fetch my patients
    public function getMyPatients($id)
    {
        return DB::table('hdocord')->where('entryby', $id)->where('dostatus', 'A')->get();
    }

    //  Fetch patient details
    public function getPatientDetails($enccode)
    {

        return DB::select('SELECT * FROM fn_patientDietEnccode_v2(?)', [$enccode]);
    }

    //  Fetch patient diet
    public function getPatientDiet($enccode)
    {
        return DB::table('hdocord AS do')
            ->join('diet AS d1', 'd1.dietcode', 'do.dietcode')
            ->leftJoin('diet_ons AS ons', 'ons.docointkey', 'do.docointkey')
            ->leftJoin('diet AS d2', 'd2.dietcode', 'ons.onsFormula')
            ->leftJoin('diet AS d3', 'd3.dietcode', 'ons.onsFormula2')
            ->select('d1.dietname AS dietname', 'd2.dietname AS ons', 'd3.dietname AS ons2')
            ->where('do.enccode', $enccode)
            ->where('do.dostatus', 'A')
            ->orderBy('do.dodate', 'desc')
            ->first();
    }

    //  Fetch patient height and weight
    public function getPatientMeasurements($enccode)
    {
        $latest_nutrition = DB::table('patientNutrition')
            ->selectRaw('MAX(datepost)')
            ->where('enccode', $enccode);

        $latest_measurement = DB::table('hospital.dbn.vsvitals')
            ->selectRaw('MAX(datelog)')
            ->where('enccode', $enccode)
            ->whereNotNull('height')
            ->whereNotNull('weight');

        return DB::table('hospital.dbn.vsvitals AS m')
            ->select('m.height', 'm.weight', 'n.riskIndicator')
            ->where('datelog', $latest_measurement)
            ->leftJoin('patientNutrition AS n', function ($join) use ($latest_nutrition) {
                $join->on('n.enccode', '=', 'm.enccode')
                    ->where('datepost', $latest_nutrition);
            })
            ->distinct()
            ->first();
    }

    //  Fetch allergies
    public function getAllergies()
    {
        return DB::table('allergies')->get();
    }

    //  Fetch precautions
    public function getPrecautions()
    {
        return DB::table('precautions')->get();
    }

    //  Update patient food allergies
    public function updatePatientFoodAllergies($hpercode, $allergies, $licno)
    {
        DB::table('patientFoodAllergies')
            ->updateOrInsert(
                ['hpercode' => $hpercode],
                ['category' => $allergies, 'created_by' => $licno, 'created_at' => Date::now()]
            );
    }

    //  Insert into patients_reproduction
    public function storeMaternalDetails($data)
    {
        DB::table('patients_reproduction')->insert($data);
    }

    public function findAdmEncounter($enccode)
    {

        return DB::select("SELECT enccode FROM hospital.dbo.hadmlog WHERE disnotes = ?", [$enccode]);
    }
}
