<?php

namespace App\Services\Doctors\Interfaces;

interface DoctorsOrdersServiceInterface
{
    public function getHistory($hpercode);
    public function getSNS($docointkey);
    public function getLatestOrder($docointkey);
    public function getDoctorsOrders();
    public function getDoctorsOrdersTotal();
    public function updatePrecautions($id, $precaution);
    public function save($data);
    public function savePriority($data, $date);
    public function saveDraft($data);
}