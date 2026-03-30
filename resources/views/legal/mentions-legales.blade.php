<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Mentions legales | Demo Site</title>
        <meta name="description" content="Mentions legales du site Demo Site.">

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
                    <a class="btn btn-ghost" href="{{ route('home') }}">Retour au site</a>
                </div>
            </div>
        </div>

        <main id="main" class="section">
            <div class="container legal-page">
                <h1 class="section-title">Mentions legales</h1>

                <article class="card legal-doc-card">
                    <h2>Editeur du site</h2>
                    <p><strong>Nom / Prenom :</strong> {{ $ownerFullName }}</p>
                    <p><strong>Email de contact :</strong> <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></p>
                </article>

                <article class="card legal-doc-card">
                    <h2>Hebergeur</h2>
                    <p><strong>Nom :</strong> {{ $hostingProvider }}</p>
                    <p><strong>Adresse :</strong> {{ $hostingAddress }}</p>
                    <p><strong>Telephone :</strong> {{ $hostingPhone }}</p>
                </article>

                <article class="card legal-doc-card">
                    <h2>Responsabilite</h2>
                    <p>Le proprietaire du site s efforce de fournir des informations exactes et a jour.</p>
                    <p>Le site peut contenir des liens externes. Le proprietaire n est pas responsable du contenu des sites tiers.</p>
                    <p>L utilisation du site se fait sous la responsabilite de l utilisateur.</p>
                </article>
            </div>
        </main>

        <footer class="footer footer-legal-links">
            <div class="container legal-links">
                <a href="{{ route('legal.mentions') }}">Mentions legales</a>
                <a href="{{ route('legal.privacy') }}">Politique de confidentialite</a>
            </div>
        </footer>
    </body>
</html>
