document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        const handleNavScroll = () => {
            if (window.scrollY > 40) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        };
        window.addEventListener('scroll', handleNavScroll, { passive: true });
        handleNavScroll();
    }

    const filterBtns = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.catalog-grid .product-card');

    filterBtns.forEach((btn) => {
        btn.addEventListener('click', () => {
            filterBtns.forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');

            const filterValue = btn.getAttribute('data-filter');

            productCards.forEach((card) => {
                const cardCategory = card.getAttribute('data-category');
                if (filterValue === 'all' || cardCategory === filterValue) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    const sections = [
        { id: 'home', link: document.querySelector('.nav-links a[href="/#home"]') },
        { id: 'about', link: document.querySelector('.nav-links a[href="/#about"]') },
        { id: 'catalog', link: document.querySelector('.nav-links a[href="/#catalog"]') }
    ];

    const handleScrollSpy = () => {
        const scrollPos = window.scrollY + 100;
        sections.forEach((section) => {
            const el = document.getElementById(section.id);
            if (el && section.link) {
                const top = el.offsetTop;
                const height = el.offsetHeight;
                if (scrollPos >= top && scrollPos < top + height) {
                    sections.forEach((s) => s.link && s.link.classList.remove('active'));
                    section.link.classList.add('active');
                }
            }
        });
    };
    window.addEventListener('scroll', handleScrollSpy, { passive: true });
});
