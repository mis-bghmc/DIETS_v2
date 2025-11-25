<?php

namespace App\Services\Interfaces;

interface UsersServiceInterface
{
    public function authenticate($empid, $enccode, $hpercode);
    public function getUserLevel($position);
}