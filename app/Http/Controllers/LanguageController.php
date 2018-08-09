<?php

namespace App\Http\Controllers;

use App\Language;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    //
    public function index() {
        return Language::all();
    }
}
