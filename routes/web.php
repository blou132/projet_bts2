<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    $requests = DB::table('contact_requests')
        ->orderByDesc('created_at')
        ->get();

    return view('admin.index', ['requests' => $requests]);
})->name('admin');

Route::post('/contact', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:120'],
        'phone' => ['required', 'string', 'regex:/^[0-9]{2}( [0-9]{2}){4}$/'],
        'message' => ['required', 'string', 'max:2000'],
    ]);

    DB::table('contact_requests')->insert([
        'name' => $validated['name'],
        'phone' => $validated['phone'],
        'message' => $validated['message'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->to(route('home') . '#contact')
        ->with('contact_success', 'Votre demande a bien été envoyée.');
})->name('contact.submit');

Route::delete('/admin/requests/{id}', function (Request $request, int $id) {
    if (!$request->session()->get('is_admin')) {
        return redirect()->route('login');
    }

    DB::table('contact_requests')->where('id', $id)->delete();

    return redirect()->route('admin')
        ->with('admin_status', 'Demande supprimée.');
})->name('admin.requests.delete');

Route::post('/logout', function (Request $request) {
    $request->session()->forget('is_admin');
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
})->name('logout');
