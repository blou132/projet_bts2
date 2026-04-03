<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Se connecter | Demo Site</title>
        <meta name="description" content="Connexion à l'espace administrateur Demo Site.">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Space+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                {!! file_get_contents(resource_path('css/app.css')) !!}
            </style>
            <script>
                {!! file_get_contents(resource_path('js/app.js')) !!}
            </script>
        @endif
    </head>
    <body>
        <a class="skip-link" href="#main">Aller au contenu</a>

        <!-- Navigation simple -->
        <div class="nav-shell">
            <div class="container nav">
                <a class="logo" href="{{ route('home') }}#accueil">
                    <img src="{{ asset('images/logo-fictif.svg') }}" alt="Logo fictif" width="140" height="48">
                </a>
                <div class="nav-cta">
                    <a class="btn btn-ghost" href="{{ route('home') }}">Retour au site</a>
                </div>
            </div>
        </div>

        <!-- Formulaire de connexion -->
        <main id="main" class="auth-page">
            @php
                $banSeconds = max((int) session('login_banned_seconds', 0), (int) ($loginBanRemaining ?? 0));
                $banHours = intdiv($banSeconds, 3600);
                $banMinutes = intdiv($banSeconds % 3600, 60);
                $banRemainingSeconds = $banSeconds % 60;
                $banTimerLabel = sprintf('%02d:%02d:%02d', $banHours, $banMinutes, $banRemainingSeconds);
            @endphp
            <form class="auth-card" method="post" action="{{ route('login.submit') }}">
                @csrf
                <h1>Se connecter</h1>
                <p class="auth-lead">Utilisateur : email + mot de passe. Comptes internes : admin ou client avec identifiant unique.</p>

                @if (session('auth_success'))
                    <div class="auth-success">{{ session('auth_success') }}</div>
                @endif

                @if ($banSeconds > 0)
                    <div class="auth-ban" data-login-ban data-ban-seconds="{{ $banSeconds }}">
                        <p class="auth-ban__title">Acces temporairement bloque (1h apres 5 essais).</p>
                        <p class="auth-ban__text" data-ban-message>
                            {{ session('login_banned') ? $errors->first('login') : 'Trop de tentatives de connexion detectees.' }}
                            Reessayez dans <strong class="auth-ban__timer" data-ban-countdown>{{ $banTimerLabel }}</strong>.
                        </p>
                    </div>
                @elseif ($errors->any())
                    <div class="auth-error">{{ $errors->first() }}</div>
                @endif

                <div class="auth-field">
                    <label for="login">Email utilisateur ou identifiant interne (admin/client)</label>
                    <input id="login" name="login" type="text" autocomplete="username" required value="{{ old('login') }}">
                </div>
                <div class="auth-field">
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                </div>
                <div class="auth-actions">
                    <button class="btn btn-primary" type="submit" data-login-submit @if ($banSeconds > 0) disabled aria-disabled="true" @endif>Connexion</button>
                    <a class="btn btn-ghost" href="{{ route('register') }}">Créer un compte</a>
                    <a class="btn btn-ghost" href="{{ route('home') }}">Retour</a>
                </div>
                <p class="rgpd-notice">
                    Les informations collectées sont utilisées uniquement pour répondre à votre demande.
                    Conformément au RGPD, vous pouvez exercer vos droits d'accès, de rectification et de suppression en nous contactant.
                </p>
            </form>
        </main>
    </body>
</html>
