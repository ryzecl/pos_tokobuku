<?php
require_once 'config/database.php';
require_once 'models/Buku.php';

// sanitize and validate id
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: all_book.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$buku = new Buku($db);
$buku->id = $id;

if (!$buku->readOne()) {
    echo "Book not found.";
    exit;
}

// Fetch related products (same category) — use model helper to get up to 3 random books
// Falls back to readAll() if the query fails for any reason.
$related_stmt = $buku->getRelatedByKategori($buku->kategori_id, $buku->id, 3);
if (!$related_stmt) {
    $related_stmt = $buku->readAll();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="assets/img/logo/logo1.png">
    <title>Daebook - <?php echo htmlspecialchars($buku->nama_buku); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        .detail-section {
            padding-top: 8rem;
            padding-bottom: 4rem;
        }

        .detail-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            align-items: start;
        }

        @media (min-width: 768px) {
            .detail-container {
                grid-template-columns: 1fr 2fr;
            }
        }

        .book-cover-large {
            width: 100%;
            border-radius: 1rem;
            box-shadow: 0 0 30px rgba(139, 92, 246, 0.3);
            aspect-ratio: 2/3;
            background: #1f1f3a;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .book-cover-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .book-info h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: white;
            line-height: 1.2;
        }

        .book-meta {
            color: #9ca3af;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .book-price {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .book-actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
        }

        .divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 2rem 0;
        }

        .relevant-section {
            margin-top: 4rem;
        }
    </style>
</head>

<body>
    <nav id="navbar">
        <div class="container navbar-container">
            <div class="logo">
                <a href="index.php" style="text-decoration:none; display:flex; align-items:center; gap:0.5rem; color:white;">
                    <img src="assets/img/logo/logo.png" alt="Daebook Logo" style="height: 40px;">
                </a>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="index.php#about" class="nav-link">About</a>
                <a href="all_book.php" class="nav-link">All Books</a>
                <a href="index.php#reviews" class="nav-link">Reviews</a>
            </div>
        </div>
    </nav>

    <main class="container detail-section">
         <?php
            define('ALLOW_CHAT_ACCESS', true);
            include 'components/chat.php';
            ?>
        <div class="detail-container">
            <div class="book-cover-large">
                <?php
                    // Build a safe filename and prefer server-side file existence check
                    $kode_safe = '';
                    if (!empty($buku->kode_buku)) {
                        $kode_safe = basename($buku->kode_buku);
                    }
                    $coverPath = 'assets/produk/' . $kode_safe . '.jpg';
                    $defaultCover = 'assets/img/default.jpg';

                    if (!empty($kode_safe) && file_exists($coverPath)) {
                        echo '<img src="' . htmlspecialchars($coverPath) . '" alt="' . htmlspecialchars($buku->nama_buku) . '" loading="lazy">';
                    } else {
                        // Fallback to default image (server-side) so it shows even when file missing
                        echo '<img src="' . $defaultCover . '" alt="No cover available" loading="lazy">';
                    }
                ?>
            </div>
            <div class="book-info">
                <a href="all_book.php" style="text-decoration: none; color: #9ca3af; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <i data-lucide="arrow-left" size="16"></i> Back to Books
                </a>

                <h1><?php echo htmlspecialchars($buku->nama_buku); ?></h1>
                <!-- Category Name would be nice here, but it's in a join in readOne, let's hope it's populated -->
                <!-- readOne in Buku.php selects k.nama_kategori but doesn't map it to a property in the class. 
                     I should have checked that. The readOne method sets properties like this->kode_buku but not the joined category name. 
                     It returns true/false.
                     Let's check the logic of readOne again from previous turn...
                     It does: $this->kode_buku = $row['kode_buku']...
                     It does NOT seem to save 'nama_kategori' to a public property named nama_kategori. 
                     Wait, I can't easily access 'nama_kategori' unless I modify the model or just ignore it for now.
                     I will ignore it for this specific task to avoid modifying model unless strictly necessary, 
                     or I can re-query manualy if really needed. 
                     Actually, I can just show the category ID or omit it. Omitting for now to be safe.
                -->

                <div class="book-meta">
                    <span>Stock: <?php echo $buku->stok; ?> pcs</span> &bull;
                    <span>Code: <?php echo htmlspecialchars($buku->kode_buku); ?></span>
                </div>

                <div class="book-price">Rp <?php echo number_format($buku->harga_jual, 0, ',', '.'); ?></div>

                <div class="divider"></div>

                <h3>Description</h3>
                <p style="color: #d1d5db; line-height: 1.8; margin-top: 0.5rem;">
                    <?php echo nl2br(htmlspecialchars($buku->deskripsi ?? 'No description available.')); ?>
                </p>

                <div class="book-actions">
                    <!-- <button class="btn-primary">Buy Now</button> -->
                    <!-- Just a dummy button for visual completeness as per request for 'style' -->
                </div>
            </div>
        </div>

        <div class="relevant-section">
            <h2 class="section-title-center" style="margin-bottom: 2rem; font-size: 1.5rem;">Relevant to your search</h2>
            <div class="ai-results" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); display: grid; gap: 2rem;">
                <?php
// Display up to 3 related books (skip current) — result already limited by model
$count = 0;
while ($row = $related_stmt->fetch(PDO::FETCH_ASSOC)) {
    if ((int)$row['id'] === $id) continue;
    if ($count >= 3) break;

    $rel_id       = (int)$row['id'];
    $rel_kategori = htmlspecialchars($row['nama_kategori'] ?? 'General');
    $rel_nama     = htmlspecialchars($row['nama_buku'] ?? '');
    $rel_price    = number_format($row['harga_jual'] ?? 0, 0, ',', '.');
    $rel_desc     = htmlspecialchars(substr($row['deskripsi'] ?? '', 0, 100));
    $rel_kode     = htmlspecialchars($row['kode_buku'] ?? '');
?>
    <div class="book-card">
        <?php
            // sanitize related book code and prepare image path
            $rel_kode_raw = $row['kode_buku'] ?? '';
            $rel_kode_safe = htmlspecialchars(basename($rel_kode_raw));
            $rel_img_path = 'assets/produk/' . $rel_kode_safe . '.jpg';
        ?>
        <img src="<?php echo $rel_img_path; ?>" 
             onerror="this.onerror=null; this.src='assets/img/default.jpg';" 
             alt="<?php echo $rel_nama; ?>"
             loading="lazy"
             style="width: 100%; height: 200px; border-radius: 12px; object-fit: cover; box-shadow: 0 4px 10px rgba(139, 69, 19, 0.2);">
        <div class="book-genre"><?php echo $rel_kategori; ?></div>
        <h3 class="book-title"><?php echo $rel_nama; ?></h3>
        <p class="book-author">Rp <?php echo $rel_price; ?></p>
        <p class="book-description"><?php echo $rel_desc . (strlen($row['deskripsi'] ?? '') > 100 ? '...' : ''); ?></p>
        <a href="detail_buku.php?id=<?php echo $rel_id; ?>" class="book-details">
            Details <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
        </a>
    </div>
<?php
    $count++;
}
?>
            </div>
        </div>

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

    <script>
        lucide.createIcons();
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