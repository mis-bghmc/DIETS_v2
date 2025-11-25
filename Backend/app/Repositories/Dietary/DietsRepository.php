<?php

namespace App\Repositories\Dietary;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use App\Repositories\Dietary\Interfaces\DietsRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DietsRepository implements DietsRepositoryInterface
{
    //  Diet List - oral
    public function getListOral()
    {
        return DB::select("SELECT * FROM getDietOral()");
    }

    //  Diet List - Enteral
    public function getListEnteral()
    {
        return DB::select("SELECT * FROM getDietEnteral()");
    }

    // Diet List - printable
    public function getListPrintable($date, $mealtime, $ward)
    {
        return DB::select("SELECT * FROM getDietList(?) WHERE meal_time=? AND wardname=? ORDER BY patname", [$date, $mealtime, $ward]);
    }

    // Diet List - specific date
    public function getListByDate($date)
    {
        return DB::select("SELECT * FROM getDietList(?) ORDER BY wardname", [$date]);
    }

    //  Diet Tags - oral
    public function getTagsOral($option, $wards)
    {
        $condition = $option == 'Adult' ? "AND (age > 18 AND agedesc = 'y/o')" : ($option == 'Pedia' ? " AND (age <= 18 AND agedesc = 'y/o')" : '');
        $selected_wards = explode(',', $wards);
        $placeholders = implode(',', array_fill(0, count($selected_wards), '?'));

        return DB::select("SELECT * FROM getDietOral() WHERE wardcode IN ($placeholders) $condition ORDER BY wardname, patname, meal_time", $selected_wards);
    }

    //  Diet List - Enteral
    public function getTagsEnteral($option, $wards)
    {
        $condition = $option == 'Adult' ? "AND (age > 18 AND agedesc = 'y/o')" : ($option == 'Pedia' ? " AND (age <= 18 AND agedesc = 'y/o')" : '');
        $selected_wards = explode(',', $wards);
        $placeholders = implode(',', array_fill(0, count($selected_wards), '?'));

        return DB::select("SELECT * FROM getDietEnteral() WHERE wardcode IN ($placeholders) $condition ORDER BY wardname, patname, meal_time", $selected_wards);
    }

    //  Meal Census for specific date
    public function getCensus($date)
    {
        return DB::select("SELECT * FROM getMealCensusDiet(?) ORDER BY created_at DESC", [$date]);
    }

    //  Latest meal time
    public function getLatestMealTime()
    {
        return DB::table('patients_diet_history')->select('meal_time', 'created_at')->latest('created_at')->first();
    }

    //  Latest meal time - specific date
    public function getLatestMealTimeByDate($date)
    {
        return DB::table('patients_diet_history')->select('meal_time', 'created_at')->whereDate('created_at', $date)->latest('created_at')->first();
    }

    //  Monthly
    public function getMonthly($year, $month)
    {
        return DB::table('patients_diet_history as dh')
            ->join('hdocord as do', 'dh.docointkey', '=', 'do.docointkey')
            ->leftJoin('hospital.dbo.hadmlog as adm', 'do.enccode', '=', 'adm.enccode')
            ->leftJoin('patientFoodAllergies as allrgy', 'do.hpercode', '=', 'allrgy.hpercode')
            ->select('dh.docointkey', 'do.hpercode', 'dh.created_at', 'dh.meal_time', 'do.dietcode', 'adm.tacode', 'do.precaution', 'allrgy.category')
            ->whereYear('dh.created_at', $year)
            ->whereMonth('dh.created_at', $month)
            ->get();
    }

    // Monthly - ward
    public function getMonthlyByWard($year, $month, $ward_code)
    {
        return DB::table('patients_diet_history as dh')
            ->join('hdocord as do', 'dh.docointkey', '=', 'do.docointkey')
            ->join('hospital.dbo.hpatroom as rm', 'do.enccode', '=', 'rm.enccode')
            ->select('rm.wardcode')
            ->where('rm.wardcode', '=', $ward_code)
            ->whereYear('dh.created_at', $year)
            ->whereMonth('dh.created_at', $month)
            ->get();
    }

    //  Update meal status
    public function updateMealStatus($id, $meal_status, $updated_by)
    {
        DB::table('patients_diet_history')
            ->where('id', '=', $id)
            ->update([
                'meal_status' => $meal_status,
                'updated_by' => $updated_by,
                'updated_at' => date("Y-m-d H:i:s")
            ]);
    }

    //  Verify served meals
    public function updateVerifiedStatus($ward_name, $meal_time)
    {
        DB::table('patients_diet_history AS ph')
            ->join('hdocord AS do', 'do.docointkey', '=', 'ph.docointkey')
            ->join("fn_AdmittedPatients_v2() AS ad", 'ad.enccode', '=', 'do.enccode')
            ->where('ad.wardname', '=', $ward_name)
            ->where('meal_time', '=', $meal_time)
            ->where('meal_status', '=', 'S')
            ->where('verified', '=', 'N')
            ->whereDate('created_at', date("Y-m-d"))
            ->update(['verified' => 'Y']);
    }

    //  Insert into patients_diet_history
    public function storeHistory($data)
    {
        DB::table('patients_diet_history')->insert($data);
    }

    public function updateMealStatusAfterDischarge($hpercode)
    {
        // Check if patient exists
        $id = DB::table('hdocord')
            ->where('hpercode', $hpercode)
            ->select('docointkey')
            ->first();

        if (!$id) {
            throw new NotFoundHttpException('Patient not found');
        }

        // Get active doctor order keys
        $do_keys = DB::table('hdocord')
            ->where('hpercode', $hpercode)
            ->where('dostatus', 'A')
            ->pluck('docointkey');

        // If no active orders, just stop gracefully
        if ($do_keys->isEmpty()) {
            return;
        }

        // Update all to 'I' (inactive)
        DB::table('hdocord')
            ->whereIn('docointkey', $do_keys)
            ->update(['dostatus' => 'I']);
    }
}
