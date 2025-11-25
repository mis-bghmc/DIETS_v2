<?php

namespace App\Repositories\Doctors;

use App\Repositories\Doctors\Interfaces\DoctorsOrdersRepositoryInterface;
use DB;
use Log;

class DoctorsOrdersRepository implements DoctorsOrdersRepositoryInterface
{
    //  Fetch doctor's orders history
    public function getHistory($hpercode)
    {
        return DB::table('hdocord as do')
            ->select(
                'do.docointkey',
                'do.enccode',
                'do.dodate',
                'do.licno',
                'do.hpercode',
                'do.ordreas',
                'do.entryby',
                'do.dietcode',
                'do.dietcode2',
                'do.dostatus',
                'do.locked',
                'do.feedingMode',
                'do.feedingDuration',
                'do.feedingFrequency',
                'do.feedingScoop',
                'do.FTW',
                'do.precaution',
                'diet.dietname',
                'diet.dietdesc',
                'diet.diettype',
                'nt.calories',
                'nt.protein',
                'nt.carbohydrates',
                'nt.fats',
                'nt.fiber',
                'nt.sodium',
                'nt.dilution',
                'nt.volume',
                'allergy.category',
                'ons.onsFormula',
                'ons.onsFormula2',
                'ons.onsFrequency',
                'ons.onsDescription',
                'ons.remarks',
                'ons.date_created',
                'ons.status',
                'employee.lastname as lname',
                'employee.firstname as fname',
                'diet1.dietname as onsName',
                'diet2.dietname as onsName2',
                'emp.lastname as ln',
                'emp.firstname as fn'
            )
            ->join('diet', 'do.dietcode', '=', 'diet.dietcode')
            ->leftJoin('nutrients as nt', 'do.docointkey', '=', 'nt.docointkey')
            ->leftJoin('patientFoodAllergies as allergy', 'do.hpercode', '=', 'allergy.hpercode')
            ->leftJoin('diet_ons as ons', 'do.docointkey', '=', 'ons.docointkey')
            ->leftjoin('diet as diet1', 'ons.onsFormula', '=', 'diet1.dietcode')
            ->leftJoin('diet as diet2', 'ons.onsFormula2', '=', 'diet2.dietcode')
            ->leftJoin('hospital.dbo.hpersonal as emp', 'do.licno', '=', 'emp.employeeid')
            ->leftJoin('hospital.dbo.hpersonal as employee', function ($join) {
                $join->on('employee.employeeid', '=', 'pr.employeeid')
                    ->leftJoin('hospital.dbo.hprovider as pr', 'pr.employeeid', '=', 'employee.employeeid')
                    ->whereRaw('pr.licno = do.licno');
            })
            ->where('do.hpercode', $hpercode)
            ->orderByRaw("CASE
                                WHEN do.dostatus = 'A' THEN 1
                                WHEN do.dostatus = 'P' THEN 2
                                WHEN do.dostatus = 'I' THEN 3
                            END")
            ->orderByDesc('do.dodate')
            ->get();
    }

    //  Fetch doctor's order for SNS
    public function getSNS($docointkey)
    {
        return DB::table('hospital_dietary.dbo.diet_ons as ons')
            ->select(
                'ons.docointkey',
                'ons.enccode',
                'ons.hpercode',
                'ons.onsFormula',
                'ons.onsFormula2',
                'ons.onsFrequency',
                'ons.onsDescription',
                'ons.remarks',
                'ons.date_created',
                'ons.status',
                'diet1.dietname as onsName',
                'diet2.dietname as onsName2'
            )
            ->join('diet as diet1', 'ons.onsFormula', '=', 'diet1.dietcode')
            ->leftJoin('diet as diet2', 'ons.onsFormula2', '=', 'diet2.dietcode')
            ->where('docointkey', $docointkey)
            ->get();
    }

    //  Fetch latest doctor's order
    public function getLatestOrder($docointkey)
    {
        return DB::table('hdocord AS do')
            ->select(
                'd.dietname AS diet',
                'do.feedingMode',
                'do.feedingDuration',
                'do.feedingFrequency AS diet_frequency',
                'do.ordreas',
                'do.precaution',
                'al.category',
                'al.allergy',
                'd2.dietname AS sns',
                'd3.dietname AS additional_sns',
                'ons.onsFrequency AS sns_frequency',
                'ons.onsDescription',
                'nt.calories',
                'nt.protein',
                'nt.carbohydrates',
                'nt.fats',
                'nt.fiber',
                'nt.sodium',
                'nt.dilution',
                'nt.volume'
            )
            ->join('diet_ons AS ons', 'ons.docointkey', '=', 'do.docointkey')
            ->join('diet AS d', 'd.dietcode', '=', 'do.dietcode')
            ->leftJoin('diet AS d2', 'd2.dietcode', '=', 'ons.onsFormula')
            ->leftJoin('diet AS d3', 'd3.dietcode', '=', 'ons.onsFormula2')
            ->leftJoin('nutrients AS nt', 'nt.docointkey', '=', 'do.docointkey')
            ->leftJoin('patientFoodAllergies AS al', 'al.hpercode', '=', 'do.hpercode')
            ->where('do.docointkey', '=', $docointkey)
            ->get();
    }

    //  Fetch latest doctor's orders
    public function getLatestOrders()
    {
        return DB::table('hdocord')
            ->select('hpercode', DB::raw('MAX(dodate) as dodate'))
            ->groupBy('hpercode');
    }

    //  Fetch doctor's orders - Diet
    public function getAdmittedOrders($operator, $datetime)
    {
        return DB::table('hdocord AS do')
            ->select('do.docointkey', 'do.feedingFrequency', 'do.enccode', 'do.hpercode')
            ->join(DB::raw("fn_AdmittedPatients_v2() AS ad"), 'ad.enccode', '=', 'do.enccode')
            ->join('diet AS d', 'd.dietcode', '=', 'do.dietcode')
            ->joinSub($this->getLatestOrders(), 'lt', function ($join) {
                $join->on('lt.hpercode', '=', 'do.hpercode')
                    ->on('lt.dodate', '=', 'do.dodate');
            })
            ->where('d.diettype', $operator, 'EN')
            ->where('do.dostatus', '!=', 'I')
            ->where('do.dodate', '<=', $datetime)
            ->get();
    }

    //  Fet doctor's orders - SNS
    public function getAdmittedOrdersSNS($snstime, $datetime)
    {
        return DB::table('hdocord AS do')
            ->select('do.docointkey', 'do.enccode')
            ->join(DB::raw("fn_AdmittedPatients_v2() AS ad"), 'ad.enccode', '=', 'do.enccode')
            ->join('diet_ons AS ons', 'ons.docointkey', '=', 'do.docointkey')
            ->joinSub($this->getLatestOrders(), 'lt', function ($join) {
                $join->on('lt.hpercode', '=', 'do.hpercode')
                    ->on('lt.dodate', '=', 'do.dodate');
            })
            ->where('ons.onsFrequency', 'LIKE', '%' . $snstime . '%')
            ->where('do.dostatus', '!=', 'I')
            ->where('do.dodate', '<=', $datetime)
            ->get();
    }

    //  Fetch docointkeys by enccodes
    public function getDocointkeys($enccodes)
    {
        return DB::table('hdocord')->select('docointkey')->whereIn('enccode', $enccodes);
    }

    //  Fetch all admitted patients doctor's orders
    public function getDoctorsOrders()
    {
        return DB::select("SELECT * FROM getDoctorsOrders()");
    }

    //  Fetch total number of doctor's orders
    public function getDoctorsOrdersTotal()
    {
        return DB::select("SELECT * FROM getDoctorsOrdersTotal()");
    }

    //  Lock and deactivate doctor's orders
    public function lockAndDeactivate($hpercode)
    {
        DB::table('hdocord')->where('hpercode', '=', $hpercode)->update(['locked' => 'Y', 'dostatus' => 'I']);
    }

    //  Lock and deactivate pending doctor's orders
    public function lockAndDeactivatePending($hpercode)
    {
        DB::table('hdocord')->where('hpercode', '=', $hpercode)->where('dostatus', 'P')->update(['locked' => 'Y', 'dostatus' => 'I']);
    }

    //  Lock and deactivate enteral doctor's orders
    public function lockAndDeactivateMultiple($hpercodes)
    {
        DB::table('hdocord')->whereIn('hpercode', $hpercodes)->update(['locked' => 'Y', 'dostatus' => 'I']);
    }

    //  Activate doctor's order
    public function activate($docointkey)
    {
        DB::table('hdocord')->where('docointkey', '=', $docointkey)->update(['dostatus' => 'A']);
    }

    //  Activate doctor's orders
    public function activateMultiple($docointkeys)
    {
        DB::table('hdocord')->whereIn('docointkey', $docointkeys)->update(['dostatus' => 'A']);
    }

    //  Update precautions
    public function updatePrecautions($id, $precaution)
    {
        DB::table('hospital_dietary.dbo.hdocord')
            ->where('docointkey', $id)
            ->update(['precaution' => $precaution]);
    }

    //  Insert into hdocord
    public function storeDiet($data)
    {
        DB::table('hdocord')->insertGetId($data);
    }

    //  Insert into diet_ons
    public function storeSNS($data)
    {
        DB::table('diet_ons')->insertGetId($data);
    }

    //  Insert into nutrients
    public function storeNutrients($data)
    {
        DB::table('nutrients')->insert($data);
    }

    public function getDraft($emp_id)
    {
        return DB::table('drafts')->where('created_by', $emp_id)->get();
    }

    public function saveDraft($data)
    {
        // Filter out properties of draftDetails property with null values
        $filteredData = array_filter((array) $data->draftDetails, function ($value) {
            return !is_null($value);
        });

        DB::table('drafts')->insert([
            'title' => $data->draftTitle,
            'description' => $data->draftRemarks,
            'details' => json_encode($filteredData),
            'created_at' => now(),
            'created_by' => $data->docId
        ]);
    }
    public function deleteDraft($id)
    {
        DB::table('drafts')->where('id', $id)->delete();

        Log::info("Draft deleted", ['id' => $id]);
    }
}

