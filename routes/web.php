<?php

use App\Http\Controllers\SubscriptionController;
use App\Models\Company;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return to_route('filament.admin.auth.login');
});


Route::get('/login', function () {
    return to_route('filament.admin.auth.login');
})->name('login');

Route::get('/subscribe', function () {
    $plans = Plan::where('active', true)->get();
    $features = Feature::whereHas('feature_plans')->get();
    return view('subscriptions.subscribe', compact('plans', 'features'));
})->middleware('auth')->name('subscribe');

Route::get('/subscribe2', function () {
    $plans = Plan::where('active', true)->get();
    $features = Feature::whereHas('feature_plans')->get();
    return view('subscriptions.subscribe2', compact('plans', 'features'));
})->middleware('auth')->name('subscribe');

Route::post('/plan/subscribe', [SubscriptionController::class, 'store'])->middleware('auth');
