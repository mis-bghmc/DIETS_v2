<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('homepage');
});


Route::get('/home', function () {
    return view('homepage');
});


Route::view('/maintenance', 'maintenance');
