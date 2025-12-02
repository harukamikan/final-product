<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');


});

Route::get('/goals/create', function (){
    return view('goals.create');
});
