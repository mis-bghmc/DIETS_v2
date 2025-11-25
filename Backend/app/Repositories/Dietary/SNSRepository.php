<?php

namespace App\Repositories\Dietary;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use App\Repositories\Dietary\Interfaces\SNSRepositoryInterface;

class SNSRepository implements SNSRepositoryInterface
{
    //  SNS - specific date
    public function getSNS($date)
    {
        return DB::table('patients_snack_history AS psh')
            ->select(
                'psh.id',
                'do.hpercode',
                'ad.patname',
                'ad.wardname',
                DB::raw('IIF(d2.dietname IS NOT NULL, d.dietname + \' + \' + d2.dietname, d.dietname) AS ons'),
                'do.onsDescription',
                'd3.dietname',
                'psh.status',
                'psh.meal_status',
                'psh.meal_time',
                'psh.remarks',
                'psh.created_at',
                'p.riskIndicator'
            )
            ->join('diet_ons AS do', 'do.docointkey', '=', 'psh.docointkey')
            ->join('hdocord AS hdo', 'hdo.docointkey', '=', 'psh.docointkey')
            ->join(DB::raw("fn_AdmittedPatients_v2() AS ad"), 'ad.enccode', '=', 'do.enccode')
            ->join('diet AS d', 'd.dietcode', '=', 'do.onsFormula')
            ->leftJoin('diet AS d2', 'd2.dietcode', '=', 'do.onsFormula2')
            ->join('diet AS d3', 'd3.dietcode', '=', 'hdo.dietcode')
            ->leftJoin(DB::raw('(SELECT * FROM getNAR()) AS p'), 'p.enccode', '=', 'ad.enccode')
            ->where(function ($query) {
                $query->where('psh.meal_status', '!=', 'C')
                    ->orWhereNull('psh.meal_status');
            })
            ->where(DB::raw('CONVERT(VARCHAR, psh.created_at, 120)'), 'LIKE', '%' . $date . '%')
            ->orderby('psh.created_at', 'DESC')
            ->distinct()
            ->get();
    }

    //  SNS
    public function getList()
    {
        return DB::select("SELECT * FROM getONSLatest()");
    }

    //  SNS List - printable
    public function getListPrintable($date, $mealtime, $ward)
    {
        return DB::select("SELECT * FROM getONSList(?) WHERE meal_time=? AND wardname=? ORDER BY patname", [$date, $mealtime, $ward]);
    }

    //  SNS List - specific date
    public function getListByDate($date)
    {
        return DB::select("SELECT * FROM getONSList(?) ORDER BY wardname", [$date]);
    }

    //  SNS - Tags
    public function getTags($option, $wards)
    {
        $condition = $option == 'Adult' ? "AND (age > 18 AND agedesc = 'y/o')" : ($option == 'Pedia' ? " AND (age <= 18 AND agedesc = 'y/o')" : '');
        $selected_wards = explode(',', $wards);
        $placeholders = implode(',', array_fill(0, count($selected_wards), '?'));

        return DB::select("SELECT * FROM getONSLatest() WHERE wardcode IN ($placeholders) $condition ORDER BY wardname, patname, meal_time", $selected_wards);
    }

    //  SNS Census for specific date
    public function getCensus($date)
    {
        return DB::select("SELECT * FROM getMealCensusONS(?) ORDER BY created_at DESC", [$date]);
    }

    //  Monthly
    public function getMonthly($year, $month)
    {
        return DB::table('patients_snack_history as sh')
            ->join('hdocord as do', 'sh.docointkey', '=', 'do.docointkey')
            ->leftJoin('hospital.dbo.hadmlog as adm', 'do.enccode', '=', 'adm.enccode')
            ->select('adm.tacode')
            ->where('sh.status', '=', 'ACCEPTED')
            ->whereYear('sh.created_at', $year)
            ->whereMonth('sh.created_at', $month)
            ->get();
    }

    //  Update meal status
    public function updateMealStatus($id, $meal_status, $updated_by)
    {
        DB::table('patients_snack_history')
            ->where('id', '=', $id)
            ->update([
                'meal_status' => $meal_status,
                'updated_by' => $updated_by,
                'updated_at' => date("Y-m-d H:i:s")
            ]);
    }

    //  Update status
    public function updateStatus($id, $status, $user, $justification, $meal_status)
    {
        DB::table('patients_snack_history')
            ->where('id', '=', $id)
            ->update([
                'meal_status' => $meal_status,
                'status' => $status,
                'updated_by' => $user,
                'updated_at' => date("Y-m-d H:i:s"),
                'remarks' => $justification
            ]);
    }

    //  Update SNS status from REQUESTED to ACKNOWLEDGED
    public function updateStatusToAcknowledged($user, $mealtime)
    {
        DB::table('patients_snack_history')
            ->where('meal_time', '=', $mealtime)
            ->where('status', '=', 'REQUESTED')
            ->whereDate('created_at', date('Y-m-d'))
            ->update([
                'status' => 'ACKNOWLEDGED',
                'updated_by' => $user,
                'updated_at' => date("Y-m-d H:i:s")
            ]);
    }

    //  Verify served meals
    public function updateVerifiedStatus($ward_name, $meal_time)
    {
        DB::table('patients_snack_history AS ph')
            ->join('hdocord AS do', 'do.docointkey', '=', 'ph.docointkey')
            ->join("fn_AdmittedPatients_v2() AS ad", 'ad.enccode', '=', 'do.enccode')
            ->where('ad.wardname', '=', $ward_name)
            ->where('meal_time', '=', $meal_time)
            ->where('meal_status', '=', 'S')
            ->where('verified', '=', 'N')
            ->whereDate('created_at', date("Y-m-d"))
            ->update(['verified' => 'Y']);
    }

    //  Insert into patients_snack_history
    public function storeHistory($data)
    {
        DB::table('patients_snack_history')->insert($data);
    }
}
