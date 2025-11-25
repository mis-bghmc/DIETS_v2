<?php

namespace App\Services\Dietary\Interfaces;

interface DietsServiceInterface
{
    public function getLatestMealTime();
    public function updateMealStatus($id, $meal_status, $meal_time, $updated_by);
    public function updateMealStatusAfterDischarge($hpercode);
}