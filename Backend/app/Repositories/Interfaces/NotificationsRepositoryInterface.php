<?php

namespace App\Repositories\Interfaces;

interface NotificationsRepositoryInterface
{
    public function getNotifications($number_of_days);
    public function storeNotification($data);
    public function acknowledge($docointkey, $updated_by);
}