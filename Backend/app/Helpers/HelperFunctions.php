<?php

namespace App\Helpers;
use Carbon\Carbon;

class HelperFunctions
{
    public function isWithinGracePeriod(): bool
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i');

        // Define meal grace periods
        $mealPeriods = [
            'breakfast' => ['start' => '04:30', 'end' => '05:30'],
            'lunch' => ['start' => '10:00', 'end' => '11:30'],
            'dinner' => ['start' => '15:00', 'end' => '17:00'],
        ];

        foreach ($mealPeriods as $period) {
            if ($currentTime >= $period['start'] && $currentTime <= $period['end']) {
                return true;
            }
        }

        return false;
    }

    public function isPriorityDietCode(string $dietType, string $dietCategory, ?string $previousDiet): bool
    {
        return in_array($dietType, ['17', '18']) || $previousDiet === '16' || $dietCategory === 'Enteral';
    }

}
