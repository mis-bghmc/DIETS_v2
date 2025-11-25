<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Interfaces\EmployeesServiceInterface;

class EmployeesController extends Controller
{
    protected $employees_service;

    //  Constructor
    public function __construct(EmployeesServiceInterface $employees_service)
    {
        $this->employees_service = $employees_service;
    }

    //  Fetch employee details
    public function employee(Request $request)
    {
        $id = $request->route('id');

        return response()->json($this->employees_service->getEmployee($id));
    }

    //  Fetch allowed personnels
    public function allowed()
    {
        return response()->json($this->employees_service->getAllowedPersonnel());
    }
}
