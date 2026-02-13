<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Créer un compte | JMI 56</title>
        <meta name="description" content="Création de compte pour l'espace administrateur JMI 56.">

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
            <form class="auth-card" method="post" action="{{ route('register.submit') }}">
                @csrf
                <h1>Créer un compte</h1>
                <p class="auth-lead">Créez un accès à l'administration.</p>

                @if ($errors->any())
                    <div class="auth-error">{{ $errors->first() }}</div>
                @endif

                <div class="auth-field">
                    <label for="name">Nom d'utilisateur</label>
                    <input id="name" name="name" type="text" autocomplete="name" required value="{{ old('name') }}">
                </div>
                <div class="auth-field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}">
                </div>
                <div class="auth-field">
                    <label for="password">Mot de passe</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required>
                </div>
                <div class="auth-field">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                </div>
                <div class="auth-actions">
                    <button class="btn btn-primary" type="submit">Créer le compte</button>
                    <a class="btn btn-ghost" href="{{ route('login') }}">Se connecter</a>
                </div>
            </form>
        </main>
    </body>
</html>
