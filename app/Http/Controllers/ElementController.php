<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ElementController extends Controller
{
    public function index()
    {
        $workerUrl = 'https://inventory-react-app.pages.dev';
        $response = Http::get($workerUrl);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }

        return $response->json();
    }
}
