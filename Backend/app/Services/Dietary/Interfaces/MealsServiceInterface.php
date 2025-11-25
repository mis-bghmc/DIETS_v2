<?php

namespace App\Services\Dietary\Interfaces;

interface MealsServiceInterface
{
    public function getHistory($enccode);
    public function getList();
    public function getListPrintable($date, $mealtime, $ward);
    public function getListByDate($date);
    public function getTags($group, $option, $wards);
    public function getCensus($date);
    public function acceptLateOrder($ons_frequency, $enccode, $hpercode, $docointkey, $updated_by);
}