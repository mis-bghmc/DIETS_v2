<?php

namespace App\Services\Interfaces;

interface DietTypesServiceInterface
{
    public function getAllActive();
    public function getEnteralAll();
    public function getEnteralFeedingModes();
    public function updateStatus($dietcode, $status, $updated_by);
}