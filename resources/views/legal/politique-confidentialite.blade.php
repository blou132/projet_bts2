<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Politique de confidentialite | Demo Site</title>
        <meta name="description" content="Politique de confidentialite RGPD du site Demo Site.">

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
                <h1 class="section-title">Politique de confidentialite</h1>

                <article class="card legal-doc-card">
                    <h2>Donnees collectees</h2>
                    <ul>
                        <li>Donnees du formulaire de contact : nom, telephone, message.</li>
                        <li>Donnees de compte : email, nom d utilisateur, mot de passe chiffre.</li>
                        <li>Donnees techniques : IP et logs serveur (securite et diagnostic).</li>
                    </ul>
                </article>

                <article class="card legal-doc-card">
                    <h2>Finalites</h2>
                    <ul>
                        <li>Repondre aux demandes envoyees via le formulaire.</li>
                        <li>Assurer le suivi des tickets et des echanges avec le support.</li>
                        <li>Ameliorer la qualite et la securite du site.</li>
                    </ul>
                </article>

                <article class="card legal-doc-card">
                    <h2>Duree de conservation</h2>
                    <p>Les demandes de contact sont conservees pendant maximum {{ $retentionDays }} jours, puis supprimees automatiquement.</p>
                    <p>Les logs techniques sont conserves selon la configuration serveur et uniquement pour la securite et la maintenance.</p>
                </article>

                <article class="card legal-doc-card">
                    <h2>Vos droits RGPD</h2>
                    <p>Conformement au RGPD, vous disposez des droits suivants :</p>
                    <ul>
                        <li>Droit d acces a vos donnees.</li>
                        <li>Droit de rectification.</li>
                        <li>Droit de suppression.</li>
                    </ul>
                    <p>Pour exercer vos droits : <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>.</p>
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
