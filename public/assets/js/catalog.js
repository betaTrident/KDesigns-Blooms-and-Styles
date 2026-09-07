document.addEventListener('DOMContentLoaded', () => {
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
        { id: 'home', href: '/#home' },
        { id: 'about', href: '/#about' },
        { id: 'catalog', href: '/#catalog' }
    ];

    const handleScrollSpy = () => {
        const scrollPos = window.scrollY + 100;
        sections.forEach((section) => {
            const el = document.getElementById(section.id);
            if (!el) {
                return;
            }
            const top = el.offsetTop;
            const height = el.offsetHeight;
            if (scrollPos >= top && scrollPos < top + height) {
                document.querySelectorAll('.nav-links a, .mobile-nav a').forEach((link) => {
                    link.classList.toggle('active', link.getAttribute('href') === section.href);
                });
            }
        });
    };
    window.addEventListener('scroll', handleScrollSpy, { passive: true });
});
