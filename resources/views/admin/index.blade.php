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

        <!-- Navigation admin -->
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

        <!-- Contenu admin -->
        <main id="main" class="admin-page">
            <div class="container">
                <header class="admin-header">
                    <div>
                        <h1>{{ $searchMode ? 'Résultats de recherche' : 'Demandes clients' }}</h1>
                        <p class="admin-lead">
                            @if ($searchMode)
                                Résultats pour « {{ $searchQuery }} ».
                            @else
                                Suivi des demandes reçues via le formulaire.
                            @endif
                        </p>
                    </div>
                </header>

                <!-- Onglets + recherche -->
                <div class="admin-toolbar">
                    <nav class="admin-tabs" aria-label="Navigation des demandes">
                        <a class="admin-tab {{ $activeStatus === 'pending' ? 'is-active' : '' }}" href="{{ route('admin') }}">En attente</a>
                        <a class="admin-tab {{ $activeStatus === 'in_progress' ? 'is-active' : '' }}" href="{{ route('admin.in_progress') }}">En cours</a>
                        <a class="admin-tab {{ $activeStatus === 'done' ? 'is-active' : '' }}" href="{{ route('admin.done') }}">Terminé</a>
                    </nav>
                    <form class="admin-search" method="get" action="{{ route('admin.search') }}">
                        <label for="admin-search" class="sr-only">Rechercher</label>
                        <input id="admin-search" name="q" type="search" placeholder="Rechercher un client ou un téléphone" value="{{ $searchQuery }}">
                        <button class="btn btn-ghost" type="submit">Rechercher</button>
                        @if ($searchMode)
                            <a class="btn btn-ghost" href="{{ route('admin') }}">Réinitialiser</a>
                        @endif
                    </form>
                </div>

                @if (session('admin_status'))
                    <div class="auth-success">{{ session('admin_status') }}</div>
                @endif

                <!-- Liste des demandes -->
                @if ($requests->isEmpty())
                    <div class="admin-empty">
                        <p>{{ $searchMode ? 'Aucune demande ne correspond à cette recherche.' : 'Aucune demande pour le moment.' }}</p>
                    </div>
                @else
                    <div class="admin-list">
                        @foreach ($requests as $request)
                            <article id="request-{{ $request->id }}" class="admin-card {{ $searchMode ? 'admin-card--search' : '' }}" data-status="{{ $request->status }}">
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
                                <div class="admin-status">
                                    @if ($request->status !== 'pending')
                                        <form method="post" action="{{ route('admin.requests.status', $request->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="pending">
                                            <button class="status-btn status-btn--pending" type="submit">En attente</button>
                                        </form>
                                    @endif
                                    @if ($request->status !== 'in_progress')
                                        <form method="post" action="{{ route('admin.requests.status', $request->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="in_progress">
                                            <button class="status-btn status-btn--progress" type="submit">En cours</button>
                                        </form>
                                    @endif
                                    @if ($request->status !== 'done')
                                        <form method="post" action="{{ route('admin.requests.status', $request->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="done">
                                            <button class="status-btn status-btn--done" type="submit">Terminé</button>
                                        </form>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </main>
    </body>
</html>
