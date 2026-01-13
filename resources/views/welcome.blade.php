<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Installateur et dépanneur informatique à Ploërmel | JMI 56</title>
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
                        <a class="btn btn-primary" href="#contact">Devis gratuit</a>
                    </div>
                </nav>
                <div class="nav-cta">
                    <a class="btn btn-primary" href="#contact">Devis gratuit</a>
                </div>
            </div>
        </div>

        <header class="hero" id="accueil">
            <div class="container hero-grid">
                <div class="hero-copy" data-animate>
                    <p class="eyebrow">JMI 56</p>
                    <h1>Votre installateur et dépanneur informatique à Ploërmel</h1>
                    <p class="lead">Spécialisée des services informatiques, JMI 56 intervient à domicile pour l'installation et le dépannage d'équipements informatiques et logiciels à Ploërmel.</p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="sms:+33614418099">06 14 41 80 99</a>
                        <a class="btn btn-outline" href="tel:+33297930783">02 97 93 07 83</a>
                        <a class="btn btn-ghost" href="#contact">Contact</a>
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
                    <img src="{{ asset('images/hero.jpg') }}" alt="Atelier informatique et réparation de matériel">
                    <div class="hero-badge">Intervention à domicile</div>
                    <div class="hero-shape shape-1" aria-hidden="true"></div>
                    <div class="hero-shape shape-2" aria-hidden="true"></div>
                </div>
            </div>
        </header>

        <main id="main">
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
                        <svg viewBox="0 0 600 420" role="img" aria-label="Carte stylisée de la zone d'intervention">
                            <rect width="600" height="420" rx="32" fill="none" stroke="#f0e1c6" stroke-width="2" />
                            <path d="M80 120C140 80 240 70 320 120C400 170 460 180 520 140" stroke="#e3c68a" stroke-width="3" fill="none" />
                            <path d="M60 280C160 230 260 240 360 280C440 310 520 320 560 280" stroke="#f1d9aa" stroke-width="3" fill="none" />
                            <circle class="pin" cx="300" cy="210" r="12" />
                            <circle cx="300" cy="210" r="30" fill="none" stroke="#ff9e00" stroke-width="2" opacity="0.6" />
                            <circle cx="300" cy="210" r="60" fill="none" stroke="#ff9e00" stroke-width="1" opacity="0.4" />
                        </svg>
                    </div>
                </div>
            </section>

            <section class="section" id="partenaire">
                <div class="container" data-animate>
                    <h2 class="section-title">Partenaire</h2>
                    <div class="partner-card">
                        <div>
                            <h3>JK informatique</h3>
                            <p>Partenaire local pour des solutions informatiques complémentaires.</p>
                        </div>
                        <a class="btn btn-outline" href="#contact">Nous contacter</a>
                    </div>
                </div>
            </section>

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
                                <a href="tel:+33297930783">02 97 93 07 83</a><br>
                                <a href="tel:+33614418099">06 14 41 80 99</a>
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
                        <form class="contact-form card" data-animate method="get" action="#">
                            <div>
                                <label for="name">Nom</label>
                                <input id="name" name="name" type="text" placeholder="Votre nom">
                            </div>
                            <div>
                                <label for="phone">Téléphone</label>
                                <input id="phone" name="phone" type="tel" inputmode="numeric" autocomplete="tel" minlength="14" maxlength="14" pattern="[0-9]{2}( [0-9]{2}){4}" placeholder="06 12 34 56 78">
                            </div>
                            <div>
                                <label for="message">Message</label>
                                <textarea id="message" name="message" placeholder="Expliquez votre besoin"></textarea>
                            </div>
                            <button class="btn btn-primary" type="submit">Envoyer la demande</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>

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
                    </div>
                </div>
                <div>
                    <h3>Contacter</h3>
                    <p>78 Rue Du Val<br>56800 PLOERMEL</p>
                    <p><a href="tel:+33297930783">02 97 93 07 83</a><br><a href="tel:+33614418099">06 14 41 80 99</a></p>
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
    </body>
</html>
