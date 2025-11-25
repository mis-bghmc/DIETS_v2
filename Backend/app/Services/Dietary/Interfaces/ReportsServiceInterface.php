<?php

namespace App\Services\Dietary\Interfaces;

interface ReportsServiceInterface
{
    public function getMonthlyStatistics($date);
    public function exportMonthlyStatistics($date);
}