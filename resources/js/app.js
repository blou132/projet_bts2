const initSiteUI = () => {
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

    const onScroll = () => {
        if (!navShell) {
            return;
        }
        navShell.classList.toggle('nav-scrolled', window.scrollY > 8);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

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
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSiteUI);
} else {
    initSiteUI();
}
