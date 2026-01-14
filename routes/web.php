<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/login', function (Request $request) {
    if ($request->session()->get('is_admin')) {
        return redirect()->route('home');
    }

    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    if ($credentials['username'] === 'admin' && $credentials['password'] === 'admin123') {
        $request->session()->regenerate();
        $request->session()->put('is_admin', true);

        return redirect()->route('home');
    }

    return back()
        ->withErrors(['login' => 'Identifiant ou mot de passe incorrect.'])
        ->withInput();
})->name('login.submit');

Route::get('/admin', function (Request $request) {
    if (!$request->session()->get('is_admin')) {
        return redirect()->route('login');
    }

    return view('admin.index');
})->name('admin');

Route::post('/logout', function (Request $request) {
    $request->session()->forget('is_admin');
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
})->name('logout');
