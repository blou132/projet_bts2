<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

if (!defined('JMI_USERNAME')) {
    define('JMI_USERNAME', 'client');
}

if (!defined('JMI_PASSWORD')) {
    define('JMI_PASSWORD', 'client123');
}

if (!defined('ADMIN_SYSTEM_EMAIL')) {
    define('ADMIN_SYSTEM_EMAIL', 'admin-system@example.test');
}

if (!defined('JMI_SYSTEM_EMAIL')) {
    define('JMI_SYSTEM_EMAIL', 'support-system@example.test');
}

if (!defined('JMI_DISPLAY_NAME')) {
    define('JMI_DISPLAY_NAME', 'Support Demo');
}

if (!defined('PROFANITY_TERMS')) {
    define('PROFANITY_TERMS', [
        'merde',
        'putain',
        'connard',
        'con',
        'fdp',
        'shit',
        'fuck',
        'bitch',
    ]);
}

if (!defined('LOGIN_FAIL_MAX_ATTEMPTS')) {
    define('LOGIN_FAIL_MAX_ATTEMPTS', 5);
}

if (!defined('LOGIN_FAIL_BAN_SECONDS')) {
    define('LOGIN_FAIL_BAN_SECONDS', 3600);
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
 * Normalise un texte pour verification anti-profanite.
 *
 * @param string $text
 * @return string
 */
if (!function_exists('normalizeForModeration')) {
    function normalizeForModeration(string $text): string
    {
        $ascii = Str::lower(Str::ascii($text));
        $ascii = preg_replace('/[^a-z0-9\\s]/', ' ', $ascii) ?? '';
        return trim(preg_replace('/\\s+/', ' ', $ascii) ?? '');
    }
}

/**
 * Retourne vrai si le texte contient un mot interdit.
 *
 * @param string $text
 * @return bool
 */
if (!function_exists('containsProfanity')) {
    function containsProfanity(string $text): bool
    {
        $normalizedText = normalizeForModeration($text);
        if ($normalizedText === '') {
            return false;
        }

        foreach (PROFANITY_TERMS as $term) {
            if (preg_match('/\\b' . preg_quote((string) $term, '/') . '\\b/', $normalizedText) === 1) {
                return true;
            }
        }

        return false;
    }
}

/**
 * Journalise une tentative de connexion echouee pour Fail2Ban.
 *
 * Format:
 * 2026-04-03T13:40:00+00:00 AUTH_FAIL ip=1.2.3.4 login=user@example.test reason=invalid_credentials uri=/login
 *
 * @param Request $request
 * @param string $login
 * @param string $reason
 * @return void
 */
if (!function_exists('logAuthFailure')) {
    function logAuthFailure(Request $request, string $login, string $reason = 'invalid_credentials'): void
    {
        $ip = (string) ($request->ip() ?? '0.0.0.0');
        $cleanLogin = trim(strip_tags($login));
        $cleanLogin = preg_replace('/\s+/', '', $cleanLogin) ?? '';
        $cleanLogin = Str::lower($cleanLogin);
        $uri = '/' . ltrim((string) $request->path(), '/');

        $line = sprintf(
            "%s AUTH_FAIL ip=%s login=%s reason=%s uri=%s\n",
            now()->toIso8601String(),
            $ip,
            $cleanLogin !== '' ? $cleanLogin : 'unknown',
            $reason,
            $uri
        );

        $logDir = storage_path('logs');
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $logFile = $logDir . DIRECTORY_SEPARATOR . 'security.log';
        if (@file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX) === false) {
            logger()->warning('Impossible d ecrire dans security.log', ['path' => $logFile]);
        }
    }
}

/**
 * Retourne la cle du compteur d'echecs de connexion pour une IP.
 *
 * @param Request $request
 * @return string
 */
if (!function_exists('loginFailCounterKey')) {
    function loginFailCounterKey(Request $request): string
    {
        $ip = (string) ($request->ip() ?? '0.0.0.0');
        return 'auth:v2:fail:count:' . sha1($ip);
    }
}

/**
 * Retourne la cle de ban temporaire de connexion pour une IP.
 *
 * @param Request $request
 * @return string
 */
if (!function_exists('loginFailBanKey')) {
    function loginFailBanKey(Request $request): string
    {
        $ip = (string) ($request->ip() ?? '0.0.0.0');
        return 'auth:v2:fail:ban_until:' . sha1($ip);
    }
}

/**
 * Retourne le nombre de secondes restantes de bannissement pour l'IP.
 *
 * @param Request $request
 * @return int
 */
if (!function_exists('loginBanRemainingSeconds')) {
    function loginBanRemainingSeconds(Request $request): int
    {
        $banUntilTimestamp = (int) Cache::get(loginFailBanKey($request), 0);
        if ($banUntilTimestamp <= 0) {
            return 0;
        }

        return max(0, $banUntilTimestamp - now()->getTimestamp());
    }
}

/**
 * Reinitialise l'etat anti brute-force pour l'IP.
 *
 * @param Request $request
 * @return void
 */
if (!function_exists('clearLoginFailState')) {
    function clearLoginFailState(Request $request): void
    {
        Cache::forget(loginFailCounterKey($request));
        Cache::forget(loginFailBanKey($request));
    }
}

/**
 * Enregistre un echec de connexion et applique le ban temporaire si necessaire.
 *
 * @param Request $request
 * @return int Nombre d'echecs consecutifs pour l'IP apres increment.
 */
if (!function_exists('recordLoginFailure')) {
    function recordLoginFailure(Request $request): int
    {
        $counterKey = loginFailCounterKey($request);
        $count = (int) Cache::increment($counterKey);
        if ($count === 1) {
            Cache::put($counterKey, 1, now()->addHours(2));
        }

        if ($count >= LOGIN_FAIL_MAX_ATTEMPTS) {
            $banUntilTimestamp = now()->addSeconds(LOGIN_FAIL_BAN_SECONDS)->getTimestamp();
            Cache::put(loginFailBanKey($request), $banUntilTimestamp, now()->addSeconds(LOGIN_FAIL_BAN_SECONDS));
            Cache::forget($counterKey);
        }

        return $count;
    }
}

/**
 * Retourne vrai si la session courante est le compte JMI (acces tickets/messages).
 *
 * @param Request $request
 * @return bool
 */
if (!function_exists('canAccessClientDesk')) {
    function canAccessClientDesk(Request $request): bool
    {
        return (bool) $request->session()->get('is_jmi', false);
    }
}

/**
 * Retourne l'identifiant d'un compte systeme (user technique).
 *
 * @param string $name
 * @param string $email
 * @return int
 */
if (!function_exists('resolveSystemUserId')) {
    function resolveSystemUserId(string $name, string $email): int
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                // Mot de passe technique uniquement pour existence du compte systeme.
                'password' => Hash::make(Str::random(32)),
            ]
        );

        if ($user->name !== $name) {
            $user->name = $name;
            $user->save();
        }

        return (int) $user->id;
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

        return resolveSystemUserId('Admin JMI 56', $adminEmail);
    }
}

/**
 * Retourne l'identifiant de l'utilisateur JMI (support client) en base.
 *
 * @return int
 */
if (!function_exists('resolveJmiSystemUserId')) {
    function resolveJmiSystemUserId(): int
    {
        $jmiEmail = (string) env('JMI_SYSTEM_EMAIL', JMI_SYSTEM_EMAIL);
        $jmiDisplayName = (string) env('JMI_DISPLAY_NAME', JMI_DISPLAY_NAME);

        return resolveSystemUserId($jmiDisplayName, $jmiEmail);
    }
}

// Page publique
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/mentions-legales.html', function () {
    return view('legal.mentions-legales', [
        'ownerFullName' => (string) env('SITE_OWNER_FULLNAME', 'Jean Exemple'),
        'contactEmail' => (string) env('SITE_CONTACT_EMAIL', 'contact@example.test'),
        'hostingProvider' => (string) env('SITE_HOSTING_PROVIDER', 'Hebergeur Demo SAS'),
        'hostingAddress' => (string) env('SITE_HOSTING_ADDRESS', '1 Rue des Serveurs, 75001 Paris'),
        'hostingPhone' => (string) env('SITE_HOSTING_PHONE', '01 11 22 33 44'),
    ]);
})->name('legal.mentions');

Route::get('/politique-confidentialite.html', function () {
    return view('legal.politique-confidentialite', [
        'retentionDays' => (int) GDPR_RETENTION_DAYS,
        'contactEmail' => (string) env('SITE_CONTACT_EMAIL', 'contact@example.test'),
    ]);
})->name('legal.privacy');

// Documentation Doxygen (lecture directe depuis le navigateur)
Route::get('/doxygen/{path?}', function (string $path = 'index.html') {
    $docsRoot = realpath(base_path('docs/doxygen/html'));
    if ($docsRoot === false) {
        abort(404);
    }

    $cleanPath = ltrim($path, '/');
    if ($cleanPath === '') {
        $cleanPath = 'index.html';
    }

    $targetFile = realpath($docsRoot . DIRECTORY_SEPARATOR . $cleanPath);
    if ($targetFile === false || !is_file($targetFile) || !str_starts_with($targetFile, $docsRoot)) {
        abort(404);
    }

    return response()->file($targetFile);
})->where('path', '.*')->name('doxygen');

// Connexion
Route::get('/login', function (Request $request) {
    if ($request->session()->get('is_admin') || $request->session()->get('is_jmi') || $request->session()->get('user_id')) {
        return redirect()->route('home');
    }

    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'login' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    $banRemaining = loginBanRemainingSeconds($request);
    if ($banRemaining > 0) {
        logAuthFailure($request, (string) $credentials['login'], 'rate_limited');
        $minutes = (int) ceil($banRemaining / 60);

        return back()
            ->withErrors(['login' => 'Trop de tentatives. Reessayez dans ' . $minutes . ' minute(s).'])
            ->withInput($request->except('password'));
    }

    $adminUsername = (string) env('ADMIN_USERNAME', ADMIN_USERNAME);
    $adminPassword = (string) env('ADMIN_PASSWORD', ADMIN_PASSWORD);
    if ($credentials['login'] === $adminUsername && $credentials['password'] === $adminPassword) {
        $adminSystemUserId = resolveAdminSystemUserId();
        $adminSystemUser = User::find($adminSystemUserId);
        clearLoginFailState($request);

        $request->session()->regenerate();
        $request->session()->put('is_admin', true);
        $request->session()->put('is_jmi', false);
        $request->session()->put('user_role', 'admin');
        $request->session()->put('user_id', $adminSystemUserId);
        $request->session()->put('user_name', $adminSystemUser?->name ?? 'Admin Client');

        return redirect()->route('home');
    }

    $jmiUsername = (string) env('JMI_USERNAME', JMI_USERNAME);
    $jmiPassword = (string) env('JMI_PASSWORD', JMI_PASSWORD);
    if ($credentials['login'] === $jmiUsername && $credentials['password'] === $jmiPassword) {
        $jmiSystemUserId = resolveJmiSystemUserId();
        $jmiSystemUser = User::find($jmiSystemUserId);
        $jmiDisplayName = (string) env('JMI_DISPLAY_NAME', JMI_DISPLAY_NAME);
        clearLoginFailState($request);

        $request->session()->regenerate();
        $request->session()->put('is_admin', false);
        $request->session()->put('is_jmi', true);
        $request->session()->put('user_role', 'jmi');
        $request->session()->put('user_id', $jmiSystemUserId);
        $request->session()->put('user_name', $jmiSystemUser?->name ?? $jmiDisplayName);

        return redirect()->route('home');
    }

    $user = User::where('email', $credentials['login'])->first();
    if ($user && Hash::check($credentials['password'], $user->password)) {
        clearLoginFailState($request);
        $request->session()->regenerate();
        $request->session()->put('is_admin', false);
        $request->session()->put('is_jmi', false);
        $request->session()->put('user_role', 'user');
        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_name', $user->name);

        return redirect()->route('home');
    }

    $failCount = recordLoginFailure($request);
    if ($failCount >= LOGIN_FAIL_MAX_ATTEMPTS) {
        $banMinutes = (int) ceil(LOGIN_FAIL_BAN_SECONDS / 60);
        logAuthFailure($request, (string) $credentials['login'], 'rate_limit_triggered');
        return back()
            ->withErrors(['login' => 'Trop de tentatives. Reessayez dans ' . $banMinutes . ' minute(s).'])
            ->withInput($request->except('password'));
    }

    logAuthFailure($request, (string) $credentials['login'], 'invalid_credentials');

    return back()
        ->withErrors(['login' => 'Identifiant ou mot de passe incorrect.'])
        ->withInput($request->except('password'));
})->name('login.submit');

Route::get('/register', function (Request $request) {
    if ($request->session()->get('is_admin') || $request->session()->get('is_jmi') || $request->session()->get('user_id')) {
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

    $cleanName = trim(strip_tags($validated['name']));
    if (containsProfanity($cleanName)) {
        return back()
            ->withErrors(['name' => 'Le nom contient des mots non autorises.'])
            ->withInput();
    }

    User::create([
        'name' => $cleanName,
        'email' => trim($validated['email']),
        'password' => Hash::make($validated['password']),
    ]);

    return redirect()->route('login')
        ->with('auth_success', 'Compte cree. Seul le compte client peut acceder aux tickets et conversations clients.');
})->name('register.submit');

// Messagerie
Route::get('/messages', function (Request $request) {
    $user = resolveSessionUser($request);
    if (!$user) {
        return redirect()->route('login');
    }

    $isAdmin = (bool) $request->session()->get('is_admin');
    $isJmi = canAccessClientDesk($request);

    if ($isAdmin && !$isJmi) {
        return redirect()->route('home')
            ->withErrors(['login' => 'Le compte admin ne peut pas acceder aux conversations clients.']);
    }

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

    if (!$isJmi) {
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

        if (!$isJmi) {
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
        'isJmi' => $isJmi,
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
    $isJmi = canAccessClientDesk($request);

    if ($isAdmin && !$isJmi) {
        return redirect()->route('home')
            ->withErrors(['login' => 'Le compte admin ne peut pas envoyer de messages clients.']);
    }

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
    if ($isJmi) {
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

        $receiverId = resolveJmiSystemUserId();
    }

    $cleanMessage = trim(strip_tags($validated['message']));
    if (containsProfanity($cleanMessage)) {
        return back()
            ->withErrors(['message' => 'Votre message contient des mots non autorises.'])
            ->withInput();
    }

    DB::table('messages')->insert([
        'sender_id' => $user->id,
        'receiver_id' => $receiverId,
        'contact_request_id' => (int) $validated['contact_request_id'],
        'message' => $cleanMessage,
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
    if (!canAccessClientDesk($request)) {
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
    if (!canAccessClientDesk($request)) {
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
    if (!canAccessClientDesk($request)) {
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
    if (!canAccessClientDesk($request)) {
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

    if (containsProfanity($sanitized['name'])) {
        return back()
            ->withErrors(['name' => 'Le nom contient des mots non autorises.'])
            ->withInput();
    }

    if (containsProfanity($sanitized['message'])) {
        return back()
            ->withErrors(['message' => 'Votre message contient des mots non autorises.'])
            ->withInput();
    }

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
            'receiver_id' => resolveJmiSystemUserId(),
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
    if (!canAccessClientDesk($request)) {
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
    if (!canAccessClientDesk($request)) {
        return redirect()->route('login');
    }

    DB::table('contact_requests')->where('id', $id)->delete();

    return back()
        ->with('admin_status', 'Demande supprimée.');
})->name('admin.requests.delete');

// Deconnexion
Route::post('/logout', function (Request $request) {
    $request->session()->forget(['is_admin', 'is_jmi', 'user_role', 'admin_user_id', 'user_id', 'user_name']);
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
})->name('logout');
