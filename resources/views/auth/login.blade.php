<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Se connecter | JMI 56</title>
        <meta name="description" content="Connexion à l'espace administrateur JMI 56.">

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

        <div class="nav-shell">
            <div class="container nav">
                <a class="logo" href="{{ route('home') }}#accueil">
                    <img src="{{ asset('images/logo-jmi56.png') }}" alt="JMI 56" width="140" height="48">
                </a>
                <div class="nav-cta">
                    <a class="btn btn-ghost" href="{{ route('home') }}">Retour au site</a>
                </div>
            </div>
        </div>

        <main id="main" class="auth-page">
            <form class="auth-card" method="post" action="{{ route('login.submit') }}">
                @csrf
                <h1>Se connecter</h1>
                <p class="auth-lead">Accès réservé à l'administration.</p>

                @if ($errors->any())
                    <div class="auth-error">{{ $errors->first() }}</div>
                @endif

                <div class="auth-field">
                    <label for="username">Identifiant</label>
                    <input id="username" name="username" type="text" autocomplete="username" required value="{{ old('username') }}">
                </div>
                <div class="auth-field">
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                </div>
                <div class="auth-actions">
                    <button class="btn btn-primary" type="submit">Connexion</button>
                    <a class="btn btn-ghost" href="{{ route('home') }}">Retour</a>
                </div>
            </form>
        </main>
    </body>
</html>
