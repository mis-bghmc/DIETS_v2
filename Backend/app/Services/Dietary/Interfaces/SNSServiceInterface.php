<?php

namespace App\Services\Dietary\Interfaces;

interface SNSServiceInterface
{
    public function getSNS($date);
    public function updateMealStatus($id, $meal_status, $meal_time, $updated_by);
    public function updateStatus($id, $status, $user, $justification, $meal_time);
    public function updateStatusToAcknowledged($user, $meal_time);
}