<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KDesigns Blooms and Styles</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <a href="/" class="logo-brand">
                    <img src="images/logo.png" alt="KDESIGNS Blooms and style" class="logo-img">
                </a>
            </div>
            <ul class="nav-links">
                <li><a href="#home" class="active">HOME</a></li>
                <li><a href="#about">ABOUT</a></li>
                <li><a href="#catalog">CATALOG</a></li>
            </ul>
            <div class="nav-actions">
                <a href="#" class="btn-login">LOG IN</a>
                <a href="#" class="btn-cart"><i class="fa-solid fa-cart-shopping"></i> CART</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header id="home" class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <span class="subtitle">PREMIER FLORAL STUDIO</span>
            <h1>Turning<br>flowers<br><span class="italic-pink">into timeless</span><br>memories.</h1>
            <div class="hero-info">
                <p>• We are open 10am to 6pm Monday to Saturday.</p>
                <p>• We only accept pre order on Sundays.</p>
            </div>
            <div class="hero-buttons">
                <a href="#catalog" class="btn-pink">SHOP OUR BLOOMS</a>
                <a href="#about" class="btn-story">OUR STORY</a>
            </div>
        </div>
    </header>

    <!-- About Section -->
    <section id="about" class="about-section container">
        <div class="about-images">
            <div class="badge-year"><span>3</span><br>YEARS</div>
            <img src="images/IMG_8603.JPG" alt="Fresh Bouquet" class="img-main">
            <img src="images/0e011570-2704-40fb-9a24-6a18d7c52867.jfif" alt="Claire Ann Ross - Florist" class="img-inset">
        </div>
        <div class="about-text">
            <span class="subtitle">OUR STORY</span>
            <h2>Where flowers<br><span class="italic-burgundy">tell stories</span></h2>
            <p>KDesigns Blooms & Styles is a premier floral design studio rooted in the belief that every individual bloom carries its own distinct meaning. We craft immersive botanical experiences by celebrating the raw beauty of seasonal stems, prioritizing organic textures, and allowing the natural architecture of the flowers to guide our intentional designs.</p>
            <p>Founded by Claire Ann Ross with a deep reverence for nature's palette, each arrangement is a considered composition — never forced, always in conversation with the light, the space, and the people within it.</p>
            
            <div class="about-stats">
                <div class="stat">
                    <h3>98%</h3>
                    <p>CLIENT SATISFACTION</p>
                </div>
                <div class="stat">
                    <h3>10</h3>
                    <p>WORKSHOPS ATTENDED</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Catalog Section -->
    <section id="catalog" class="catalog-section container">
        <div class="catalog-header">
            <div>
                <span class="subtitle">WHAT WE CREATE</span>
                <h2>Flowers Catalog</h2>
            </div>
            <div class="catalog-filters">
                <button class="filter-btn active" data-filter="all">ALL</button>
                <button class="filter-btn" data-filter="fresh">FRESH BOUQUET</button>
                <button class="filter-btn" data-filter="dried">DRIED BOUQUET</button>
                <button class="filter-btn" data-filter="bloombox">BLOOM BOX</button>
                <button class="filter-btn" data-filter="glassdome">GLASS DOME</button>
                <button class="filter-btn" data-filter="others">OTHERS</button>
            </div>
        </div>

        <div class="catalog-grid">
            <!-- Product Card 1 -->
            <div class="product-card" data-category="fresh">
                <div class="product-img-wrapper">
                    <span class="tag tag-bestseller">BESTSELLER</span>
                    <img src="images/IMG_8603.JPG" alt="Crimson Romance Bouquet">
                </div>
                <div class="product-info">
                    <span class="category">FRESH BOUQUET</span>
                    <h4>Crimson Romance Bouquet</h4>
                    <p class="desc">Lush red roses with baby's breath and eucalyptus, hand-tied with silk ribbon.</p>
                    <span class="stock in-stock">12 in stock</span>
                    <div class="product-bottom">
                        <span class="price">₱1,850</span>
                        <button class="btn-primary add-to-cart">ADD TO CART</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 2 -->
            <div class="product-card" data-category="fresh">
                <div class="product-img-wrapper">
                    <span class="tag tag-limited">LIMITED</span>
                    <img src="images/IMG_8597.JPG" alt="Pastel Peony Bouquet">
                </div>
                <div class="product-info">
                    <span class="category">FRESH BOUQUET</span>
                    <h4>Pastel Peony Bouquet</h4>
                    <p class="desc">Soft blush peonies and garden roses in a dreamy pastel palette.</p>
                    <span class="stock in-stock">5 in stock</span>
                    <div class="product-bottom">
                        <span class="price">₱2,200</span>
                        <button class="btn-primary add-to-cart">ADD TO CART</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 3 -->
            <div class="product-card" data-category="fresh">
                <div class="product-img-wrapper">
                    <img src="images/IMG_8630.JPG" alt="Sunflower & Wildflower Mix">
                </div>
                <div class="product-info">
                    <span class="category">FRESH BOUQUET</span>
                    <h4>Sunflower & Wildflower Mix</h4>
                    <p class="desc">Cheerful sunflowers paired with seasonal wildflowers and greenery.</p>
                    <span class="stock in-stock">20 in stock</span>
                    <div class="product-bottom">
                        <span class="price">₱1,200</span>
                        <button class="btn-primary add-to-cart">ADD TO CART</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 4 -->
            <div class="product-card" data-category="fresh">
                <div class="product-img-wrapper">
                    <span class="tag tag-luxury">LUXURY</span>
                    <img src="images/IMG_8618.JPG" alt="Orchid Elegance Vase">
                </div>
                <div class="product-info">
                    <span class="category">FRESH BOUQUET</span>
                    <h4>Orchid Elegance Vase</h4>
                    <p class="desc">Cascading white orchids in a handcrafted ceramic vase — a statement piece.</p>
                    <span class="stock low-stock">Only 3 left</span>
                    <div class="product-bottom">
                        <span class="price">₱3,500</span>
                        <button class="btn-primary add-to-cart">ADD TO CART</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 5 -->
            <div class="product-card" data-category="dried">
                <div class="product-img-wrapper">
                    <img src="images/IMG_8586.JPG" alt="Garden Table Centerpiece">
                </div>
                <div class="product-info">
                    <span class="category">DRIED BOUQUET</span>
                    <h4>Garden Table Centerpiece</h4>
                    <p class="desc">Lush garden-style centerpiece with seasonal blooms, foliage, and dried botanicals.</p>
                    <span class="stock in-stock">8 in stock</span>
                    <div class="product-bottom">
                        <span class="price">₱2,800</span>
                        <button class="btn-primary add-to-cart">ADD TO CART</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 6 -->
            <div class="product-card" data-category="dried">
                <div class="product-img-wrapper">
                    <img src="images/IMG_8587.JPG" alt="Lavender Dreams Bundle">
                </div>
                <div class="product-info">
                    <span class="category">DRIED BOUQUET</span>
                    <h4>Lavender Dreams Bundle</h4>
                    <p class="desc">Fragrant dried lavender bundles, perfect for home décor or gifts.</p>
                    <span class="stock in-stock">15 in stock</span>
                    <div class="product-bottom">
                        <span class="price">₱980</span>
                        <button class="btn-primary add-to-cart">ADD TO CART</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 7 -->
            <div class="product-card" data-category="dried">
                <div class="product-img-wrapper">
                    <span class="tag tag-new">NEW</span>
                    <img src="images/IMG_8564.JPG" alt="Tropical Bloom Arrangement">
                </div>
                <div class="product-info">
                    <span class="category">DRIED BOUQUET</span>
                    <h4>Tropical Bloom Arrangement</h4>
                    <p class="desc">Bold heliconias, anthuriums, and tropical foliage in an architectural composition.</p>
                    <span class="stock in-stock">4 in stock</span>
                    <div class="product-bottom">
                        <span class="price">₱3,200</span>
                        <button class="btn-primary add-to-cart">ADD TO CART</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 8 -->
            <div class="product-card" data-category="dried">
                <div class="product-img-wrapper">
                    <span class="tag tag-luxury">LUXURY</span>
                    <img src="images/IMG_8607.JPG" alt="Bridal White Cascade">
                </div>
                <div class="product-info">
                    <span class="category">DRIED BOUQUET</span>
                    <h4>Bridal White Cascade</h4>
                    <p class="desc">All-white cascading bridal bouquet with garden roses, stephanotis, and lily of the valley.</p>
                    <span class="stock low-stock">Only 2 left</span>
                    <div class="product-bottom">
                        <span class="price">₱4,800</span>
                        <button class="btn-primary add-to-cart">ADD TO CART</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 9 -->
            <div class="product-card" data-category="dried">
                <div class="product-img-wrapper">
                    <img src="images/IMG_8643.JPG" alt="Autumn Harvest Wreath">
                </div>
                <div class="product-info">
                    <span class="category">DRIED BOUQUET</span>
                    <h4>Autumn Harvest Wreath</h4>
                    <p class="desc">Dried autumn botanicals including protea, cotton, and seed pods on a natural base.</p>
                    <span class="stock out-of-stock">Out of Stock</span>
                    <div class="product-bottom">
                        <span class="price">₱1,600</span>
                        <button class="btn-sold-out" disabled>SOLD OUT</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 10 -->
            <div class="product-card" data-category="bloombox">
                <div class="product-img-wrapper">
                    <img src="images/IMG_8635.JPG" alt="Bloom Arrangement No. 10">
                </div>
                <div class="product-info">
                    <span class="category">BLOOM BOX</span>
                    <h4>Bloom Arrangement No. 10</h4>
                    <p class="desc">A curated seasonal arrangement — update this description to match your product.</p>
                    <span class="stock in-stock">10 in stock</span>
                    <div class="product-bottom">
                        <span class="price">₱1,400</span>
                        <button class="btn-primary add-to-cart">ADD TO CART</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 11 -->
            <div class="product-card" data-category="others">
                <div class="product-img-wrapper">
                    <img src="images/IMG_8606.JPG" alt="Bloom Arrangement No. 11">
                </div>
                <div class="product-info">
                    <span class="category">OTHERS</span>
                    <h4>Bloom Arrangement No. 11</h4>
                    <p class="desc">A curated seasonal arrangement — update this description to match your product.</p>
                    <span class="stock in-stock">10 in stock</span>
                    <div class="product-bottom">
                        <span class="price">₱1,400</span>
                        <button class="btn-primary add-to-cart">ADD TO CART</button>
                    </div>
                </div>
            </div>

            <!-- Product Card 12 -->
            <div class="product-card" data-category="glassdome">
                <div class="product-img-wrapper">
                    <img src="images/IMG_8582.JPG" alt="Bloom Arrangement No. 12">
                </div>
                <div class="product-info">
                    <span class="category">GLASS DOME</span>
                    <h4>Bloom Arrangement No. 12</h4>
                    <p class="desc">A curated seasonal arrangement — update this description to match your product.</p>
                    <span class="stock in-stock">10 in stock</span>
                    <div class="product-bottom">
                        <span class="price">₱1,400</span>
                        <button class="btn-primary add-to-cart">ADD TO CART</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <div class="testimonials-header">
                <span class="subtitle text-tan">KIND WORDS</span>
                <h2 class="text-white">What Our Clients Say</h2>
            </div>
            
            <div class="testimonials-grid">
                <!-- Testimonial 1 -->
                <div class="testimonial-card">
                    <div class="quote-mark">"</div>
                    <p>Thank you kaayo sa inyo arrangement &lt;3 gwapa kaayo ag bulak and happy kaayo ang ako miga intawon pag dawat niya.</p>
                    <div class="client-info">
                        <div class="avatar">H</div>
                        <div>
                            <h5>Hannah Montana</h5>
                            <span>2026</span>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="testimonial-card">
                    <div class="quote-mark">"</div>
                    <p>OMG, Thankk youuu kaayoo for the Prettyy flowerss!! 😭 🤍 I didn't expect it to be this big and very fressh sa personal. I'm amazed by how it was delivered securely and on time huhu. Thank you so much for making my day soo wonderful as this flowers. We will surely be ordering again! 🫶💖</p>
                    <div class="client-info">
                        <div class="avatar">D</div>
                        <div>
                            <h5>Donny Pangilinan</h5>
                            <span>2024</span>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="testimonial-card">
                    <div class="quote-mark">"</div>
                    <p>Huge thank you for the stunning arrangement you put together for my anniversary! My partner was absolutely blown away by how huge and vibrant the bouquet was, and it honestly made our whole day so much more special. The delivery was right on time, the packaging was super cute, and the blooms are still looking perfect days later. You guys are the absolute best!</p>
                    <div class="client-info">
                        <div class="avatar">A</div>
                        <div>
                            <h5>Annabelle</h5>
                            <span>2025</span>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 4 -->
                <div class="testimonial-card">
                    <div class="quote-mark">"</div>
                    <p>Absolutely stunning work. The flowers were incredibly fresh, and the composition was effortless and elegant. It's rare to see such thoughtful attention to detail in floral design. I couldn't be happier!</p>
                    <div class="client-info">
                        <div class="avatar">S</div>
                        <div>
                            <h5>Sarah De Guzman</h5>
                            <span>2026</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback Form -->
            <div class="feedback-form-container">
                <h3>Share Your Experience</h3>
                <p>We'd love to hear from you.</p>
                <form action="#" method="POST">
                    <div class="form-group">
                        <label>YOUR NAME</label>
                        <input type="text" placeholder="e.g. Maria Santos">
                    </div>
                    <div class="form-group">
                        <label>YOUR MESSAGE</label>
                        <textarea rows="4" placeholder="Tell us about your experience..."></textarea>
                    </div>
                    <button type="submit" class="btn-pink w-full">SEND FEEDBACK</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <a href="/" class="footer-logo-link">
                    <img src="images/logo.png" alt="KDESIGNS Blooms and style" class="footer-logo-img">
                </a>
                <p>Premier floral arrangements dedicated to botanical storytelling and intentional beauty.</p>
                <div class="social-links">
                    <a href="#">INSTAGRAM</a>
                    <a href="#">FACEBOOK</a>
                    <a href="#">TIKTOK</a>
                </div>
            </div>
            
            <div class="footer-links">
                <h4>STUDIO</h4>
                <ul>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#">Our Process</a></li>
                    <li><a href="#">Sustainability</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h4>SERVICES</h4>
                <ul>
                    <li><a href="#catalog">Fresh and Dried Bouquets</a></li>
                    <li><a href="#catalog">Korean Baskets</a></li>
                    <li><a href="#catalog">Bloom Box</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h4>CONTACT</h4>
                <ul>
                    <li><a href="mailto:kdesigns@gmail.com">kdesigns@gmail.com</a></li>
                    <li><a href="tel:+639056487476">+63 905 648 7476</a></li>
                    <li>Dumaguete City</li>
                </ul>
            </div>
        </div>
        
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 KDesigns Blooms & Styles. All rights reserved.</p>
                <p class="italic-serif">Turning flowers into timeless memories.</p>
            </div>
        </div>
    </footer>

    <!-- Interactive Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Sticky Navbar Scroll Effect
            const navbar = document.querySelector('.navbar');
            const handleNavScroll = () => {
                if (window.scrollY > 40) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            };
            window.addEventListener('scroll', handleNavScroll, { passive: true });
            handleNavScroll();

            // Catalog Filtering
            const filterBtns = document.querySelectorAll('.filter-btn');
            const productCards = document.querySelectorAll('.catalog-grid .product-card');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    const filterValue = btn.getAttribute('data-filter');

                    productCards.forEach(card => {
                        const cardCategory = card.getAttribute('data-category');
                        if (filterValue === 'all' || cardCategory === filterValue) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });

            // Scrollspy for active nav link
            const sections = [
                { id: 'home', link: document.querySelector('.nav-links a[href="#home"]') },
                { id: 'about', link: document.querySelector('.nav-links a[href="#about"]') },
                { id: 'catalog', link: document.querySelector('.nav-links a[href="#catalog"]') }
            ];

            const handleScrollSpy = () => {
                const scrollPos = window.scrollY + 100;
                sections.forEach(section => {
                    const el = document.getElementById(section.id);
                    if (el && section.link) {
                        const top = el.offsetTop;
                        const height = el.offsetHeight;
                        if (scrollPos >= top && scrollPos < top + height) {
                            sections.forEach(s => s.link && s.link.classList.remove('active'));
                            section.link.classList.add('active');
                        }
                    }
                });
            };
            window.addEventListener('scroll', handleScrollSpy, { passive: true });
        });
    </script>

</body>
</html>