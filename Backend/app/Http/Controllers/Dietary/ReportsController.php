<?php

namespace App\Http\Controllers\Dietary;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Services\Dietary\Interfaces\ReportsServiceInterface;

class ReportsController extends Controller
{
    protected $reports_service;

    //  Constructor
    public function __construct(ReportsServiceInterface $reports_service)
    {
        $this->reports_service = $reports_service;
    }

    //  Fetch monthly statistics
    public function monthly(Request $request)
    {
        $date = $request->route('date');

        return response()->json($this->reports_service->getMonthlyStatistics($date));
    }

    //  Export monthly statistics
    public function export(Request $request)
    {
        $date = $request->route('date');

        $download_path = $this->reports_service->exportMonthlyStatistics($date);
        
        return Storage::download($download_path);
    }
}
