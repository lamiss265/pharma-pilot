<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('locale')) {
            $locale = $request->get('locale');
            if (in_array($locale, ['en', 'fr', 'ar'])) {
                Session::put('locale', $locale);
                return redirect()->back();
            }
        }
        // If user is logged in, use their language preference
        if (Auth::check() && Auth::user()->language) {
            $locale = Auth::user()->language;
        } 
        // Otherwise check session
        else if (session()->has('locale')) {
            $locale = session('locale');
        } 
        // Default to English
        else {
            $locale = 'en';
        }
        
        // Debug: Log the locale being set
        Log::debug('Setting locale to: ' . $locale);
        
        // Set the application locale
        App::setLocale($locale);
        
        return $next($request);
    }
} 