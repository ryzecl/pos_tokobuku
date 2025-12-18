<?php
require_once 'config/database.php';
require_once 'models/Buku.php';

$database = new Database();
$db = $database->getConnection();
$buku = new Buku($db);

$search_query = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($search_query)) {
    $stmt = $buku->search($search_query);
    $title_display = 'Search Results for "' . htmlspecialchars($search_query) . '"';
} else {
    $stmt = $buku->readAll();
    $title_display = 'All Books Collection';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daebook - <?php echo $title_display; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        .page-header {
            padding-top: 8rem;
            padding-bottom: 2rem;
            text-align: center;
        }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 0;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <nav id="navbar">
        <div class="container navbar-container">
            <div class="logo">
                <a href="index.php" style="text-decoration:none; display:flex; align-items:center; gap:0.5rem; color:white;">
                    <!-- <div class="logo-icon"><i data-lucide="book-open"></i></div> -->
                    <img src="assets/img/logo/logo.png" alt="Daebook Logo" style="height: 40px;">
                    <!-- <span class="logo-text">Daebook</span> -->
                </a>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="index.php#about" class="nav-link">About</a>
                <a href="all_book.php" class="nav-link" style="color:white;">All Books</a>
                <a href="index.php#reviews" class="nav-link">Reviews</a>
                <div class="nav-cta">
                    <a href="login.php" class="btn-primary" style="text-decoration: none;">Log In</a>
                </div>
            </div>
            <!-- Mobile Toggle reused from index.php logic if needed, simplifed here -->
        </div>
    </nav>

    <main class="container">
         <?php
            define('ALLOW_CHAT_ACCESS', true);
            include 'components/chat.php';
            ?>
        <div class="page-header">
            <h1 class="section-title-center"><?php echo $title_display; ?></h1>
            <div class="search-wrapper" style="max-width: 600px; margin: 0 auto;">
                <div class="search-glow"></div>
                <form class="search-form" action="all_book.php" method="GET">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search books..." class="search-input">
                    <button type="submit" class="search-btn">
                        <i data-lucide="search"></i>
                    </button>
                </form>
            </div>
        </div>

        <?php if ($stmt && $stmt->rowCount() > 0): ?>
            <div class="books-grid">
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="book-card">
                        <div class="book-genre"><?php echo htmlspecialchars($row['nama_kategori'] ?? 'General'); ?></div>
                        <!-- Placeholder image logic since DB might not have valid paths or actual files -->
                        <div style="height: 420px; background: #1f1f3a; margin-bottom: 1rem; border-radius: 0.5rem; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($row['kode_buku'])): ?>
                                <img src="assets/produk/<?php echo htmlspecialchars($row['kode_buku']); ?>.jpg" alt="Cover" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i data-lucide="book" size="48" color="#6b7280"></i>
                            <?php endif; ?>
                        </div>

                        <h3 class="book-title"><?php echo htmlspecialchars($row['nama_buku']); ?></h3>
                        <p class="book-author">Harga: Rp <?php echo number_format($row['harga_jual'], 0, ',', '.'); ?></p>
                        <p class="book-description">
                            <?php
                            $desc = $row['deskripsi'] ?? 'No description available.';
                            echo htmlspecialchars(substr($desc, 0, 100)) . (strlen($desc) > 100 ? '...' : '');
                            ?>
                        </p>
                        <a href="detail_buku.php?id=<?php echo (int)$row['id']; ?>" class="book-details" style="text-decoration: none;">
                            Details <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i data-lucide="book-x" size="64" style="margin-bottom: 1rem;"></i>
                <h2>No books found.</h2>
                <p>Try searching for something else.</p>
            </div>
        <?php endif; ?>
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
                        <a href="#" class="social-link"><i data-lucide="facebook"></i></a>
                        <a href="#" class="social-link"><i data-lucide="linkedin"></i></a>
                        <a href="#" class="social-link"><i data-lucide="twitter"></i></a>
                        <a href="#" class="social-link"><i data-lucide="instagram"></i></a>
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
                        <button>Sign Up</button>
                    </div>
                </div>
            </div>
            <div class="footer-copyright">Copyright Daebook © 2025 All Rights Reserved.</div>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>

</html>