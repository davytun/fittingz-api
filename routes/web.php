<?php

use App\Http\Controllers\UnsubscribeController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/unsubscribe/{userId}', [UnsubscribeController::class, 'unsubscribe'])
    ->name('unsubscribe')
    ->middleware('signed');
