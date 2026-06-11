<?php

use App\Http\Controllers\Api\VisitController;
use Illuminate\Support\Facades\Route;

Route::post('/visits', [VisitController::class, 'store']);
