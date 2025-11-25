<?php

namespace App\Repositories\Dietary;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use App\Repositories\Dietary\Interfaces\MealsRepositoryInterface;

class MealsRepository implements MealsRepositoryInterface
{
    //  Diet history for specific encounter
    public function getHistory($enccode)
    {
        return DB::select("SELECT * FROM getDietHistory(?) ORDER BY created_at", [$enccode]);
    }

    //  Update current meal's status to 'C'
    public function cancelCurrentMeal($table, $enccode, $created_at, $meal_time)
    {
        DB::table($table)
            ->where('docointkey', 'like', '%' . $enccode . '%')
            ->whereDate('created_at', $created_at)
            ->where('verified', '=', 'N')
            ->where(function (Builder $query) use($meal_time) {
                $query->where('meal_time', $meal_time)
                    ->orWhereNotIn('meal_time', ['BREAKFAST', 'LUNCH', 'DINNER', 'AM', 'PM', 'MN']);
            })
            ->where(function (Builder $query) {
                $query->where('meal_status', '!=', 'S')
                    ->orWhereNull('meal_status');
            })
            ->update(['meal_status' => 'C']);
    }

    //  Update current meals' status to 'C'
    public function cancelCurrentMeals($table, $docointkeys, $meal_time)
    {
        DB::table($table)
            ->whereIn('docointkey', $docointkeys)
            ->whereDate('created_at', date("Y-m-d"))
            ->where('verified', '=', 'N')
            ->where(function (Builder $query) use($meal_time) {
                $query->where('meal_time', $meal_time)
                    ->orWhereNotIn('meal_time', ['BREAKFAST', 'LUNCH', 'DINNER', 'AM', 'PM', 'MN']);
            })
            ->where(function (Builder $query) {
                $query->where('meal_status', '!=', 'S')
                    ->orWhereNull('meal_status');
            })
            ->update(['meal_status' => 'C']);
    }
}