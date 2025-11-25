<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Interfaces\UsersServiceInterface;
use App\Repositories\Interfaces\UsersRepositoryInterface;

class UsersController extends Controller
{
    protected $users_service;
    protected $users_repository;

    //  Constructor
    public function __construct(UsersServiceInterface $users_service, UsersRepositoryInterface $users_repository)
    {
        $this->users_service = $users_service;
        $this->users_repository = $users_repository;
    }

    // Authenticate
    public function dietaryTunnel($empid, $enccode, $hpercode)
    {
        $empid = trim($empid);
        $enccode = trim($enccode);
        $hpercode = trim($hpercode);

        if (empty($empid) || empty($enccode) || empty($hpercode)) {
            throw new \Exception('Invalid request');
        }

        return $this->users_service->authenticate($empid, $enccode, $hpercode);
    }

    public function userLevels()
    {
        return $this->users_repository->getUserLevels();
    }

    public function getUserLevel($position)
    {
        if (!$position || empty($position)) {
            throw new \Exception('Position is required');
        }

        return $this->users_service->getUserLevel($position);
    }

    public function getUserDetails($empid)
    {
        return $this->users_repository->getUserDetails($empid);
    }
}
