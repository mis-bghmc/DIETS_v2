<?php

namespace App\Repositories\Dietary\Interfaces;

interface DietsRepositoryInterface
{
    public function getListOral();
    public function getListEnteral();
    public function getListPrintable($date, $mealtime, $ward);
    public function getListByDate($date);
    public function getTagsOral($option, $wards);
    public function getTagsEnteral($option, $wards);
    public function getCensus($date);
    public function getLatestMealTime();
    public function getLatestMealTimeByDate($date);
    public function getMonthly($year, $month);
    public function getMonthlyByWard($year, $month, $ward_code);
    public function updateMealStatus($id, $meal_status, $updated_by);
    public function updateVerifiedStatus($ward_name, $meal_time);
    public function storeHistory($data);
    public function updateMealStatusAfterDischarge($hpercode);
}