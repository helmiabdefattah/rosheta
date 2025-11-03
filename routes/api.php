<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientRequestController;

Route::post('/client-requests', [ClientRequestController::class, 'store']);



