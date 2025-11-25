<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\NutritionRepositoryInterface;

class NutritionRepository implements NutritionRepositoryInterface
{
    //  Fetch admitted patients nutrition
    public function getAdmittedPatientsNutrition()
    {
        return DB::select("SELECT
                                adm.patname,
                                adm.hpercode,
                                adm.enccode,
                                adm.patage,
                                adm.patsex,
                                adm.wardname,
                                adm.rmname,
                                adm.tacode,
                                adm.admdate,
                                adm.wardcode,
                                adm.rmtype,
                                adm.admtxt,
                                measurement.weight,
                                measurement.ddate,
                                measurement.height,
                                n.riskIndicator,
                                n.bmi,
                                n.datepost
                            FROM
                                fn_AdmittedPatients_v2() AS adm
                            LEFT OUTER JOIN
                            (
                                SELECT
                                        m.hpercode,
                                        m.enccode,
                                        m.vsweight AS weight,
                                        m.vsheight AS height,
                                        m.othrdte AS ddate
                                    FROM
                                        hospital.dbo.hvsothr m
                                    WHERE
                                        m.othrdte = (
                                            SELECT MAX(m2.othrdte)
                                            FROM hospital.dbo.hvsothr m2
                                            WHERE m2.hpercode = m.hpercode
                                            )
                            )	AS measurement ON measurement.hpercode = adm.hpercode
                            LEFT OUTER JOIN
                            (
                                SELECT
                                    n.riskIndicator,
                                    n.enccode,
                                    n.bmi,
                                    n.datepost
                                FROM
                                    patientNutrition as n
                                WHERE
                                    n.datepost = (
                                        SELECT MAX(n2.datepost)
                                        FROM patientNutrition n2
                                        WHERE n2.enccode = n.enccode
                                    )
                            ) AS n ON n.enccode = adm.enccode");
    }

    //  Fetch patient nutrition
    public function getPatientNutrition($enccode)
    {
        return DB::table('patientNutrition')
            ->where('enccode', $enccode)
            ->orderBy('datepost', 'desc')
            ->get();
    }

    //  Fetch patient nutrition assessment
    public function getPatientNutritionAssessment($enccode)
    {
        return DB::table('nutrition_assessment AS na')
            ->join('hospital.dbo.hpersonal AS hp', 'hp.employeeid', '=', 'na.assessed_by')
            ->where('enccode', '=', $enccode)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    //  Store patient nutrition screening
    public function storePatientNutritionScreening($data)
    {
        DB::table('patientNutrition')->insert($data);
    }

    //  Store patient nutrition assessment
    public function storePatientNutritionAssessment($data)
    {
        DB::table('nutrition_assessment')->insert($data);
    }

    public function deletePatientNutritionScreening($id)
    {
        DB::table('patientNutrition')->where('id', '=', $id)->delete();
    }
}
