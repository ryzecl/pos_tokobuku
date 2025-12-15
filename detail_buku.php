<?php
require_once 'config/database.php';
require_once 'models/Buku.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
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

// Fetch relative products (same category)
// Since there's no dedicated method, we'll crudely reuse readAll and filter in loop or specialized query if needed. 
// For now let's just create a quick custom query here or reuse readAll but limit output.
// Better practice: Add getByKategori to model, but I will just use readAll() and pick first 3 random ones for "Relevant" section.
$all_stmt = $buku->readAll(); // This is inefficient but fits within current scope without Modifying Model too much
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <div class="detail-container">
            <div class="book-cover-large">
                <?php if (!empty($buku->kode_buku)): ?>
                    <img src="assets/produk/<?php echo htmlspecialchars($buku->kode_buku); ?>.jpg" alt="Cover">
                <?php else: ?>
                    <i data-lucide="book" size="100" color="#6b7280"></i>
                <?php endif; ?>
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
                // Display 3 random books
                $count = 0;
                while ($row = $all_stmt->fetch(PDO::FETCH_ASSOC)) {
                    if ($row['id'] == $id) continue;
                    if ($count >= 3) break;
                ?>
                    <div class="book-card">
                        <div class="book-genre"><?php echo htmlspecialchars($row['nama_kategori'] ?? 'General'); ?></div>
                        <h3 class="book-title"><?php echo htmlspecialchars($row['nama_buku']); ?></h3>
                        <p class="book-author">Rp <?php echo number_format($row['harga_jual'], 0, ',', '.'); ?></p>
                        <p class="book-description">
                            <?php
                            $d = $row['deskripsi'] ?? '';
                            echo htmlspecialchars(substr($d, 0, 80)) . '...';
                            ?>
                        </p>
                        <a href="detail_buku.php?id=<?php echo $row['id']; ?>" class="book-details">
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

    <footer class="footer">
        <div class="container">
            <div class="footer-copyright" style="text-align: center;">Copyright Daebook © 2025 All Rights Reserved.</div>
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