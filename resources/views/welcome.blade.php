<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>JMI 56 - Projet BTS</title>
        <meta name="description" content="Spécialisée des services informatiques, JMI 56 intervient à domicile pour l'installation et le dépannage d'équipements informatiques et logiciels à Ploërmel.">

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

        <!-- Navigation principale -->
        <div class="nav-shell">
            <div class="container nav">
                <a class="logo" href="#accueil">
                    <img src="{{ asset('images/logo-jmi56.png') }}" alt="JMI 56" width="140" height="48">
                </a>
                <button class="nav-toggle" data-nav-toggle aria-controls="site-nav" aria-expanded="false">Menu</button>
                <nav class="nav-links" id="site-nav" data-nav aria-label="Navigation principale">
                    <a href="#accueil">Accueil</a>
                    <a href="#presentation">Présentation</a>
                    <a href="#services">Services</a>
                    <a href="#zone">Zone</a>
                    <a href="#contact">Contact</a>
                    <div class="nav-actions">
                        @if (session('is_admin'))
                            <div class="nav-admin">
                                <a class="btn btn-ghost" href="{{ route('admin') }}">Admin</a>
                                <form method="post" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="btn btn-ghost" type="submit">Se déconnecter</button>
                                </form>
                            </div>
                        @else
                            <a class="btn btn-ghost" href="{{ route('login') }}">Se connecter</a>
                            <a class="btn btn-primary" href="#contact">Devis gratuit</a>
                        @endif
                    </div>
                </nav>
                <div class="nav-cta">
                    @if (session('is_admin'))
                        <div class="nav-admin">
                            <a class="btn btn-ghost" href="{{ route('admin') }}">Admin</a>
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-ghost" type="submit">Se déconnecter</button>
                            </form>
                        </div>
                    @else
                        <a class="btn btn-ghost" href="{{ route('login') }}">Se connecter</a>
                        <a class="btn btn-primary" href="#contact">Devis gratuit</a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Hero -->
        <header class="hero" id="accueil">
            <div class="container hero-grid">
                <div class="hero-copy" data-animate>
                    <p class="eyebrow">JMI 56</p>
                    <h1>Votre installateur et dépanneur informatique à Ploërmel</h1>
                    <p class="lead">Spécialisée des services informatiques, JMI 56 intervient à domicile pour l'installation et le dépannage d'équipements informatiques et logiciels à Ploërmel.</p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="sms:+33614418099">06 14 41 80 99</a>
                        <a class="btn btn-outline" href="tel:+33297930783">02 97 93 07 83</a>
                    </div>
                    <div class="hero-meta">
                        <div class="meta-card">
                            <strong>10+ ans</strong>
                            <span>d'expérience</span>
                        </div>
                        <div class="meta-card">
                            <strong>Devis gratuit</strong>
                            <span>sans engagement</span>
                        </div>
                        <div class="meta-card">
                            <strong>30 km</strong>
                            <span>autour de Ploërmel</span>
                        </div>
                    </div>
                </div>
                <div class="hero-visual" data-animate>
                    <img src="{{ asset('images/hero.jpg') }}" alt="Réparateur informatique en intervention sur un ordinateur">
                    <div class="hero-badge">Intervention à domicile</div>
                    <div class="hero-shape shape-1" aria-hidden="true"></div>
                    <div class="hero-shape shape-2" aria-hidden="true"></div>
                </div>
            </div>
        </header>

        <main id="main">
            <!-- Presentation -->
            <section class="section" id="presentation">
                <div class="container section-grid">
                    <div data-animate>
                        <h2 class="section-title">Présentation</h2>
                        <p class="section-lead">La société JMI 56 se déplace autour de Campénéac et de Josselin pour vous proposer ses services en terme de dépannage informatique.</p>
                        <p>Notre professionnel assure des prestations de qualité pour vous aider à trouver des solutions rapides à l’ensemble de vos pannes et autres problèmes avec votre ordinateur.</p>
                        <p>Avec plus de 10 ans d’expérience dans le domaine, vous pourrez faire confiance à notre professionnalisme pour établir un diagnostic préalable et vous fournir un devis gratuit et sans engagement.</p>
                        <p>Située à 78 Rue Du Val 56800 PLOERMEL, JMI 56 vous répond au 06 14 41 80 99 ou au 02 97 93 07 83, ou via notre formulaire de contact situé en bas de la page. N’attendez plus pour confier la réparation de votre PC à un expert en informatique.</p>
                    </div>
                    <div class="card" data-animate>
                        <h3>Votre spécialiste local</h3>
                        <p>Interventions rapides à domicile pour installation, dépannage et entretien de vos équipements.</p>
                        <div class="tag-list">
                            <span class="tag">Ploërmel</span>
                            <span class="tag">Campénéac</span>
                            <span class="tag">Josselin</span>
                            <span class="tag">Ruffiac</span>
                            <span class="tag">Guer</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Services -->
            <section class="section section--alt" id="services">
                <div class="container">
                    <div data-animate>
                        <h2 class="section-title">Services</h2>
                        <p class="section-lead">Des solutions claires pour les particuliers, avec un diagnostic précis et un suivi personnalisé.</p>
                    </div>
                    <div class="service-grid">
                        <article class="card service-card" data-animate>
                            <h3>Dépannage informatique</h3>
                            <p>Un virus s’est installé sur votre ordinateur ?</p>
                        </article>
                        <article class="card service-card" data-animate>
                            <h3>Installation internet</h3>
                            <p>Vous avez des problèmes pour installer votre box internet ?</p>
                        </article>
                        <article class="card service-card" data-animate>
                            <h3>Entretien ordinateur</h3>
                            <p>Votre ordinateur prend de plus en plus de temps à s’allumer ou à ouvrir une page ?</p>
                        </article>
                        <article class="card service-card" data-animate>
                            <h3>Service de proximité</h3>
                            <p>L’entreprise de réparation et de dépannage informatique JMI 56 intervient aux alentours de Ruffiac et de Guer pour garantir un service de proximité et rapide à tous les particuliers en recherche d’un spécialiste aguerri.</p>
                        </article>
                    </div>

                    <div class="process-block" data-animate>
                        <h3 class="section-subtitle">Une intervention claire, sans surprise</h3>
                        <div class="process-grid">
                            <div class="process-step">
                                <span>1</span>
                                <h4>Diagnostic préalable</h4>
                                <p>Analyse précise de la panne pour proposer la bonne solution.</p>
                            </div>
                            <div class="process-step">
                                <span>2</span>
                                <h4>Devis gratuit</h4>
                                <p>Un devis sans engagement avant toute intervention.</p>
                            </div>
                            <div class="process-step">
                                <span>3</span>
                                <h4>Intervention rapide</h4>
                                <p>Réparation et mise en service dans les meilleurs délais.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Zone d'intervention -->
            <section class="section" id="zone">
                <div class="container zone-grid">
                    <div data-animate>
                        <h2 class="section-title">Zone d'intervention</h2>
                        <p class="section-lead">Nous intervenons dans un rayon de 30 Km autour de Ploërmel.</p>
                        <p>L’entreprise est située à Ploërmel dans le Morbihan et fournit ses prestations aux particuliers dans un périmètre de 30km.</p>
                        <div class="tag-list">
                            <span class="tag">Ploërmel</span>
                            <span class="tag">Campénéac</span>
                            <span class="tag">Josselin</span>
                            <span class="tag">Ruffiac</span>
                            <span class="tag">Guer</span>
                            <span class="tag">Quily</span>
                        </div>
                    </div>
                    <div class="zone-map" data-animate>
                        <div class="map-shell">
                            <div id="service-map" class="map-canvas" data-map-address="78 Rue du Val, 56800 Ploërmel, France" role="region" aria-label="Carte de la zone d'intervention autour de Ploërmel"></div>
                            <div class="map-fallback" aria-hidden="true">
                                <iframe
                                    title="Carte Google Maps de Ploërmel"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://www.google.com/maps?q=Plo%C3%ABrmel%2C%20France&z=9&output=embed">
                                </iframe>
                                <span class="map-radius" aria-hidden="true"></span>
                            </div>
                        </div>
                        <noscript>
                            <p>Activez JavaScript pour voir la carte interactive de la zone d'intervention.</p>
                        </noscript>
                    </div>
                </div>
            </section>

            <!-- Partenaire -->
            <section class="section" id="partenaire">
                <div class="container" data-animate>
                    <h2 class="section-title">Partenaire</h2>
                    <div class="partner-card">
                        <div>
                            <h3>JK informatique</h3>
                            <p>Partenaire local pour des solutions informatiques complémentaires.</p>
                        </div>
                        <a class="btn btn-outline" href="https://www.jkinformatique.com/" target="_blank" rel="noopener">JK informatique</a>
                    </div>
                </div>
            </section>

            <!-- Contact -->
            <section class="section section--alt" id="contact">
                <div class="container">
                    <div data-animate>
                        <h2 class="section-title">Contacter</h2>
                        <p class="section-lead">Par téléphone ou via le formulaire ci-dessous.</p>
                    </div>
                    <div class="contact-grid">
                        <div class="contact-details" data-animate>
                            <div class="contact-card">
                                <strong>Adresse</strong>
                                <span>78 Rue Du Val</span><br>
                                <span>56800 PLOERMEL</span>
                            </div>
                            <div class="contact-card">
                                <strong>Téléphone</strong>
                                <a href="tel:+33614418099">06 14 41 80 99</a><br>
                                <a href="tel:+33297930783">02 97 93 07 83</a>
                            </div>
                            <div class="contact-card">
                                <strong>Nos horaires</strong>
                                <div class="hours-grid">
                                    <div class="hours-row"><span>Lundi</span><span>09h30-16h, 18h-20h</span></div>
                                    <div class="hours-row"><span>Mardi</span><span>09h30-16h, 18h-20h</span></div>
                                    <div class="hours-row"><span>Mercredi</span><span>09h-12h, 15h30-20h</span></div>
                                    <div class="hours-row"><span>Jeudi</span><span>09h30-12h50, 13h45-16h</span></div>
                                    <div class="hours-row"><span>Vendredi</span><span>09h30-12h50, 13h45-16h</span></div>
                                    <div class="hours-row"><span>Samedi</span><span>09h-12h50, 14h-17h30</span></div>
                                    <div class="hours-row"><span>Dimanche</span><span>Fermé</span></div>
                                </div>
                            </div>
                        </div>
                        <form class="contact-form card" data-animate method="post" action="{{ route('contact.submit') }}">
                            @csrf
                            @if (session('contact_success'))
                                <div class="auth-success">{{ session('contact_success') }}</div>
                            @endif
                            @if ($errors->any())
                                <div class="auth-error">{{ $errors->first() }}</div>
                            @endif
                            <div class="contact-field">
                                <input id="name" name="name" type="text" placeholder="Votre nom" value="{{ old('name') }}" required>
                                <label for="name">Nom</label>
                            </div>
                            <div class="contact-field">
                                <input id="phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel" minlength="14" maxlength="14" pattern="[0-9]{2}( [0-9]{2}){4}" placeholder="06 12 34 56 78" value="{{ old('phone') }}" required>
                                <label for="phone">Téléphone</label>
                            </div>
                            <div class="contact-field">
                                <textarea id="message" name="message" placeholder="Expliquez votre besoin" required>{{ old('message') }}</textarea>
                                <label for="message">Message</label>
                            </div>
                            <button class="btn btn-primary contact-submit" type="submit">Envoyer la demande</button>
                        </form>
                    </div>
                </div>
            </section>

            <!-- Mentions legales -->
            <section class="section" id="mentions-legales">
                <div class="container" data-animate>
                    <h2 class="section-title">Mentions légales</h2>
                    <div class="legal-grid">
                        <article class="card legal-card">
                            <h3>Éditeur du site</h3>
                            <p>JMI 56<br>78 Rue Du Val<br>56800 PLOERMEL</p>
                            <p>Téléphone : <a href="tel:+33614418099">06 14 41 80 99</a> — <a href="tel:+33297930783">02 97 93 07 83</a></p>
                        </article>
                        <article class="card legal-card">
                            <h3>Hébergement</h3>
                            <p>À compléter (nom de l’hébergeur, adresse, téléphone).</p>
                        </article>
                        <article class="card legal-card">
                            <h3>Données personnelles</h3>
                            <p>Les informations envoyées via le formulaire sont utilisées uniquement pour répondre aux demandes. Elles sont conservées pendant 12 mois maximum puis supprimées conformément au RGPD.</p>
                        </article>
                    </div>
                </div>
            </section>
        </main>

        <!-- Pied de page -->
        <footer class="footer">
            <div class="container footer-grid">
                <div>
                    <div class="footer-logo">
                        <img src="{{ asset('images/logo-jmi56.png') }}" alt="JMI 56" width="140" height="42">
                    </div>
                    <p>Votre installateur et dépanneur informatique à Ploërmel.</p>
                </div>
                <div>
                    <h3>Accès rapide</h3>
                    <div class="tag-list">
                        <a class="tag" href="#accueil">Accueil</a>
                        <a class="tag" href="#presentation">Présentation</a>
                        <a class="tag" href="#services">Services</a>
                        <a class="tag" href="#contact">Contact</a>
                        <a class="tag" href="#mentions-legales">Mentions légales</a>
                    </div>
                </div>
                <div>
                    <h3>Contacter</h3>
                    <p>78 Rue Du Val<br>56800 PLOERMEL</p>
                    <p><a href="tel:+33614418099">06 14 41 80 99</a><br><a href="tel:+33297930783">02 97 93 07 83</a></p>
                </div>
            </div>
            <div class="container">
                <details class="keywords">
                    <summary>Recherches fréquentes</summary>
                    <div class="keyword-list">
                        <span>Dépannage informatique Ploërmel</span>
                        <span>Dépannage informatique Campénéac</span>
                        <span>Dépannage informatique Quily</span>
                        <span>Dépannage informatique Josselin</span>
                        <span>Dépannage informatique Ruffiac</span>
                        <span>Dépannage informatique Guer</span>
                        <span>Réparation PC Ploërmel</span>
                        <span>Réparation PC Campénéac</span>
                        <span>Réparation PC Quily</span>
                        <span>Réparation PC Josselin</span>
                        <span>Réparation PC Ruffiac</span>
                        <span>Réparation PC Guer</span>
                        <span>Configuration d'ordinateur pour avoir accès à internet par un spécialiste à Ploërmel</span>
                        <span>Dépannage d'ordinateur à domicile pour supprimer un virus à Ploërmel</span>
                        <span>Dépanneur informatique qui peut intervenir rapidement auprès d'un particulier à Ploërmel</span>
                        <span>Entreprise de réparation informatique qui se déplace à domicile à Ploërmel</span>
                        <span>Entretien d'ordinateur par un professionnel pour augmenter la vitesse de chargement à Ploërmel</span>
                        <span>Installation d'internet sur un PC portable par une entreprise informatique à Ploërmel</span>
                        <span>Installation de WI-FI sur un ordinateur fixe chez un particulier à Ploërmel</span>
                        <span>Réparation d'ordinateur par un professionnel pour un problème d'alimentation à Ploërmel</span>
                        <span>Réparation de PC portable pour un problème de clavier à Ploërmel</span>
                        <span>Service de dépannage informatique en urgence avec assistance par un professionnel à Ploërmel</span>
                        <span>Société de dépannage informatique qui propose un devis gratuit à Ploërmel</span>
                        <span>Société informatique pour récupérer des données sur un ordinateur à Ploërmel</span>
                    </div>
                </details>
                <small>© JMI 56 — Installateur et dépanneur informatique à Ploërmel.</small>
            </div>
        </footer>

        @if (env('GOOGLE_MAPS_KEY'))
            <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_KEY') }}&callback=initMap" async defer></script>
        @endif
    </body>
</html>
