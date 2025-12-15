<?php
class Buku
{
    private $conn;
    private $table_name = "buku";

    // fitur deteksi kolom opsional
    private $has_diskon = false;
    private $has_foto_cover = false;

    public $id;
    public $kode_buku;
    public $nama_buku;
    public $kategori_id;
    public $satuan;
    public $harga_beli;
    public $harga_jual;
    public $diskon;
    public $stok;
    public $stok_minimum;
    public $tanggal_expired;
    public $deskripsi;
    public $foto_cover;

    public function __construct($db)
    {
        $this->conn = $db;

        // detect optional columns (tidak fatal kalau gagal)
        try {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM " . $this->table_name);
            $stmt->execute();
            $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->has_diskon = in_array('diskon', $cols);
            $this->has_foto_cover = in_array('foto_cover', $cols);
        } catch (Exception $e) {
            $this->has_diskon = false;
            $this->has_foto_cover = false;
        }
    }

    public function kodeBukuExists($kode_buku, $excludeId = null)
    {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE kode_buku = :kode_buku";
        if ($excludeId) $query .= " AND id != :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':kode_buku', (string)$kode_buku, PDO::PARAM_STR);
        if ($excludeId) $stmt->bindValue(':id', (int)$excludeId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['count'] ?? 0) > 0;
    }

    public function create()
    {
        try {
            if ($this->kodeBukuExists($this->kode_buku)) {
                return false;
            }

            $fields = [
                'kode_buku' => ':kode_buku',
                'nama_buku' => ':nama_buku',
                'kategori_id' => ':kategori_id',
                'satuan' => ':satuan',
                'harga_beli' => ':harga_beli',
                'harga_jual' => ':harga_jual',
            ];

            if ($this->has_diskon) $fields['diskon'] = ':diskon';
            if ($this->has_foto_cover) $fields['foto_cover'] = ':foto_cover';

            $fields['stok'] = ':stok';
            $fields['stok_minimum'] = ':stok_minimum';
            $fields['tanggal_expired'] = ':tanggal_expired';
            $fields['deskripsi'] = ':deskripsi';

            $parts = [];
            foreach ($fields as $k => $v) $parts[] = "$k = $v";
            $query = "INSERT INTO " . $this->table_name . " SET " . implode(', ', $parts);

            $stmt = $this->conn->prepare($query);

            // sanitize / cast
            $kode = trim($this->kode_buku);
            $nama = trim($this->nama_buku);
            $kategori = (int)$this->kategori_id;
            $satuan = trim($this->satuan);
            $hb = (float)$this->harga_beli;
            $hj = (float)$this->harga_jual;
            $diskon = (float)($this->diskon ?? 0);
            $stok = (int)$this->stok;
            $stok_min = (int)$this->stok_minimum;
            $exp = $this->tanggal_expired ?: null;
            $desc = trim($this->deskripsi) ?: null;
            $foto = $this->foto_cover ?? null;

            $stmt->bindValue(':kode_buku', $kode, PDO::PARAM_STR);
            $stmt->bindValue(':nama_buku', $nama, PDO::PARAM_STR);
            $stmt->bindValue(':kategori_id', $kategori, PDO::PARAM_INT);
            $stmt->bindValue(':satuan', $satuan, PDO::PARAM_STR);
            $stmt->bindValue(':harga_beli', $hb);
            $stmt->bindValue(':harga_jual', $hj);

            if ($this->has_diskon) $stmt->bindValue(':diskon', $diskon);
            if ($this->has_foto_cover) $stmt->bindValue(':foto_cover', $foto, PDO::PARAM_STR);

            $stmt->bindValue(':stok', $stok, PDO::PARAM_INT);
            $stmt->bindValue(':stok_minimum', $stok_min, PDO::PARAM_INT);
            $stmt->bindValue(':tanggal_expired', $exp);
            $stmt->bindValue(':deskripsi', $desc);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Buku::create error: " . $e->getMessage());
            return false;
        }
    }

    public function readAll()
    {
        try {
            $query = "SELECT o.*, k.nama_kategori 
                      FROM " . $this->table_name . " o
                      LEFT JOIN kategori_buku k ON o.kategori_id = k.id
                      ORDER BY o.nama_buku ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Buku::readAll error: " . $e->getMessage());
            return null;
        }
    }

    public function readOne()
    {
        try {
            $query = "SELECT o.*, k.nama_kategori 
                      FROM " . $this->table_name . " o
                      LEFT JOIN kategori_buku k ON o.kategori_id = k.id
                      WHERE o.id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', (int)$this->id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return false;

            $this->kode_buku = $row['kode_buku'] ?? null;
            $this->nama_buku = $row['nama_buku'] ?? null;
            $this->kategori_id = $row['kategori_id'] ?? null;
            $this->satuan = $row['satuan'] ?? null;
            $this->harga_beli = $row['harga_beli'] ?? 0;
            $this->harga_jual = $row['harga_jual'] ?? 0;
            $this->diskon = $row['diskon'] ?? 0;
            $this->stok = $row['stok'] ?? 0;
            $this->stok_minimum = $row['stok_minimum'] ?? 0;
            $this->tanggal_expired = $row['tanggal_expired'] ?? null;
            $this->deskripsi = $row['deskripsi'] ?? null;
            $this->foto_cover = $row['foto_cover'] ?? null;
            return true;
        } catch (PDOException $e) {
            error_log("Buku::readOne error: " . $e->getMessage());
            return false;
        }
    }

    public function update()
    {
        try {
            if ($this->kodeBukuExists($this->kode_buku, $this->id)) {
                return false;
            }

            $fields = [
                'kode_buku' => ':kode_buku',
                'nama_buku' => ':nama_buku',
                'kategori_id' => ':kategori_id',
                'satuan' => ':satuan',
                'harga_beli' => ':harga_beli',
                'harga_jual' => ':harga_jual',
            ];

            if ($this->has_diskon) $fields['diskon'] = ':diskon';
            if ($this->has_foto_cover && !empty($this->foto_cover)) $fields['foto_cover'] = ':foto_cover';

            $fields['stok'] = ':stok';
            $fields['stok_minimum'] = ':stok_minimum';
            $fields['tanggal_expired'] = ':tanggal_expired';
            $fields['deskripsi'] = ':deskripsi';

            $parts = [];
            foreach ($fields as $k => $v) $parts[] = "$k = $v";
            $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $parts) . " WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            // sanitize / cast
            $kode = trim($this->kode_buku);
            $nama = trim($this->nama_buku);
            $kategori = (int)$this->kategori_id;
            $satuan = trim($this->satuan);
            $hb = (float)$this->harga_beli;
            $hj = (float)$this->harga_jual;
            $diskon = (float)($this->diskon ?? 0);
            $stok = (int)$this->stok;
            $stok_min = (int)$this->stok_minimum;
            $exp = $this->tanggal_expired ?: null;
            $desc = trim($this->deskripsi) ?: null;
            $foto = $this->foto_cover ?? null;
            $id = (int)$this->id;

            $stmt->bindValue(':kode_buku', $kode, PDO::PARAM_STR);
            $stmt->bindValue(':nama_buku', $nama, PDO::PARAM_STR);
            $stmt->bindValue(':kategori_id', $kategori, PDO::PARAM_INT);
            $stmt->bindValue(':satuan', $satuan, PDO::PARAM_STR);
            $stmt->bindValue(':harga_beli', $hb);
            $stmt->bindValue(':harga_jual', $hj);

            if ($this->has_diskon) $stmt->bindValue(':diskon', $diskon);
            if ($this->has_foto_cover && !empty($this->foto_cover)) $stmt->bindValue(':foto_cover', $foto, PDO::PARAM_STR);

            $stmt->bindValue(':stok', $stok, PDO::PARAM_INT);
            $stmt->bindValue(':stok_minimum', $stok_min, PDO::PARAM_INT);
            $stmt->bindValue(':tanggal_expired', $exp);
            $stmt->bindValue(':deskripsi', $desc);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Buku::update error: " . $e->getMessage());
            return false;
        }
    }

    public function delete()
    {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', (int)$this->id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Buku::delete error: " . $e->getMessage());
            return false;
        }
    }

    public function updateStok($buku_id, $jumlah)
    {
        try {
            $query = "UPDATE " . $this->table_name . " SET stok = stok + :jumlah WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':jumlah', (int)$jumlah, PDO::PARAM_INT);
            $stmt->bindValue(':id', (int)$buku_id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Buku::updateStok error: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalBuku()
    {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM " . $this->table_name);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Buku::getTotalBuku error: " . $e->getMessage());
            return 0;
        }
    }

    public function getBukuExpired()
    {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE tanggal_expired IS NOT NULL AND tanggal_expired <= CURDATE()");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Buku::getBukuExpired error: " . $e->getMessage());
            return 0;
        }
    }

    public function getStokMinimum()
    {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE stok <= stok_minimum");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Buku::getStokMinimum error: " . $e->getMessage());
            return 0;
        }
    }

    public function search($keyword)
    {
        try {
            $kw = '%' . trim((string)$keyword) . '%';
            $query = "SELECT o.*, k.nama_kategori 
                      FROM " . $this->table_name . " o
                      LEFT JOIN kategori_buku k ON o.kategori_id = k.id
                      WHERE o.nama_buku LIKE :kw1 OR o.kode_buku LIKE :kw2
                      ORDER BY o.nama_buku ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':kw1', $kw, PDO::PARAM_STR);
            $stmt->bindValue(':kw2', $kw, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Buku::search error: " . $e->getMessage());
            return null;
        }
    }
}
