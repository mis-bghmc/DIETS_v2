<?php

namespace App\Repositories\Dietary\Interfaces;

interface MealsRepositoryInterface
{
    public function getHistory($enccode);
    public function cancelCurrentMeal($table, $enccode, $created_at, $meal_time);
    public function cancelCurrentMeals($table, $docointkeys, $meal_time);
}