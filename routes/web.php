<?php

use App\Http\Controllers\UnsubscribeController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/unsubscribe/{userId}', [UnsubscribeController::class, 'showUnsubscribeForm'])
    ->name('unsubscribe')
    ->middleware('signed');

Route::post('/unsubscribe/{userId}', [UnsubscribeController::class, 'unsubscribe'])
    ->name('unsubscribe.destroy')
    ->middleware('signed');
