<?php

namespace App\Services\Dietary\Interfaces;

interface FoodServiceInterface
{
    public function getHistory($date, $ward, $meal_time);
    public function verify($ward_name, $meal_time, $total, $served, $not_served, $not_given, $unlisted_patients, $remarks, $server_id, $nurse_id, $nurse_signature);
}