<?php

use Illuminate\Support\Facades\Route;

Route::get('{any}', function () {
    $path = base_path('index.html');
    if (!file_exists($path)) {
        $path = public_path('index.html');
    }
    
    if (file_exists($path)) {
        return file_get_contents($path);
    }
    
    return response()->json(['message' => 'Frontend build not found.'], 404);
})->where('any', '^(?!api).*$');
