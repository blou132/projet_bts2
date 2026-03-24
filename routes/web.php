<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

if (!defined('GDPR_RETENTION_DAYS')) {
    define('GDPR_RETENTION_DAYS', 365);
}

if (!defined('ADMIN_USERNAME')) {
    define('ADMIN_USERNAME', 'admin');
}

if (!defined('ADMIN_PASSWORD')) {
    define('ADMIN_PASSWORD', 'admin123');
}

if (!defined('ADMIN_SYSTEM_EMAIL')) {
    define('ADMIN_SYSTEM_EMAIL', 'admin-system@jmi56.local');
}

/**
 * Supprime les demandes de contact depassant la duree de conservation RGPD.
 *
 * @return void
 */
if (!function_exists('purgeOldContactRequests')) {
    function purgeOldContactRequests(): void
    {
        DB::table('contact_requests')
            ->where('created_at', '<', now()->subDays(GDPR_RETENTION_DAYS))
            ->delete();
    }
}

/**
 * Retourne l'utilisateur connecte en session, sinon null.
 *
 * @param Request $request
 * @return User|null
 */
if (!function_exists('resolveSessionUser')) {
    function resolveSessionUser(Request $request): ?User
    {
        $userId = (int) $request->session()->get('user_id', 0);
        if ($userId <= 0) {
            return null;
        }

        return User::find($userId);
    }
}

/**
 * Retourne l'identifiant de l'utilisateur admin systeme en base.
 *
 * @return int
 */
if (!function_exists('resolveAdminSystemUserId')) {
    function resolveAdminSystemUserId(): int
    {
        $adminEmail = (string) env('ADMIN_SYSTEM_EMAIL', ADMIN_SYSTEM_EMAIL);

        $admin = User::query()->firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin JMI 56',
                // Mot de passe technique uniquement pour existence du compte systeme.
                'password' => Hash::make(Str::random(32)),
            ]
        );

        return (int) $admin->id;
    }
}

// Page publique
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Connexion
Route::get('/login', function (Request $request) {
    if ($request->session()->get('is_admin') || $request->session()->get('user_id')) {
        return redirect()->route('home');
    }

    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'login' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    $adminUsername = (string) env('ADMIN_USERNAME', ADMIN_USERNAME);
    $adminPassword = (string) env('ADMIN_PASSWORD', ADMIN_PASSWORD);
    if ($credentials['login'] === $adminUsername && $credentials['password'] === $adminPassword) {
        $adminSystemUserId = resolveAdminSystemUserId();
        $adminSystemUser = User::find($adminSystemUserId);

        $request->session()->regenerate();
        $request->session()->put('is_admin', true);
        $request->session()->put('user_id', $adminSystemUserId);
        $request->session()->put('user_name', $adminSystemUser?->name ?? 'Admin JMI 56');

        return redirect()->route('home');
    }

    $user = User::where('email', $credentials['login'])->first();
    if ($user && Hash::check($credentials['password'], $user->password)) {
        $request->session()->regenerate();
        $request->session()->put('is_admin', false);
        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_name', $user->name);

        return redirect()->route('home');
    }

    return back()
        ->withErrors(['login' => 'Identifiant ou mot de passe incorrect.'])
        ->withInput();
})->name('login.submit');

Route::get('/register', function (Request $request) {
    if ($request->session()->get('is_admin') || $request->session()->get('user_id')) {
        return redirect()->route('home');
    }

    return view('auth.register');
})->name('register');

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:120'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    User::create([
        'name' => trim($validated['name']),
        'email' => trim($validated['email']),
        'password' => Hash::make($validated['password']),
    ]);

    return redirect()->route('login')
        ->with('auth_success', 'Compte créé. Seul le compte admin peut accéder à l’administration.');
})->name('register.submit');

// Messagerie
Route::get('/messages', function (Request $request) {
    $user = resolveSessionUser($request);
    if (!$user) {
        return redirect()->route('login');
    }

    $isAdmin = (bool) $request->session()->get('is_admin');

    $threadsQuery = DB::table('contact_requests as cr')
        ->leftJoin('users as u', 'u.id', '=', 'cr.user_id')
        ->select([
            'cr.id',
            'cr.user_id',
            'cr.name',
            'cr.phone',
            'cr.status',
            'cr.created_at',
            'u.name as user_name',
            'u.email as user_email',
        ])
        ->orderByDesc('cr.created_at');

    if (!$isAdmin) {
        $threadsQuery->where('cr.user_id', $user->id);
    }

    $threads = $threadsQuery->get();

    $selectedRequestId = (int) $request->query('request', 0);
    if ($selectedRequestId <= 0 && $threads->isNotEmpty()) {
        $selectedRequestId = (int) $threads->first()->id;
    }

    $activeThread = $threads->firstWhere('id', $selectedRequestId);
    if ($selectedRequestId > 0 && !$activeThread) {
        return redirect()->route('messages.index');
    }

    $messages = collect();
    if ($activeThread) {
        $messagesQuery = DB::table('messages as m')
            ->join('users as sender', 'sender.id', '=', 'm.sender_id')
            ->join('users as receiver', 'receiver.id', '=', 'm.receiver_id')
            ->where('m.contact_request_id', $selectedRequestId)
            ->select([
                'm.id',
                'm.contact_request_id',
                'm.message',
                'm.status',
                'm.created_at',
                'm.sender_id',
                'm.receiver_id',
                'sender.name as sender_name',
                'sender.email as sender_email',
                'receiver.name as receiver_name',
                'receiver.email as receiver_email',
            ])
            ->orderBy('m.created_at');

        if (!$isAdmin) {
            $messagesQuery->where(function ($q) use ($user) {
                $q->where('m.sender_id', $user->id)
                    ->orWhere('m.receiver_id', $user->id);
            });
        }

        $messages = $messagesQuery->get();

        DB::table('messages')
            ->where('contact_request_id', $selectedRequestId)
            ->where('receiver_id', $user->id)
            ->where('status', 'unread')
            ->update([
                'status' => 'read',
                'updated_at' => now(),
            ]);
    }

    return view('messages.index', [
        'isAdmin' => $isAdmin,
        'threads' => $threads,
        'activeThread' => $activeThread,
        'messages' => $messages,
    ]);
})->name('messages.index');

Route::post('/messages', function (Request $request) {
    $user = resolveSessionUser($request);
    if (!$user) {
        return redirect()->route('login');
    }

    $isAdmin = (bool) $request->session()->get('is_admin');

    $validated = $request->validate([
        'contact_request_id' => ['required', 'integer', 'exists:contact_requests,id'],
        'message' => ['required', 'string', 'max:2000'],
    ]);

    $contactRequest = DB::table('contact_requests')
        ->where('id', (int) $validated['contact_request_id'])
        ->first();

    if (!$contactRequest) {
        return back()->withErrors([
            'contact_request_id' => 'Demande introuvable.',
        ])->withInput();
    }

    $receiverId = 0;
    if ($isAdmin) {
        $receiverId = (int) ($contactRequest->user_id ?? 0);
        if ($receiverId <= 0) {
            return back()->withErrors([
                'contact_request_id' => 'Cette demande n est pas liee a un compte utilisateur.',
            ])->withInput();
        }
    } else {
        if ((int) $contactRequest->user_id !== $user->id) {
            return redirect()->route('messages.index')
                ->withErrors(['message' => 'Acces interdit a cette conversation.']);
        }

        $receiverId = resolveAdminSystemUserId();
    }

    DB::table('messages')->insert([
        'sender_id' => $user->id,
        'receiver_id' => $receiverId,
        'contact_request_id' => (int) $validated['contact_request_id'],
        'message' => trim(strip_tags($validated['message'])),
        'status' => 'unread',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('messages.index', [
        'request' => (int) $validated['contact_request_id'],
    ])
        ->with('messages_status', 'Message envoye.');
})->name('messages.send');

Route::post('/messages/{id}/read', function (Request $request, int $id) {
    $user = resolveSessionUser($request);
    if (!$user) {
        return redirect()->route('login');
    }

    DB::table('messages')
        ->where('id', $id)
        ->where('receiver_id', $user->id)
        ->update([
            'status' => 'read',
            'updated_at' => now(),
        ]);

    return redirect()->route('messages.index', [
        'request' => (int) $request->input('request_id', 0),
    ])
        ->with('messages_status', 'Message marque comme lu.');
})->name('messages.read');

// Admin : listes par statut
Route::get('/admin', function (Request $request) {
    if (!$request->session()->get('is_admin')) {
        return redirect()->route('login');
    }

    purgeOldContactRequests();

    $requests = DB::table('contact_requests')
        ->where('status', 'pending')
        ->orderByDesc('created_at')
        ->get();

    return view('admin.index', [
        'requests' => $requests,
        'activeStatus' => 'pending',
        'searchQuery' => '',
        'searchMode' => false,
    ]);
})->name('admin');

Route::get('/admin/en-cours', function (Request $request) {
    if (!$request->session()->get('is_admin')) {
        return redirect()->route('login');
    }

    purgeOldContactRequests();

    $requests = DB::table('contact_requests')
        ->where('status', 'in_progress')
        ->orderByDesc('created_at')
        ->get();

    return view('admin.index', [
        'requests' => $requests,
        'activeStatus' => 'in_progress',
        'searchQuery' => '',
        'searchMode' => false,
    ]);
})->name('admin.in_progress');

Route::get('/admin/termine', function (Request $request) {
    if (!$request->session()->get('is_admin')) {
        return redirect()->route('login');
    }

    purgeOldContactRequests();

    $requests = DB::table('contact_requests')
        ->where('status', 'done')
        ->orderByDesc('created_at')
        ->get();

    return view('admin.index', [
        'requests' => $requests,
        'activeStatus' => 'done',
        'searchQuery' => '',
        'searchMode' => false,
    ]);
})->name('admin.done');

// Admin : recherche globale
Route::get('/admin/recherche', function (Request $request) {
    if (!$request->session()->get('is_admin')) {
        return redirect()->route('login');
    }

    purgeOldContactRequests();

    $query = trim((string) $request->query('q', ''));
    if ($query === '') {
        return redirect()->route('admin');
    }

    $requests = DB::table('contact_requests')
        ->where('name', 'like', '%' . $query . '%')
        ->orWhere('phone', 'like', '%' . $query . '%')
        ->orderByDesc('created_at')
        ->get();

    return view('admin.index', [
        'requests' => $requests,
        'activeStatus' => 'search',
        'searchQuery' => $query,
        'searchMode' => true,
    ]);
})->name('admin.search');

// Formulaire de contact
Route::post('/contact', function (Request $request) {
    $sessionUser = resolveSessionUser($request);

    $validated = $request->validate([
        'name' => ['required', 'string', 'max:120'],
        'phone' => ['required', 'string', 'regex:/^[0-9]{2}( [0-9]{2}){4}$/'],
        'message' => ['required', 'string', 'max:2000'],
    ]);

    $sanitized = [
        'name' => trim(strip_tags($validated['name'])),
        'phone' => trim(strip_tags($validated['phone'])),
        'message' => trim(strip_tags($validated['message'])),
    ];

    $contactRequestId = DB::table('contact_requests')->insertGetId([
        'name' => $sanitized['name'],
        'phone' => $sanitized['phone'],
        'message' => $sanitized['message'],
        'user_id' => $sessionUser?->id,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    if ($sessionUser) {
        DB::table('messages')->insert([
            'sender_id' => $sessionUser->id,
            'receiver_id' => resolveAdminSystemUserId(),
            'contact_request_id' => $contactRequestId,
            'message' => $sanitized['message'],
            'status' => 'unread',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return redirect()->to(route('home') . '#contact')
        ->with('contact_success', 'Votre demande a bien été envoyée.');
})->name('contact.submit');

// Admin : mise a jour du statut
Route::post('/admin/requests/{id}/status', function (Request $request, int $id) {
    if (!$request->session()->get('is_admin')) {
        return redirect()->route('login');
    }

    $status = $request->validate([
        'status' => ['required', 'in:pending,in_progress,done'],
    ])['status'];

    DB::table('contact_requests')
        ->where('id', $id)
        ->update(['status' => $status, 'updated_at' => now()]);

    $targetRoute = match ($status) {
        'in_progress' => route('admin.in_progress'),
        'done' => route('admin.done'),
        default => route('admin'),
    };

    return redirect()->to($targetRoute . '#request-' . $id);
})->name('admin.requests.status');

// Admin : suppression
Route::delete('/admin/requests/{id}', function (Request $request, int $id) {
    if (!$request->session()->get('is_admin')) {
        return redirect()->route('login');
    }

    DB::table('contact_requests')->where('id', $id)->delete();

    return back()
        ->with('admin_status', 'Demande supprimée.');
})->name('admin.requests.delete');

// Deconnexion
Route::post('/logout', function (Request $request) {
    $request->session()->forget(['is_admin', 'admin_user_id', 'user_id', 'user_name']);
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
})->name('logout');
