<?php

namespace App\Repositories\Doctors\Interfaces;

interface DoctorsOrdersRepositoryInterface
{
    public function getHistory($hpercode);
    public function getSNS($docointkey);
    public function getLatestOrder($docointkey);
    public function getLatestOrders();
    public function getAdmittedOrders($operator, $datetime);
    public function getAdmittedOrdersSNS($snstime, $datetime);
    public function getDocointkeys($enccodes);
    public function getDoctorsOrders();
    public function getDoctorsOrdersTotal();
    public function updatePrecautions($id, $precaution);
    public function lockAndDeactivate($hpercode);
    public function lockAndDeactivatePending($hpercode);
    public function lockAndDeactivateMultiple($hpercodes);
    public function activate($docointkey);
    public function activateMultiple($docointkeys);
    public function storeDiet($data);
    public function storeSNS($data);
    public function storeNutrients($data);
    public function getDraft($emp_id);
    public function saveDraft($data);
    public function deleteDraft($id);
}