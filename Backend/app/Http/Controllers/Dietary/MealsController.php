<?php

namespace App\Http\Controllers\Dietary;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Dietary\Interfaces\MealsServiceInterface;

class MealsController extends Controller
{
    protected $meals_service;

    //  Constructor
    public function __construct(MealsServiceInterface $meals_service)
    {
        $this->meals_service = $meals_service;
    }

    //  History
    public function history(Request $request)
    {
        $enccode = $request->route('enccode');
        
        return response()->json($this->meals_service->getHistory($enccode));
    }

    //  List
    public function list()
    {
        return response()->json($this->meals_service->getList());
    }

    //  List - printable
    public function printableList(Request $request)
    {
        $date = $request->query('date');
        $mealtime = $request->query('mealtime');
        $ward = $request->query('ward');
        
        return response()->json($this->meals_service->getListPrintable($date, $mealtime, $ward));
    }

    // List - specific date
    public function listByDate(Request $request)
    {
        $date = $request->route('date');
        
        return response()->json($this->meals_service->getListByDate($date));
    }

    //  Tags
    public function tags(Request $request)
    {
        $group = $request->query('group');
        $option = $request->query('option');
        $wards = $request->query('wards');

        return response()->json($this->meals_service->getTags($group, $option, $wards));
    }

    //  Census
    public function census(Request $request)
    {
        $date = $request->route('date');

        return response()->json($this->meals_service->getCensus($date));
    }

    //  Accept late doctor's order
    public function accept(Request $request)
    {
        $this->meals_service->acceptLateOrder($request->ons_frequency, $request->enccode, $request->hpercode, $request->docointkey, $request->updated_by);

        return response()->json([
            'message' => 'Late doctor\'s order accepted.'
        ]);
    }
}
