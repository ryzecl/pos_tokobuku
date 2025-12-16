<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daebook - Discover Books</title>
    <!-- Font diganti jadi Poppins seperti code 2 -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
    <nav id="navbar">
        <div class="container navbar-container">
            <!-- <div class="logo">
                <div class="logo-icon"><i data-lucide="book-open"></i></div>
                <span class="logo-text">Daebook</span>
            </div> -->
            <div class="logo">
                <img src="assets/img/logo/logo.png" alt="Daebook Logo">
            </div>
            <div class="nav-links">
                <a href="#home" class="nav-link">Home</a>
                <a href="#about" class="nav-link">About</a>
                <a href="#categories" class="nav-link">All Books</a>
                <a href="#reviews" class="nav-link">Reviews</a>
                <div class="nav-cta">
                    <a href="login.php" class="btn-primary" style="text-decoration: none;">Log In</a>
                </div>
            </div>

            <button class="mobile-toggle" id="mobile-toggle">
                <i data-lucide="menu" id="menu-icon"></i>
                <i data-lucide="x" id="close-icon" style="display: none;"></i>
            </button>
        </div>
        <div class="mobile-menu" id="mobile-menu">
            <a href="#home" class="mobile-link">Home</a>
            <a href="#about" class="mobile-link">About</a>
            <a href="#categories" class="mobile-link">All Books</a>
            <a href="#reviews" class="mobile-link">Reviews</a>
            <a href="login.php"><button class="btn-primary mobile-btn">Log In</button></a>
        </div>
    </nav>

    <main>
        <section id="home" class="home">
            <div class="home-bg-blur home-blur-1"></div>
            <div class="home-bg-blur home-blur-2"></div>
            <div class="container home-container">
                <div class="home-content fade-in-left">
                    <!-- <h1 class="home-title">Discover Books <br><span class="gradient-text">Worth Your Time</span> <br>Today.</h1> -->
                    <h1>Discover Books<br><span>Worth</span> Your <span>Time</span><br>Today.</h1>
                    <p class="home-description">Curated books to sharpen thinking, spark curiosity, and inspire meaningful personal growth.</p>
                    <div class="home-buttons">
                        <a href="#categories"><button class="btn-home-primary">Explore More Now!</button></a>
                        <button class="btn-home-secondary">Find Books</button>
                    </div>
                </div>
                <div class="home-image fade-in-right">
                    <div class="image-container">
                        <div class="glow-platform"></div>
                        <div class="book-image">
                            <img src="assets/img/table-book.png" alt="Magic Book" class="book-img">
                            <div class="particle particle-1"></div>
                            <div class="particle particle-2"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            define('ALLOW_CHAT_ACCESS', true);
            include 'components/chat.php';
            ?>
        </section>

        <section class="publishers">
            <div class="container">
                <p class="publishers-label">Featured Publishers</p>
                <div class="publishers-grid " id="about">
                    <div class="publisher-item">
                        <a href="https://www.gramedia.com/" target="_blank" rel="noopener noreferrer"><img src="assets/img/logo/gramedia-logo.png" alt="Gramedia"></a>
                    </div>
                    <div class="publisher-item">
                        <a href="https://mizan.com/" target="_blank" rel="noopener noreferrer"><img src="assets/img/logo/mizan-logo.png" alt="Mizan"></a>
                    </div>
                    <div class="publisher-item">
                        <a href="https://bintangpustaka.com/" target="_blank" rel="noopener noreferrer"><img src="assets/img/logo/bentang-pustaka-logo.png" alt="Bentang"></a>
                    </div>
                    <div class="publisher-item">
                        <a href="https://penerbitdeepublish.com/" target="_blank" rel="noopener noreferrer"><img src="assets/img/logo/deepublish-logo.png" alt="Deepublish"></a>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="about">
            <div class="container about-container">
                <div class="about-image fade-in-view">
                    <div class="about-image-bg"></div>
                    <img src="assets/img/about-us.png" alt="Couple reading" class="about-img">
                </div>
                <div class="about-content fade-in-view">
                    <span class="section-label">About Us</span>
                    <h2 class="section-title">Daebook: A Smarter Way to Discover, Buy, and Experience Books</h2>
                    <p class="section-description">Daebook exists for readers who seek more than just finishing a book — they read to expand their thinking.</p>
                    <div class="features-list">
                        <div class="feature-item"><i data-lucide="check-circle-2" class="feature-icon"></i><span>Intelligent discovery features</span></div>
                        <div class="feature-item"><i data-lucide="check-circle-2" class="feature-icon"></i><span>Seamless buying experience</span></div>
                        <div class="feature-item"><i data-lucide="check-circle-2" class="feature-icon"></i><span>Growth as a natural part of life</span></div>
                    </div>
                    <div class="about-footer">
                        <span class="about-thank">Thank You for Choosing Daebook &lt;3</span>
                        <div class="about-logo">
                            <img src="assets/img/logo/logo.png" alt="Daebook Logo">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="categories" class="book-search">
            <div class="star-deco star-left">*</div>
            <div class="star-deco star-right">*</div>
            <div class="container">
                <div class="search-header">
                    <span class="section-label-gray">Book Genres</span>
                    <h2 class="section-title-center">Explore different type book <br> and genres</h2>
                    <div class="search-wrapper">
                        <div class="search-glow"></div>
                        <form class="search-form" id="search-form" action="all_book.php" method="GET">
                            <input type="text" name="search" id="search-input" placeholder="Search all books: e.g. 'The Da Vinci Code' or 'Cyberpunk Novels'" class="search-input">
                            <button type="submit" class="search-btn" id="search-btn">
                                <i data-lucide="search" id="search-icon"></i>
                                <!-- <i data-lucide="loader-2" id="loader-icon" class="spin" style="display: none;"></i> -->
                            </button>
                        </form>
                    </div>
                </div>
                <div class="ai-results" id="ai-results"></div>
                <div class="genre-cards">
                    <div class="genre-card">
                        <div class="genre-watermark">100K+</div>
                        <div class="genre-content">
                            <div class="genre-header">
                                <h3 class="genre-count genre-primary">100K+</h3>
                                <div class="genre-badges">
                                    <div class="badge badge-black">A</div>
                                    <div class="badge badge-orange">5+</div>
                                </div>
                            </div>
                            <div class="genre-books">
                                <img src="assets/img/books/romance-1.jpg" class="book-cover book-left" alt="Romance 1">
                                <img src="assets/img/books/romance-2.jpg" class="book-cover book-center" alt="Romance 2">
                                <img src="assets/img/books/romance-3.jpg" class="book-cover book-right" alt="Romance 3">
                            </div>
                            <div class="genre-footer">
                                <div class="genre-text">
                                    <p>#1 The best-selling genre on Daebook: <span class="text-secondary">Romance.</span></p>
                                </div>
                                <button class="genre-explore text-primary">Explore Now <i data-lucide="arrow-right"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="genre-card">
                        <div class="genre-watermark">60K+</div>
                        <div class="genre-content">
                            <div class="genre-header">
                                <h3 class="genre-count genre-secondary">60K+</h3>
                                <div class="genre-badges">
                                    <div class="badge badge-black">E</div>
                                    <div class="badge badge-blue">5+</div>
                                </div>
                            </div>
                            <div class="genre-books">
                                <img src="assets/img/books/thriller-1.jpg" class="book-cover book-left" alt="Thriller 1">
                                <img src="assets/img/books/thriller-2.jpg" class="book-cover book-center" alt="Thriller 2">
                                <img src="assets/img/books/thriller-3.jpg" class="book-cover book-right" alt="Thriller 3">
                            </div>
                            <div class="genre-footer">
                                <div class="genre-text">
                                    <p>#2 Best-selling genre on Daebook: <span class="text-primary">Thriller.</span></p>
                                </div>
                                <button class="genre-explore text-secondary">Explore Now <i data-lucide="arrow-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="reviews" class="testimonials">
            <div class="container">
                <div class="testimonials-header">
                    <span class="section-label-white">Customer Trust</span>
                    <h2 class="section-title-center">Building Trust Through Real <br><span class="text-gray">Customer Experiences</span></h2>
                </div>
                <div class="testimonials-grid">
                    <div class="avatars-column">
                        <img src="assets/img/testimonial/person1.jpg" class="avatar" alt="User 1">
                        <img src="assets/img/testimonial/person2.jpg" class="avatar avatar-active" alt="User 2">
                        <img src="assets/img/testimonial/person3.jpg" class="avatar" alt="User 3">
                    </div>
                    <div class="testimonial-content">
                        <div class="quote-mark">"</div>
                        <div class="testimonial-text">
                            <h3 class="testimonial-quote">"Grateful for the Books That Changed Our Access to Knowledge."</h3>
                            <p class="testimonial-description">We're grateful because your books broaden our knowledge, improve study access, and make learning experiences richer for everyone.</p>
                        </div>
                        <div class="testimonial-author">
                            <div>
                                <h4 class="author-name">Makayla Barron</h4>
                                <p class="author-role">Harvard University Student's</p>
                            </div>
                            <div class="rating">
                                <i data-lucide="star" class="star-filled"></i>
                                <i data-lucide="star" class="star-filled"></i>
                                <i data-lucide="star" class="star-filled"></i>
                                <i data-lucide="star" class="star-filled"></i>
                                <i data-lucide="star" class="star-filled"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stats-column">
                        <div class="stat-card">
                            <h4 class="stat-number">999K+</h4>
                            <p class="stat-label">People Chose Us</p>
                        </div>
                        <div class="stat-card">
                            <h4 class="stat-number stat-secondary">97%</h4>
                            <p class="stat-label">Readers Feel Satisfied</p>
                        </div>
                        <div class="stat-card">
                            <h4 class="stat-number stat-gray">86%</h4>
                            <p class="stat-label">Readers Come Back Again</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer" id="contact">
        <div class="container">
            <div class="footer-logo-mobile">
                <div class="logo-icon"><i data-lucide="book-open"></i></div>
                <span class="logo-text">Daebook</span>
            </div>
            <div class="footer-grid">
                <div class="footer-column">
                    <h4 class="footer-title">Contact Information</h4>
                    <p class="footer-text">Jl. Pangkal Perjuangan By Pass<br>Tanjungpura, Karawang, Jawa Barat</p>
                    <p class="footer-text">daebook.work@gmail.com</p>
                    <p class="footer-text">+1 (212) 555-0198</p>
                    <div class="social-links">
                        <a href="https://www.facebook.com/profile.php?id=100074331092431" class="social-link"><i data-lucide="facebook"></i></a>
                        <a href="https://www.linkedin.com/in/daebook-app-24b076394/" class="social-link"><i data-lucide="linkedin"></i></a>
                        <a href="https://x.com/Daebook01" class="social-link"><i data-lucide="X"></i></a>
                        <a href="https://www.instagram.com/daebook.id_/" class="social-link"><i data-lucide="instagram"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h4 class="footer-title">Daebook Pages</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#categories">Categories</a></li>
                        <li><a href="#reviews">Reviews</a></li>
                        <li><a href="#">Log In</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4 class="footer-title">Our Team</h4>
                    <ul class="footer-links">
                        <li>Fhazar R A - PM</li>
                        <li>Fadel R - QA</li>
                        <li>Hildan A T P - Frontend</li>
                        <li>Ferry N H - Backend</li>
                        <li>Daffa N Z - UI/UX</li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4 class="footer-title">Get Latest Update</h4>
                    <p class="footer-text">Sign up now to receive fresh updates, product releases, and news you would regret missing later.</p>
                    <!-- <form class="newsletter-form">
                        <input type="email" placeholder="Enter your email.." class="newsletter-input">
                        <button type="submit" class="newsletter-btn">Sign Up</button>
                    </form> -->
                    <div class="newsletter" style="margin-top:25px;">
                        <input type="email" placeholder="Enter your email">
                        <button class="btn-newsletter">Sign Up</button>
                    </div>
                </div>
            </div>
            <div class="footer-copyright">Copyright Daebook © 2025 All Rights Reserved.</div>
        </div>
    </footer>

    <!-- Script tetap sama seperti code 1 -->
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Mobile Menu Toggle
        const mobileToggle = document.getElementById('mobile-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');
        const closeIcon = document.getElementById('close-icon');

        mobileToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
            if (mobileMenu.classList.contains('active')) {
                menuIcon.style.display = 'none';
                closeIcon.style.display = 'block';
            } else {
                menuIcon.style.display = 'block';
                closeIcon.style.display = 'none';
            }
            lucide.createIcons();
        });

        // Close mobile menu on link click
        document.querySelectorAll('.mobile-link').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                menuIcon.style.display = 'block';
                closeIcon.style.display = 'none';
            });
        });

        // Navbar Scroll Effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in-view').forEach(el => {
            observer.observe(el);
        });

        // Simpel Search Handler redirect to all_book.php
        const searchForm = document.getElementById('search-form');
        searchForm.addEventListener('submit', (e) => {
            // Biarkan form melakukan submit jika method/action sudah diset di HTML, 
            // atau kita set manual di sini jika belum.
            // Kita akan update HTML formnya juga, tapi untuk safety kita bisa force redirect via JS
            // jika update HTML terpisah.
        });
    </script>
</body>

</html>