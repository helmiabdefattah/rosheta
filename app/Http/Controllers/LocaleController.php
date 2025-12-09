<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function switch($locale)
    {
        // Validate locale
        $allowedLocales = ['en', 'ar'];
        
        if (!in_array($locale, $allowedLocales)) {
            $locale = 'en';
        }

        // Set locale in session
        Session::put('locale', $locale);
        App::setLocale($locale);

        // Redirect back to previous page or home
        return redirect()->back();
    }
}
