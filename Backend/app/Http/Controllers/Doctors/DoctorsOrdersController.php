<?php

namespace App\Http\Controllers\Doctors;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Doctors\Interfaces\DoctorsOrdersServiceInterface;
use App\Repositories\Doctors\Interfaces\DoctorsOrdersRepositoryInterface;

class DoctorsOrdersController extends Controller
{
    protected $orders_service;
    protected $orders_repository;

    //  Constructor
    public function __construct(DoctorsOrdersServiceInterface $orders_service, DoctorsOrdersRepositoryInterface $orders_repository)
    {
        $this->orders_service = $orders_service;
        $this->orders_repository = $orders_repository;
    }

    //  Fetch doctor's orders history
    public function orders(Request $request)
    {
        $hpercode = $request->route('hpercode');

        return response()->json($this->orders_service->getHistory($hpercode));
    }

    //  Fetch doctor's order for SNS
    public function sns(Request $request)
    {
        $docointkey = $request->route('docointkey');

        return response()->json($this->orders_service->getSNS($docointkey));
    }

    //  Fetch latest doctor's order
    public function order(Request $request)
    {
        $docointkey = $request->route('docointkey');

        return response()->json($this->orders_service->getLatestOrder($docointkey));
    }

    //  Fetch all admitted patients doctor's orders
    public function all()
    {
        return response()->json($this->orders_service->getDoctorsOrders());
    }

    //  Fetch total number of doctor's orders
    public function total()
    {
        return response()->json($this->orders_service->getDoctorsOrdersTotal());
    }

    //  Update precautions
    public function updatePrecautions(Request $request)
    {
        $this->orders_service->updatePrecautions($request->id, $request->precaution, $request->updated_by);

        return response()->json([
            'message' => 'Precautions updated successfully!'
        ]);
    }

    //  Save doctor's order
    public function save(Request $request)
    {
        $this->orders_service->save($request);

        return response()->json([
            'status' => 'success',
            'message' => 'Doctor\'s order saved successfully!'
        ], 200);
    }

    public function getDraft(Request $request)
    {
        $draft = $this->orders_repository->getDraft($request->emp_id);

        return response()->json($draft);
    }

    public function saveDraft(Request $request)
    {
        $this->orders_service->saveDraft($request);

        return response()->json([
            'status' => 'success',
            'message' => 'Doctor\'s order draft saved successfully!'
        ], 200);
    }

    public function deleteDraft(Request $request)
    {


        $this->orders_repository->deleteDraft($request->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Doctor\'s order draft deleted successfully!'
        ], 200);
    }
}
