<?php
class Penjualan
{
    private $conn;
    private $table_name = "penjualan";

    public $id;
    public $no_transaksi;
    public $user_id;
    public $customer_id;
    public $diskon;
    public $ppn;
    public $total_harga;
    public $total_bayar;
    public $kembalian;
    public $note;
    public $tanggal_penjualan;
    public $metode_pembayaran;
    public $token_public;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // CREATE
    public function create()
    {
        try {
            $this->token_public = bin2hex(random_bytes(32));

            $query = "INSERT INTO " . $this->table_name . "
                (no_transaksi, user_id, customer_id, diskon, ppn, total_harga, total_bayar, kembalian, note, metode_pembayaran, token_public)
                VALUES (:no_transaksi, :user_id, :customer_id, :diskon, :ppn, :total_harga, :total_bayar, :kembalian, :note, :metode_pembayaran, :token_public)";

            $stmt = $this->conn->prepare($query);

            $this->no_transaksi = htmlspecialchars(strip_tags($this->no_transaksi ?? ''));
            $this->user_id = !empty($this->user_id) ? (int)$this->user_id : null;
            $this->customer_id = !empty($this->customer_id) ? (int)$this->customer_id : null;
            $this->diskon = floatval($this->diskon ?? 0);
            $this->ppn = floatval($this->ppn ?? 0);
            $this->total_harga = floatval($this->total_harga ?? 0);
            $this->total_bayar = floatval($this->total_bayar ?? 0);
            $this->kembalian = floatval($this->kembalian ?? 0);
            $this->note = !empty($this->note) ? htmlspecialchars(strip_tags($this->note)) : null;
            $this->metode_pembayaran = htmlspecialchars(strip_tags($this->metode_pembayaran ?? 'CASH'));

            $stmt->bindValue(':no_transaksi', $this->no_transaksi);
            $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
            $stmt->bindValue(':customer_id', $this->customer_id, PDO::PARAM_INT);
            $stmt->bindValue(':diskon', $this->diskon);
            $stmt->bindValue(':ppn', $this->ppn);
            $stmt->bindValue(':total_harga', $this->total_harga);
            $stmt->bindValue(':total_bayar', $this->total_bayar);
            $stmt->bindValue(':kembalian', $this->kembalian);
            $stmt->bindValue(':note', $this->note);
            $stmt->bindValue(':metode_pembayaran', $this->metode_pembayaran);
            $stmt->bindValue(':token_public', $this->token_public);

            if ($stmt->execute()) {
                $this->id = (int)$this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Penjualan::create error: " . $e->getMessage());
            return false;
        }
    }

    // Generate nomor transaksi
    public function generateNoTransaksi()
    {
        $fallback = 'TRX' . date('Ymd') . '0001';
        try {
            if (!$this->conn) return $fallback;

            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE DATE(tanggal_penjualan) = CURDATE()");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = ($row && isset($row['count'])) ? (int)$row['count'] : 0;
            return 'TRX' . date('Ymd') . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            return $fallback;
        }
    }

    // READ ONE
    public function readOne()
    {
        $query = "SELECT p.*, u.nama_lengkap as kasir
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE p.id = :id
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = (int)$row['id'];
            $this->no_transaksi = $row['no_transaksi'];
            $this->user_id = $row['user_id'];
            $this->customer_id = $row['customer_id'];
            $this->diskon = $row['diskon'];
            $this->ppn = $row['ppn'];
            $this->total_harga = $row['total_harga'];
            $this->total_bayar = $row['total_bayar'];
            $this->kembalian = $row['kembalian'];
            $this->note = $row['note'];
            $this->tanggal_penjualan = $row['tanggal_penjualan'];
            $this->metode_pembayaran = $row['metode_pembayaran'];
            $this->token_public = $row['token_public'] ?? null;
            return true;
        }
        return false;
    }

    public function readOneByToken()
    {
        $query = "SELECT p.*, u.nama_lengkap as kasir
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE p.token_public = :token
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':token', $this->token_public);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = (int)$row['id'];
            $this->no_transaksi = $row['no_transaksi'];
            $this->user_id = $row['user_id'];
            $this->customer_id = $row['customer_id'];
            $this->diskon = $row['diskon'];
            $this->ppn = $row['ppn'];
            $this->total_harga = $row['total_harga'];
            $this->total_bayar = $row['total_bayar'];
            $this->kembalian = $row['kembalian'];
            $this->note = $row['note'];
            $this->tanggal_penjualan = $row['tanggal_penjualan'];
            $this->metode_pembayaran = $row['metode_pembayaran'];
            $this->token_public = $row['token_public'];
            return true;
        }
        return false;
    }

    public function readAll()
    {
        $query = "SELECT p.*, u.nama_lengkap as kasir
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  ORDER BY p.tanggal_penjualan DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getDetailPenjualan($penjualan_id)
    {
        $query = "SELECT dp.*, b.nama_buku, b.kode_buku
                  FROM detail_penjualan dp
                  LEFT JOIN buku b ON dp.buku_id = b.id
                  WHERE dp.penjualan_id = :penjualan_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':penjualan_id', $penjualan_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Statistik
    public function getTotalPenjualanHari()
    {
        $query = "SELECT COALESCE(SUM(total_harga), 0) as total 
                  FROM " . $this->table_name . "
                  WHERE DATE(tanggal_penjualan) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($row['total'] ?? 0);
    }

    public function getTotalPenjualanBulan()
    {
        $query = "SELECT COALESCE(SUM(total_harga), 0) as total 
                  FROM " . $this->table_name . "
                  WHERE MONTH(tanggal_penjualan) = MONTH(CURDATE())
                  AND YEAR(tanggal_penjualan) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($row['total'] ?? 0);
    }

    public function getTotalTransaksiHari()
    {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . "
                  WHERE DATE(tanggal_penjualan) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function getLaporanPenjualan($start_date, $end_date)
    {
        $query = "SELECT p.*, u.nama_lengkap as kasir
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  WHERE DATE(p.tanggal_penjualan) BETWEEN :start_date AND :end_date
                  ORDER BY p.tanggal_penjualan DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':start_date', $start_date);
        $stmt->bindValue(':end_date', $end_date);
        $stmt->execute();
        return $stmt;
    }

    public function readRecent($limit = 10)
    {
        $query = "SELECT p.*, u.nama_lengkap as kasir
                  FROM " . $this->table_name . " p
                  LEFT JOIN users u ON p.user_id = u.id
                  ORDER BY p.tanggal_penjualan DESC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
}
?>