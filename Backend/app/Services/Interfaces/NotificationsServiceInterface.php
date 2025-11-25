<?php

namespace App\Services\Interfaces;

interface NotificationsServiceInterface
{
    public function getNotifications($number_of_days);
    public function acknowledge($docointkey, $updated_by);
}