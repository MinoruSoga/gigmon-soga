<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switchLanguage(Request $request, $language)
    {
        Session::put('app_locale', $language);
        App::setLocale($language);

        return redirect()->back();
    }
}