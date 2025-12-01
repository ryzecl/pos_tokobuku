<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daebook - Discover Books Worth Your Time</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #A259FF;
            /* Warna ungu utama */
            --light-primary: #bb86fc;
            /* Gradasi lebih terang */
            --dark: #121212;
            --card-bg: rgba(162, 89, 255, 0.27);
            /* Latar belakang kartu */
            --stat-bg: rgba(162, 89, 255, 0.15);
            /* Latar belakang statistik */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', 'Poppins', 'Segoe UI', sans-serif;
            /* Menggunakan Inter sebagai font utama */
            background: #0f0f1a;
            color: #fff;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 25px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            background: rgba(0, 0, 0, 0.15);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            width: 150px;
            height: 32px;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 20px;
            /* jarak nav ke tombol login */
        }

        nav {
            display: flex;
            gap: 20px;
        }

        nav a {
            color: #ddd;
            margin: 0 15px;
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
            transition: color 0.3s;
        }

        nav a:hover,
        .aktip {
            color: var(--light-primary);
            border-bottom: 2px solid var(--light-primary);
        }

        .login-btn {
            background: var(--light-primary);
            color: #000;
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s;
        }

        .login-btn:hover {
            background: #9b59b6;
        }

        /* home */
        .home {
            height: 100vh;
            background: radial-gradient(circle at 80% 20%, rgba(162, 89, 255, 0.3), transparent 50%),
                #0a0a0a;
            display: flex;
            align-items: center;
            padding: 0 60px;
            position: relative;
            overflow: hidden;
            padding-top: 120px;
        }

        .home-content {
            max-width: 600px;
            z-index: 2;
        }

        .home h1 {
            font-size: 68px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
        }

        .home h1 span {
            color: var(--light-primary);
        }

        .home p {
            font-size: 19px;
            opacity: 0.9;
            margin-bottom: 40px;
        }

        .btns a {
            padding: 16px 36px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            margin-right: 15px;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--light-primary);
            color: #000;
        }

        .btn-primary:hover {
            background: #9b59b6;
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 2px solid var(--light-primary);
            color: var(--light-primary);
        }

        .btn-outline:hover {
            background: rgba(162, 89, 255, 0.1);
            transform: translateY(-2px);
        }

        .home-book {
            position: absolute;
            right: 80px;
            top: 50%;
            transform: translateY(-50%);
            width: 700px;
            animation: float 7s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(-50%) translateY(0);
            }

            50% {
                transform: translateY(-50%) translateY(-30px);
            }
        }

        /* Publishers */
        .publishers {
            padding: 100px 60px;
            text-align: left;
            background: rgba(10, 10, 10, 0.95);
        }

        .publishers p {
            color: #bbb;
            margin-bottom: 50px;
            font-size: 18px;
        }

        .pub-logos {
            display: flex;
            justify-content: center;
            gap: 80px;
            flex-wrap: wrap;
            background: #121212;
            padding: 30px;
            border-radius: 20px;
        }

        .pub-logos img {
            height: 55px;
            opacity: 0.5;
            transition: 0.4s;
        }

        .pub-logos img:hover {
            opacity: 1;
        }

        /* About Us */
        .about-us {
            padding: 120px 60px;
            background: #0f0f0f;
            display: flex;
            align-items: center;
            gap: 80px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .about-img {
            flex: 1;
            position: relative;
        }

        .about-img img {
            width: 100%;
            border-radius: 24px;
            object-fit: cover;
        }

        .about-img::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            width: 100%;
            height: 100%;
            border: 4px solid var(--light-primary);
            border-radius: 24px;
            z-index: -1;
        }

        .about-text {
            flex: 1;
            text-align: left;
        }

        .about-text h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .about-text p {
            font-size: 16px;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .about-signature {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 30px;
        }

        .about-signature p {
            font-size: 16px;
            font-weight: 600;
        }

        .about-signature img {
            width: 150px;
            height: 32px;
        }

        /* Genres */
        .genres {
            padding: 120px 60px;
            text-align: center;
        }

        .genres h2 {
            font-size: 42px;
            margin-bottom: 70px;
        }

        .genre-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            max-width: 1300px;
            margin: 0 auto;
        }

        .genre-card {
            background: var(--primary);
            border-radius: 24px;
            padding: 40px 30px;
            text-align: center;
            color: #000;
            transition: transform 0.3s;
        }

        .genre-card:hover {
            transform: translateY(-5px);
        }

        .genre-card img {
            width: 64px;
            height: 64px;
            margin-bottom: 25px;
        }

        .genre-card h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .big-card {
            background: var(--primary);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            font-size: 56px;
            font-weight: 800;
            color: #000;
            transition: transform 0.3s;
        }

        .big-card:hover {
            transform: translateY(-5px);
        }

        .big-card small {
            font-size: 18px;
            margin-top: 10px;
            font-weight: 500;
            text-align: center;
            color: #000;
        }

        /* Testimonial */
        .testimonial {
            padding: 140px 60px;
            text-align: center;
            background: #0f0f0f;
        }

        .testimonial h2 {
            font-size: 44px;
            margin-bottom: 100px;
        }

        .testi-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 100px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .testi-photos {
            display: flex;
            flex-direction: column;
            gap: 25px;
            background-size: cover;
            padding: 20px;
            border-radius: 20px;
        }

        .testi-photos img {
            width: 110px;
            height: 110px;
            border-radius: 20px;
            object-fit: cover;
            border: 4px solid var(--light-primary);
        }

        /* .testi-text {
            background: url("assets/img/testi-bg.png") no-repeat center center;
            background-size: cover;
            text-align: left;
            padding: 150px;
            border-radius: 20px;
            color: #fff;
            position: relative;
            z-index: 1;
        } */
        /* .testi-text {
            width: 1000px;
            height: 1000px;
            background: url("assets/img/testi-bg.png") no-repeat center center;
            background-size: cover;
            border-radius: 20px;
            position: relative;
            padding: 20px;
            color: #fff;
            text-align: left;
        } */
        .testi-text {
            max-width: 900px;
            /* width: 100%;
            height: 30px; */
            /* bebas lu atur */
            background: url("assets/img/testi-bg.png") no-repeat center center;
            background-size: contain;
            border-radius: 20px;
            padding: 20px;
            text-align: left;
        }



        /* .testi-text::before {
            content: "";
            position: absolute;
            inset: 0;

            z-index: -1;
            border-radius: 20px;
            backdrop-filter: blur(2px);
        } */

        .testi-text h3 {
            font-size: 28px;
            margin-bottom: 30px;
        }

        .testi-text p {
            font-size: 20px;
            line-height: 1.7;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .author {
            font-weight: 600;
            font-size: 18px;
        }

        .stats {
            text-align: center;
        }

        .stats>div {
            background: var(--stat-bg);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            font-size: 42px;
            font-weight: 700;
        }

        .stats small {
            display: block;
            font-size: 16px;
            margin-top: 8px;
            opacity: 0.8;
        }

        /* Footer */
        footer {
            background: linear-gradient(90deg, var(--primary), var(--primary));
            padding: 90px 60px 50px;
            color: #000;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 50px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .footer-col h4 {
            font-size: 20px;
            margin-bottom: 25px;
        }

        .footer-col a,
        .footer-col p {
            opacity: 0.9;
            margin-bottom: 10px;
            display: block;
            text-decoration: none;
        }

        .newsletter input {
            padding: 13px 10px;
            border: none;
            border-radius: 50px 0 0 50px;
            width: 70%;
            font-size: 16px;
        }

        .newsletter button {
            padding: 16px 20px;
            border: none;
            background: #000;
            color: #fff;
            border-radius: 0 50px 50px 0;
            cursor: pointer;
            font-weight: 600;
        }

        .copyright {
            text-align: center;
            margin-top: 70px;
            opacity: 0.9;
            font-size: 14px;
        }

        .social-icons {
            display: flex;
            gap: 15px;
        }

        .social-icons a {
            padding: 5px;
            transition: all 0.3s;
        }

        .social-icons a:hover {
            border-radius: 25%;
            background: #000;
        }

        .reveal {
            will-change: transform, opacity, filter;
            filter: blur(5px);
    opacity: 0;
    transform: translateY(50px);
    transition: opacity 0.8s ease, transform 0.8s ease;
}

/* aktif pas muncul */
.reveal.active {
    filter: blur(0);
    opacity: 1;
    transform: translateY(0);
}

/* delay animasi untuk tiap elemen di dalamnya */
.reveal > *:nth-child(1) { transition-delay: .1s; }
.reveal > *:nth-child(2) { transition-delay: .25s; }
.reveal > *:nth-child(3) { transition-delay: .4s; }
.reveal > *:nth-child(4) { transition-delay: .55s; }
.reveal > *:nth-child(5) { transition-delay: .7s; }
.reveal > *:nth-child(6) { transition-delay: .85s; }


    </style>
    <!-- Menambahkan font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tetap menyertakan Poppins untuk fallback -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <div class="logo">
            <img src="assets/img/logo/logo.png" alt="Daebook Logo">
        </div>
        <div class="menu">
            <nav>
                <a href="#home" class="aktip">Home</a>
                <a href="#about">About</a>
                <a href="#categories">Categories</a>
                <a href="#reviews">Reviews</a>
            </nav>
            <a href="login.php" class="login-btn">Log In</a>
        </div>
    </header>
    <section class="home reveal" id="home">
        <div class="home-content">
            <h1>Discover Books<br><span>Worth</span> Your <span>Time</span><br>Today.</h1>
            <p>Curated books to sharpen thinking, spark curiosity, and inspire meaningful personal growth.</p>
            <div class="btns">
                <a href="#about" class="btn-primary">Explore More Now!</a>
                <a href="#contact" class="btn-outline">Contact Us</a>
            </div>
        </div>
        <!-- <img src="assets/img/table-book.png" alt="Magic Book" class="home-book"> -->
        <video autoplay loop muted playsinline width="100%">
            <!-- <source src="assets/video/book.mp4" type="video/mp4"> -->
        </video>
    </section>
    <section class="publishers reveal">
        <p>Featured Publishers</p>
        <div class="pub-logos" id="about">
            <a href="https://www.gramedia.com/" target="_blank" rel="noopener noreferrer"><img src="assets/img/logo/gramedia-logo.png" alt="Gramedia"></a>
            <a href="https://mizan.com/" target="_blank" rel="noopener noreferrer"><img src="assets/img/logo/mizan-logo.png" alt="Mizan"></a>
            <a href="https://bintangpustaka.com/" target="_blank" rel="noopener noreferrer"><img src="assets/img/logo/bentang-pustaka-logo.png" alt="Bentang"></a>
            <a href="https://penerbitdeepublish.com/" target="_blank" rel="noopener noreferrer"><img src="assets/img/logo/deepublish-logo.png" alt="Deepublish"></a>
        </div>
    </section>
    <section class="about-us reveal reveal-left">
        <div class="about-img">
            <img src="assets/img/about-us.png" alt="About Us Image">
        </div>
        <div class="about-text">
            <h2>About Us</h2>
            <h3>Daebook A Smarter Way to Discover, Buy, and Experience Books</h3>
            <p>Daebook exists for readers who seek more than just finishing a book — they read to expand their thinking. We provide a space where quality titles meet curious minds, with a seamless buying experience and intelligent discovery features across genres. Our mission is simple: make knowledge accessible, ideas reachable, and growth a natural part of everyday life.</p>
            <div class="about-signature">
                <p>Thank You for Choosing Daebook &lt;3</p>
                <img src="assets/img/logo.png" alt="Daebook Logo">
            </div>
        </div>
    </section>
    <section class="genres reveal" id="categories">
        <p>Book Genres</p>
        <h2>Explore different book genres<br>and categories.</h2>

        <div class="genre-grid">
            <div class="genre-card">
                <img src="https://via.placeholder.com/64?text=Romance" alt="Romance">
                <h3>Romance</h3>
                <p>Heartwarming love stories filled with emotions, meaningful moments, and unforgettable journeys of the heart.</p>
            </div>
            <div class="genre-card">
                <img src="https://via.placeholder.com/64?text=Fantasy" alt="Fantasy">
                <h3>Fantasy</h3>
                <p>Magical worlds with mythical creatures, epic quests, and adventures beyond imagination and reality.</p>
            </div>
            <div class="genre-card">
                <img src="https://via.placeholder.com/64?text=Mystery" alt="Mystery">
                <h3>Mystery</h3>
                <p>Suspenseful stories full of secrets, hidden clues, and unexpected twists at every turn.</p>
            </div>
            <div class="genre-card">
                <img src="https://via.placeholder.com/64?text=Thriller" alt="Thriller">
                <h3>Thriller</h3>
                <p>Fast-paced narratives with intense action, dark themes, and adrenaline-filled suspense throughout.</p>
            </div>
            <div class="genre-card">
                <img src="https://via.placeholder.com/64?text=Biography" alt="Biography">
                <h3>Biography</h3>
                <p>Inspiring life stories of remarkable people, their struggles, achievements, and unforgettable journeys.</p>
            </div>
            <div class="big-card">
                12+<br>
                <small>Book Genres to Explore<br>Find Your Next Great Read!</small>
            </div>
        </div>
    </section>
    <section class="testimonial reveal" id="reviews">
        <h2>Building Trust Through Real<br>Customer Experiences</h2>
        <div class="testi-wrapper">
            <div class="testi-photos">
                <img src="https://i.ibb.co/4pXv7gK/person1.png" alt="">
                <img src="https://i.ibb.co/5YZnY7h/person2.png" alt="">
                <img src="https://i.ibb.co/6P7Y8kN/person3.png" alt="">
            </div>

            <div class="testi-text">
                <h3>Grateful for the Books That<br>Changed Our Access to Knowledge.</h3><br>
                <p>“We’re grateful because your books broaden our knowledge, improve study access, and make learning experiences richer for everyone.”</p><br>
                <div class="author">Makayla Barron<br><small>Student at Horizon University Indonesia</small></div>
                <div style="margin-top:20px; color:#bb86fc;">★★★★★</div>
            </div>

            <div class="stats">
                <div>320+<small>People Choose Us</small></div>
                <div>97%<small>Readers Feel Satisfied</small></div>
                <div>86%<small>Readers Come Back Again</small></div>
            </div>
        </div>
    </section>
    <footer class="reveal">
        <div class="footer-grid" id="contact">
            <div class="footer-col">
                <h4>Contact Information</h4>
                <p>Jl. Pangkal Perjuangan By Pass<br>Tanjungpura, Karawang, Jawa Barat</p>
                <p>daebook.work@gmail.com</p>
                <p>+1 (212) 555-0198</p>
                <div class="social-icons">
                    <a href="#"><img src="assets/img/contact/facebook.png" alt="facebook"></a>
                    <a href="#"><img src="assets/img/contact/linkedin.png" alt="linkedin"></a>
                    <a href="#"><img src="assets/img/contact/x.png" alt="twitter"></a>
                    <a href="#"><img src="assets/img/contact/instagram.png" alt="instagram"></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Daebook Pages</h4>
                <a href="#home">Home</a>
                <a href="#about">About</a>
                <a href="#categories">Categories</a>
                <a href="#reviews">Reviews</a>
                <a href="login.php">Log In</a>
            </div>
            <div class="footer-col">
                <h4>Our Team</h4>
                <p>Fhazar R A - PM</p>
                <p>Fadel R - QA</p>
                <p>Hildan A T P - Frontend</p>
                <p>Ferry N H - Backend</p>
                <p>Daffa N Z - UI/UX</p>
            </div>
            <div class="footer-col">
                <h4>Get Latest Update</h4>
                <p>Sign up now to receive fresh updates, product releases, and news you would regret missing later.</p>
                <div class="newsletter" style="margin-top:25px;">
                    <input type="email" placeholder="Enter your email">
                    <button>Sign Up</button>
                </div>
            </div>
        </div>
        <div class="copyright">
            Copyright © Daebook © 2025 All Rights Reserved.
        </div>
    </footer>
</body>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    const reveals = document.querySelectorAll('.reveal');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target); // animasi sekali aja = hemat!
            }
        });
    }, { threshold: 0.15 });

    reveals.forEach(reveal => {
        observer.observe(reveal);
    });
});

</script>

</html>