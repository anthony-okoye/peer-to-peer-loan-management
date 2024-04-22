<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DateTime;
use Illuminate\Support\Facades\Http;


class ApiController extends Controller
{
    public static function generate_string($length=12) {
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($permitted_chars), 0, $length);
    }
    
    public static function wallet_headers() {
        $date = new DateTime();
        $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'access_key' => '17AFE8F7F9ACAA9F741B',
        'signature' => static::generate_string(),    
        'salt' => static::generate_string(),
        'timestamp' => $date->getTimestamp()
        ]);
    }
}
