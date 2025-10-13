<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', function (Request $request) {
    try {
        // Check database connection
        DB::connection()->getPdo();
        
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'database' => 'connected',
            'message' => 'Application is running normally'
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'timestamp' => now()->toISOString(),
            'error' => $e->getMessage(),
            'message' => 'Application requires attention'
        ], 500);
    }
});

Route::get('/ping', function () {
    return response('pong', 200);
});