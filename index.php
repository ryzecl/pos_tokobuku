<?php
require_once 'config/config.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Sistem Point of Sale Modern</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
            background: #fdf4e3;
            /* krem gading lembut (ganti putih polos) */
        }

        /* Navigation – tetap pakai gradient cokelat yang kamu pilih */
        .navbar {
            background: linear-gradient(135deg, #8B4513 0%, #A0522D 50%, #D2691E 100%);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        /* Tombol login – warna ungu diganti cokelat caramel */
        .btn-login {
            background: white;
            color: #A0522D;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(160, 82, 45, 0.4);
        }

        /* Hero Section – ganti ungu jadi cokelat sama seperti navbar */
        .hero {
            background:
                linear-gradient(rgba(139, 69, 19, 0.70), rgba(160, 82, 45, 0.80)),
                url('assets/roty-bg.jpg') center/cover no-repeat;

            color: white;
            padding: 150px 2rem 100px;
            text-align: center;
            margin-top: 60px;
            background-attachment: fixed;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease 0.4s both;
        }

        .btn-primary {
            background: white;
            color: #A0522D;
            /* ganti #667eea */
            padding: 1rem 2.5rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: transform 0.3s, box-shadow 0.3s;
            display: inline-block;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: transparent;
            color: white;
            padding: 1rem 2.5rem;
            border: 2px solid white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-secondary:hover {
            background: white;
            color: #A0522D;
            /* ganti #667eea */
            transform: translateY(-3px);
        }

        /* Features Section – background hitam pekat diganti cokelat tua */
        .features {
            padding: 80px 2rem;
            background: #2c1b18;
            /* cokelat sangat gelap (lebih hangat dari hitam) */
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #D2691E;
            /* aksen cokelat susu */
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #D2691E;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .feature-card p {
            color: #666;
            line-height: 1.8;
        }

        /* Stats Section – gradient ungu diganti cokelat */
        .stats {
            background: linear-gradient(135deg, #8B4513 0%, #A0522D 50%, #D2691E 100%);
            color: white;
            padding: 60px 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .stat-item p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* CTA Section */
        .cta {
            padding: 80px 2rem;
            background: #fdf4e3;
            text-align: center;
        }

        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #2c1b18;
        }

        .cta p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
        }

        /* Footer */
        .footer {
            background: #2c1b18;
            /* cokelat pekat */
            color: white;
            padding: 40px 2rem;
            text-align: center;
        }

        .footer p {
            opacity: 0.8;
        }

        /* Animations – tetap persis */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive – tetap persis */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo"><?php echo APP_NAME; ?></a>
            <div class="nav-links">
                <a href="#features">Farian</a>
                <a href="#about">Tentang</a>
                <a href="login.php" class="btn-login" style="background: white !important; color: #000 !important;">Masuk</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Kelola RootyMart Anda dengan Mudah</h1>
            <p>Sistem Point of Sale modern yang membantu Anda mengelola penjualan, stok, dan laporan dengan efisien</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn-primary">Mulai Sekarang</a>
                <a href="#features" class="btn-secondary">Pelajari Lebih Lanjut</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">Jenis Jenis Produc</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon" style="width:100px;height:100px;margin:0 auto 1rem;overflow:hidden;border-radius:18px;background:#fff8f0;padding:8px;box-shadow:0 4px 15px rgba(139,69,19,0.15);">
                        <img src="assets/buku-manis.jpg" alt="POS" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.src='assets/produk/default.jpg'">
                    </div>
                    <h3>buku manis</h3>
                    <p>buku dengan kandungan gula dan lemak yang lebih tinggi, seringkali diberi isian (seperti cokelat, keju, selai) atau topping. Teksturnya lembut, empuk, dan rasanya dominan manis</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="width:100px;height:100px;margin:0 auto 1rem;overflow:hidden;border-radius:18px;background:#fff8f0;padding:8px;box-shadow:0 4px 15px rgba(139,69,19,0.15);">
                        <img src="assets/buku-tawar.jpg" alt="Laporan" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.src='assets/produk/default.jpg'">
                    </div>
                    <h3>buku Tawar</h3>
                    <p>buku dasar yang dibuat dari adonan sederhana (tepung, air, ragi, sedikit garam/gula). Tidak memiliki isian/rasa yang kuat dan biasanya digunakan sebagai pendamping (misalnya untuk sarapan dengan selai, mentega, atau diolah menjadi sandwich).</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="width:100px;height:100px;margin:0 auto 1rem;overflow:hidden;border-radius:18px;background:#fff8f0;padding:8px;box-shadow:0 4px 15px rgba(139,69,19,0.15);">
                        <img src="assets/pastry.jpg" alt="Stok" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.src='assets/produk/default.jpg'">
                    </div>
                    <h3>Pastry</h3>
                    <p>Produk yang dibuat dari adonan berlapis-lapis kaya lemak (seperti mentega atau margarin), yang menghasilkan tekstur renyah, garing, dan ringan (flaky). Contoh umum: croissant, danish, puff pastry, dan pie.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="width:100px;height:100px;margin:0 auto 1rem;overflow:hidden;border-radius:18px;background:#fff8f0;padding:8px;box-shadow:0 4px 15px rgba(139,69,19,0.15);">
                        <img src="assets/cake.jpg" alt="Multi User" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.src='assets/produk/default.jpg'">
                    </div>
                    <h3>Cake</h3>
                    <p>Produk panggang yang biasanya lebih manis dan kaya lemak/telur, memiliki tekstur yang lembut, halus, dan moist (lembab). Sering dihias dengan icing, frosting, atau dekorasi lain untuk acara khusus.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="width:100px;height:100px;margin:0 auto 1rem;overflow:hidden;border-radius:18px;background:#fff8f0;padding:8px;box-shadow:0 4px 15px rgba(139,69,19,0.15);">
                        <img src="images/icons/discount.png" alt="Diskon" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.src='assets/produk/default.jpg'">
                    </div>
                    <h3>Biscotti</h3>
                    <p>Biskuit Italia yang dipanggang dua kali (bi-scotti), menjadikannya sangat kering, renyah, dan keras. Sempurna untuk dicelupkan ke dalam kopi, teh, atau minuman lain. Biasanya berbentuk memanjang dan mengandung kacang-kacangan.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon" style="width:100px;height:100px;margin:0 auto 1rem;overflow:hidden;border-radius:18px;background:#fff8f0;padding:8px;box-shadow:0 4px 15px rgba(139,69,19,0.15);">
                        <img src="images/icons/payment.png" alt="Pembayaran" style="width:100%;height:100%;object-fit:cover;border-radius:12px;" onerror="this.src='assets/produk/default.jpg'">
                    </div>
                    <h3>Muffin</h3>
                    <p>Kue porsi tunggal (individual) yang dibuat dengan metode cepat. Teksturnya lebih padat, lebih kasar, dan tidak selembut cake. Sering kali diisi dengan buah, cokelat, atau kacang, dan memiliki 'kubah' yang khas di atasnya.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>100%</h3>
                    <p>Akurat</p>
                </div>
                <div class="stat-item">
                    <h3>24/7</h3>
                    <p>Tersedia</p>
                </div>
                <div class="stat-item">
                    <h3>3</h3>
                    <p>Level Akses</p>
                </div>
                <div class="stat-item">
                    <h3>∞</h3>
                    <p>Transaksi</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta" id="about">
        <div class="container">
            <h2>Siap Memulai?</h2>
            <p>Bergabunglah dengan sistem POS modern yang akan membantu bisnis RootyMart Anda berkembang</p>
            <a href="login.php" class="btn-primary">Login Sekarang</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            <p style="margin-top: 10px; font-size: 0.9rem;">Dibuat dengan ❤️ untuk kemudahan bisnis Anda</p>
        </div>
    </footer>

    <script>
        // Smooth scroll
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
    </script>
</body>

</html>