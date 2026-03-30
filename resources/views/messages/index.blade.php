<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Messagerie | Demo Site</title>
        <meta name="description" content="Messagerie utilisateur et support Demo Site.">

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
                    <img src="{{ asset('images/logo-fictif.svg') }}" alt="Logo fictif" width="140" height="48">
                </a>
                <div class="nav-cta">
                    @if ($isJmi)
                        <a class="btn btn-ghost" href="{{ route('admin') }}">Tickets</a>
                    @endif
                    <a class="btn btn-ghost" href="{{ route('home') }}">Retour au site</a>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-ghost" type="submit">Se déconnecter</button>
                    </form>
                </div>
            </div>
        </div>

        <main id="main" class="messages-page">
            <div class="container">
                <header class="messages-header">
                    <h1>Messagerie</h1>
                    <p class="messages-lead">
                        @if ($isJmi)
                            Repondez aux utilisateurs a partir de leur demande de contact.
                        @else
                            Discutez avec le support Demo a propos de vos demandes.
                        @endif
                    </p>
                </header>

                @if (session('messages_status'))
                    <div class="auth-success">{{ session('messages_status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="auth-error">{{ $errors->first() }}</div>
                @endif

                <section class="messages-layout">
                    <article class="card messages-threads">
                        <h2>Conversations</h2>
                        @if ($threads->isEmpty())
                            <p class="messages-empty">Aucune conversation disponible.</p>
                        @else
                            <div class="messages-thread-list">
                                @foreach ($threads as $thread)
                                    @php
                                        $requestStatusLabel = match ($thread->status) {
                                            'pending' => 'En attente',
                                            'in_progress' => 'En cours',
                                            'done' => 'Terminé',
                                            default => (string) $thread->status,
                                        };
                                    @endphp
                                    <a class="messages-thread {{ $activeThread && $activeThread->id === $thread->id ? 'is-active' : '' }}" href="{{ route('messages.index', ['request' => $thread->id]) }}">
                                        <p class="messages-thread__title">Demande #{{ $thread->id }}</p>
                                        @if ($isJmi)
                                            <p class="messages-thread__meta">
                                                @if ($thread->user_id)
                                                    {{ $thread->user_name }} ({{ $thread->user_email }})
                                                @else
                                                    Demande sans compte utilisateur
                                                @endif
                                            </p>
                                        @else
                                            <p class="messages-thread__meta">{{ $thread->name }} · {{ $thread->phone }}</p>
                                        @endif
                                        <p class="messages-thread__status">Statut demande: {{ $requestStatusLabel }}</p>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </article>

                    <article class="card messages-box">
                        @if (!$activeThread)
                            <p class="messages-empty">Selectionnez une conversation pour commencer.</p>
                        @else
                            <div class="messages-conversation-header">
                                <h2>Conversation de la demande #{{ $activeThread->id }}</h2>
                                <p>
                                    @if ($isJmi)
                                        @if ($activeThread->user_id)
                                            Utilisateur: {{ $activeThread->user_name }} ({{ $activeThread->user_email }})
                                        @else
                                            Demande sans compte: conversation impossible.
                                        @endif
                                    @else
                                        Vous echangez avec le support Demo.
                                    @endif
                                </p>
                            </div>

                            @if ($messages->isEmpty())
                                <p class="messages-empty">Aucun message pour cette conversation.</p>
                            @else
                                <div class="messages-list">
                                    @foreach ($messages as $message)
                                        @php
                                            $isMine = (int) $message->sender_id === (int) session('user_id');
                                        @endphp
                                        <article class="messages-item {{ $isMine ? 'is-mine' : 'is-other' }}">
                                            <div class="messages-item__top">
                                                <p>
                                                    <strong>{{ $isMine ? 'Moi' : $message->sender_name }}</strong>
                                                    <span class="messages-item__email">({{ $message->sender_email }})</span>
                                                </p>
                                                <span class="message-status {{ $message->status === 'read' ? 'is-read' : 'is-unread' }}">{{ $message->status === 'read' ? 'Lu' : 'Non lu' }}</span>
                                            </div>
                                            <p class="messages-item__meta">{{ \Illuminate\Support\Carbon::parse($message->created_at)->format('d/m/Y H:i') }}</p>
                                            <p class="messages-item__body">{{ $message->message }}</p>

                                            @if (!$isMine && $message->status !== 'read')
                                                <form method="post" action="{{ route('messages.read', $message->id) }}">
                                                    @csrf
                                                    <input type="hidden" name="request_id" value="{{ $activeThread->id }}">
                                                    <button class="btn btn-ghost" type="submit">Marquer comme lu</button>
                                                </form>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            @endif

                            <form class="messages-compose-form" method="post" action="{{ route('messages.send') }}">
                                @csrf
                                <input type="hidden" name="contact_request_id" value="{{ $activeThread->id }}">
                                <div class="auth-field">
                                    <label for="message">Votre message</label>
                                    <textarea id="message" name="message" placeholder="Ecrivez votre message" required>{{ old('message') }}</textarea>
                                </div>
                                <p class="rgpd-notice">
                                    Les informations collectées sont utilisées uniquement pour répondre à votre demande.
                                    Conformément au RGPD, vous pouvez exercer vos droits d'accès, de rectification et de suppression en nous contactant.
                                </p>
                                <button class="btn btn-primary" type="submit" @if ($isJmi && !$activeThread->user_id) disabled @endif>
                                    Envoyer
                                </button>
                            </form>
                        @endif
                    </article>
                </section>
            </div>
        </main>
    </body>
</html>
