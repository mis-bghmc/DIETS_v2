<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\NotificationsRepositoryInterface;
use Carbon\Carbon as Date;

class NotificationsRepository implements NotificationsRepositoryInterface
{
    //  Fetch notifications
    public function getNotifications($number_of_days)
    {
        return DB::table('notifications AS n')
            ->select(
                'ad.patname',
                'ad.wardname',
                'do.hpercode',
                'do.enccode',
                'do.dodate',
                'do.locked',
                'n.docointkey',
                'n.message',
                'n.priority',
                'n.created_at',
                'n.seen_date',
                'n.seen_by',
                'd.dietname',
                'd2.dietname AS dietname2',
                'pd.docointkey AS pd_docointkey',
                'onsFrequency',
                'emp.lastname',
                'emp.firstname'
            )
            ->join('hdocord AS do', 'do.docointkey', '=', 'n.docointkey')
            ->leftJoin('diet_ons AS ons', 'ons.docointkey', '=', 'n.docointkey')
            ->join(DB::raw("fn_AdmittedPatients_v2() AS ad"), 'ad.enccode', '=', 'do.enccode')
            ->join('diet AS d', 'd.dietcode', '=', 'do.dietcode')
            ->leftJoin('diet AS d2', 'd2.dietcode', '=', 'ons.onsFormula')
            ->leftJoin('patients_diet_history AS pd', 'pd.docointkey', '=', 'n.docointkey')
            ->leftJoin('hospital.dbo.hpersonal AS emp', 'emp.employeeid', '=', 'n.seen_by')
            ->whereRaw('n.created_at >= DATEADD(DAY, -?, CAST(GETDATE() AS DATE))', [$number_of_days])
            ->orderBy('n.created_at', 'DESC')
            ->distinct()
            ->get();
    }

    //  Insert into notifications
    public function storeNotification($data)
    {
        DB::table('notifications')->insert($data);
    }

    //  Acknowledge notifications
    public function acknowledge($docointkey, $updated_by)
    {
        DB::table('notifications')
            ->where('docointkey', '=', $docointkey)
            ->update([
                'seen_date' => Date::now(),
                'seen_by' => $updated_by
            ]);
    }
}
