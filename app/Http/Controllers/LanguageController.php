<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    /**
     * Change the application language
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchLang(Request $request, $locale)
    {
        // Check if the language is supported
        if (!in_array($locale, ['en', 'fr', 'ar'])) {
            $locale = 'en'; // Default to English
        }
        
        // Store the selected language in session
        session(['locale' => $locale]);
        
        // If user is logged in, update their language preference
        if (Auth::check()) {
            $user = Auth::user();
            $user->language = $locale;
            $user->save();
        }
        
        // Redirect back to the previous page
        return redirect()->back();
    }
} 