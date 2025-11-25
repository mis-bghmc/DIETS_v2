<?php

namespace App\Services;

use App\Events\NewDoctorsOrder;
use App\Services\Interfaces\NotificationsServiceInterface;
use App\Repositories\Interfaces\NotificationsRepositoryInterface;
use Illuminate\Support\Facades\DB;


class NotificationsService implements NotificationsServiceInterface
{
    protected $notifications_repository;

    //  Constructor
    public function __construct(NotificationsRepositoryInterface $notifications_repository)
    {
        $this->notifications_repository = $notifications_repository;
    }

    //  Fetch notifications
    public function getNotifications($number_of_days)
    {
        return $this->notifications_repository->getNotifications($number_of_days);
    }

    //  Acknowledge notifications
    public function acknowledge($docointkey, $updated_by)
    {
        $this->notifications_repository->acknowledge($docointkey, $updated_by);

        broadcast(new NewDoctorsOrder("Updated"))->toOthers();
    }
}