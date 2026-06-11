<?php

use App\Http\Controllers\Api\VisitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['validate-api-key', 'throttle:visits'])
    ->post('/visits', [VisitController::class, 'store']);
