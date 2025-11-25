<?php

namespace App\Repositories\Dietary\Interfaces;

interface FoodRequestsRepositoryInterface
{
    public function getFoodRequests($date);
    public function storeFoodRequests($data);
    public function updateFoodRequests($data);
}