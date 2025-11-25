<?php

namespace App\Repositories\Interfaces;

interface DietTypesRepositoryInterface
{
    public function getAllActive();
    public function getEnteralAll();
    public function getEnteralFeedingModes();
    public function getType($dietcode);
    public function updateStatus($dietcode, $status, $updated_by);
}