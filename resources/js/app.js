// Carte Google avec rayon autour de Ploermel
const initMap = () => {
    const mapElement = document.getElementById('service-map');
    if (!mapElement || mapElement.dataset.mapInitialized === 'true') {
        return;
    }
    if (!window.google || !window.google.maps) {
        return;
    }

    const fallbackCenter = { lat: 47.9326, lng: -2.3979 };
    const address = mapElement.dataset.mapAddress || 'Ploërmel, France';
    const map = new window.google.maps.Map(mapElement, {
        center: fallbackCenter,
        zoom: 10,
        disableDefaultUI: true,
        zoomControl: true,
        gestureHandling: 'cooperative',
        styles: [
            { elementType: 'geometry', stylers: [{ color: '#f7f1e6' }] },
            { elementType: 'labels.text.stroke', stylers: [{ color: '#f7f1e6' }] },
            { elementType: 'labels.text.fill', stylers: [{ color: '#6e6a63' }] },
            { featureType: 'administrative', elementType: 'geometry.stroke', stylers: [{ color: '#e2d2b5' }] },
            { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#f0e2c8' }] },
            { featureType: 'road', elementType: 'geometry.stroke', stylers: [{ color: '#e6d7bc' }] },
            { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#d7e7f5' }] },
            { featureType: 'poi', elementType: 'labels.text.fill', stylers: [{ color: '#b06c19' }] },
        ],
    });

    const marker = new window.google.maps.Marker({
        position: fallbackCenter,
        map,
        title: 'JMI 56 - Ploërmel',
    });

    const circle = new window.google.maps.Circle({
        strokeColor: '#ff9e00',
        strokeOpacity: 0.6,
        strokeWeight: 2,
        fillColor: '#ff9e00',
        fillOpacity: 0.2,
        map,
        center: fallbackCenter,
        radius: 30000,
    });

    const zoomToMarker = () => {
        const position = marker.getPosition();
        if (!position) {
            return;
        }
        map.setZoom(15);
        map.panTo(position);
    };

    marker.addListener('click', zoomToMarker);

    const geocoder = new window.google.maps.Geocoder();
    geocoder.geocode({ address }, (results, status) => {
        if (status === 'OK' && results && results[0]) {
            const location = results[0].geometry.location;
            marker.setPosition(location);
            circle.setCenter(location);
            map.setCenter(location);
        }
    });

    const mapShell = mapElement.closest('.map-shell');
    if (mapShell) {
        mapShell.classList.add('is-loaded');
    }
    mapElement.dataset.mapInitialized = 'true';
};

window.initMap = initMap;

// UI generale (navigation, scroll, animations, formulaire)
const initSiteUI = () => {
    // Menu mobile
    const navToggle = document.querySelector('[data-nav-toggle]');
    const navLinks = document.querySelector('[data-nav]');
    const navShell = document.querySelector('.nav-shell');

    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            const isOpen = navLinks.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', String(isOpen));
        });

        navLinks.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    // Effet sur la barre de navigation au scroll
    const onScroll = () => {
        if (!navShell) {
            return;
        }
        navShell.classList.toggle('nav-scrolled', window.scrollY > 8);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Apparition des sections
    const animatedElements = document.querySelectorAll('[data-animate]');
    if (animatedElements.length > 0) {
        const observer = new IntersectionObserver(
            (entries, obs) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        obs.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12 }
        );

        animatedElements.forEach((element) => observer.observe(element));
    }

    // Formatage du telephone
    const phoneInput = document.querySelector('#phone');
    if (phoneInput) {
        const formatPhone = (value) => {
            const digits = value.replace(/\D/g, '').slice(0, 10);
            return digits.replace(/(\d{2})(?=\d)/g, '$1 ').trim();
        };

        const handlePhoneInput = () => {
            const formatted = formatPhone(phoneInput.value);
            phoneInput.value = formatted;
        };

        phoneInput.addEventListener('input', handlePhoneInput);
        phoneInput.addEventListener('blur', handlePhoneInput);
        handlePhoneInput();
    }

    // Si la carte est chargee, on initialise
    if (window.google && window.google.maps) {
        initMap();
    }
};

// Lancement apres chargement du DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSiteUI);
} else {
    initSiteUI();
}
