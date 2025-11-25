<?php

namespace App\Services\Dietary\Interfaces;

interface FoodRequestsServiceInterface
{
    public function getFoodRequests($date);
    public function storeFoodRequests($data);
    public function updateFoodRequests($data);
}