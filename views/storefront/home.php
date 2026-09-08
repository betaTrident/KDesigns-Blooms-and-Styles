<?php
declare(strict_types=1);
?>
<?php View::render('partials/head', ['pageTitle' => $pageTitle, 'cssBundle' => $cssBundle]); ?>
<body>

<?php View::render('partials/storefront-nav', [
    'is_logged_in' => $is_logged_in ?? false,
    'is_admin'     => $is_admin ?? false,
    'user_name'    => $user_name ?? '',
    'navVariant'   => 'storefront',
]); ?>

    <!-- Hero Section -->
    <header id="home" class="hero" style="background-image: url('<?= e(kd_image_url('images/IMG_8620.JPG')); ?>');">
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
            <img src="<?= e(kd_image_url('images/0e011570-2704-40fb-9a24-6a18d7c52867.jfif')); ?>" alt="Fresh Bouquet" class="img-main">
            <img src="<?= e(kd_image_url('images/IMG_8603.JPG')); ?>" alt="Claire Ann Ross - Florist" class="img-inset">
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
            <div class="catalog-filter-scroll">
                <div class="catalog-filters">
                    <button class="filter-btn active" data-filter="all">ALL</button>
                    <button class="filter-btn" data-filter="fresh">FRESH BOUQUET</button>
                    <button class="filter-btn" data-filter="dried">DRIED BOUQUET</button>
                    <button class="filter-btn" data-filter="bloombox">BLOOM BOX</button>
                    <button class="filter-btn" data-filter="glassdome">GLASS DOME</button>
                    <button class="filter-btn" data-filter="others">OTHERS</button>
                </div>
            </div>
        </div>

        <div class="catalog-grid">
<?php foreach (($catalog ?? []) as $product): ?>
<?php View::render('partials/catalog-card', ['product' => $product]); ?>
<?php endforeach; ?>
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

<?php View::render('partials/storefront-footer', []); ?>

<script src="<?= e(kd_asset('assets/js/catalog.js')); ?>" defer></script>
</body>
</html>

