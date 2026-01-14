<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Espace admin | JMI 56</title>
        <meta name="description" content="Espace administrateur JMI 56.">

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

        <main id="main" class="admin-page">
            <div class="container">
                <header class="admin-header">
                    <div>
                        <h1>Demandes clients</h1>
                        <p class="admin-lead">Liste des demandes reçues via le formulaire.</p>
                    </div>
                    <a class="btn btn-ghost" href="{{ route('home') }}#contact">Voir le formulaire</a>
                </header>

                @if (session('admin_status'))
                    <div class="auth-success">{{ session('admin_status') }}</div>
                @endif

                @if ($requests->isEmpty())
                    <div class="admin-empty">
                        <p>Aucune demande pour le moment.</p>
                    </div>
                @else
                    <div class="admin-list">
                        @foreach ($requests as $request)
                            <article class="admin-card">
                                <div class="admin-card__header">
                                    <div>
                                        <h2>{{ $request->name }}</h2>
                                        <p class="admin-meta">{{ $request->phone }} · {{ \Illuminate\Support\Carbon::parse($request->created_at)->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <form method="post" action="{{ route('admin.requests.delete', $request->id) }}" onsubmit="return confirm('Supprimer cette demande ?');">
                                        @csrf
                                        @method('delete')
                                        <button class="btn btn-ghost" type="submit">Supprimer</button>
                                    </form>
                                </div>
                                <p class="admin-message">{{ $request->message }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </main>
    </body>
</html>
