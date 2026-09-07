document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        const handleNavScroll = () => {
            navbar.classList.toggle('scrolled', window.scrollY > 40);
        };
        window.addEventListener('scroll', handleNavScroll, { passive: true });
        handleNavScroll();
    }

    const closePanel = (btn, panel) => {
        panel.classList.remove('is-open');
        btn.setAttribute('aria-expanded', 'false');
        navbar?.classList.remove('menu-open');
        document.body.classList.remove('nav-open');
        const icon = btn.querySelector('i');
        if (icon) {
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-bars');
        }
    };

    document.querySelectorAll('[data-nav-toggle]').forEach((btn) => {
        const panelId = btn.getAttribute('aria-controls');
        const panel = panelId ? document.getElementById(panelId) : null;
        if (!panel) {
            return;
        }

        btn.addEventListener('click', () => {
            const open = !panel.classList.contains('is-open');
            if (open) {
                panel.classList.add('is-open');
                btn.setAttribute('aria-expanded', 'true');
                navbar?.classList.add('menu-open');
                document.body.classList.add('nav-open');
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-xmark');
                }
            } else {
                closePanel(btn, panel);
            }
        });

        panel.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => closePanel(btn, panel));
        });
    });

    const adminSidebar = document.getElementById('admin-sidebar');
    const adminToggle = document.getElementById('admin-menu-toggle');
    const adminOverlay = document.getElementById('admin-sidebar-overlay');
    if (adminSidebar && adminToggle) {
        const setAdminOpen = (open) => {
            adminSidebar.classList.toggle('is-open', open);
            adminToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            adminOverlay?.classList.toggle('hidden', !open);
            document.body.classList.toggle('nav-open', open);
        };
        adminToggle.addEventListener('click', () => {
            setAdminOpen(!adminSidebar.classList.contains('is-open'));
        });
        adminOverlay?.addEventListener('click', () => setAdminOpen(false));
        adminSidebar.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.matchMedia('(max-width: 767px)').matches) {
                    setAdminOpen(false);
                }
            });
        });
    }
});
