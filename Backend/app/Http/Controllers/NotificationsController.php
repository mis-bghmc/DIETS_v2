<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Interfaces\NotificationsServiceInterface;

class NotificationsController extends Controller
{
    protected $notifications_service;

    //  Constructor
    public function __construct(NotificationsServiceInterface $notifications_service)
    {
        $this->notifications_service = $notifications_service;
    }

    //  Fetch notifications
    public function notifications(Request $request) {
        $number_of_days = (int) $request->route('date_range');
        
        return response()->json($this->notifications_service->getNotifications($number_of_days));
    }

    //  Acknowledge notifications
    public function acknowledge(Request $request)
    {
        $this->notifications_service->acknowledge($request->docointkey, $request->updated_by);

        return response()->json([
            'message' => 'Notification acknowledged successfully!'
        ]);
    }
}
