<?php

namespace App\Repositories\Dietary\Interfaces;

interface FoodRepositoryInterface
{
    public function getHistory($date, $ward, $meal_time);
    public function storeHistory($data);
}