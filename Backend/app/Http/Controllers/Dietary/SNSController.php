<?php

namespace App\Http\Controllers\Dietary;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Dietary\Interfaces\SNSServiceInterface;

class SNSController extends Controller
{
    protected $sns_service;

    //  Constructor
    public function __construct(SNSServiceInterface $sns_service)
    {
        $this->sns_service = $sns_service;
    }

    //  SNS - specific date
    public function sns(Request $request)
    {
        $date = $request->route('date');

        return response()->json($this->sns_service->getSNS($date));
    }

    //  Update meal status
    public function updateMealStatus(Request $request)
    {
        $this->sns_service->updateMealStatus($request->id, $request->meal_status, $request->meal_time, $request->updated_by);

        return response()->json([
            'message' => 'Meal status updated successfully.'
        ]);
    }

    //  Update SNS status
    public function updateStatus(Request $request)
    {
        $this->sns_service->updateStatus($request->id, $request->status, $request->updated_by, $request->justification, $request->sns_time);
        
        return response()->json([
            'message' => 'SNS status updated successfully.'
        ]);
    }

    //  Update SNS status from REQUESTED to ACKNOWLEDGED
    public function acknowledge(Request $request)
    {
        $this->sns_service->updateStatusToAcknowledged($request->updated_by, $request->sns_time);

        return response()->json([
            'message' => 'SNS status updated successfully.'
        ]);
    }
}
