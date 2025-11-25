<?php

namespace App\Repositories\Dietary\Interfaces;

interface SNSRepositoryInterface
{
    public function getSNS($date);
    public function getList();
    public function getListPrintable($date, $mealtime, $ward);
    public function getListByDate($date);
    public function getTags($option, $wards);
    public function getCensus($date);
    public function getMonthly($year, $month);
    public function updateMealStatus($id, $meal_status, $updated_by);
    public function updateStatus($id, $status, $user, $justification, $meal_status);
    public function updateStatusToAcknowledged($user, $mealtime);
    public function updateVerifiedStatus($ward_name, $meal_time);
    public function storeHistory($data);
}