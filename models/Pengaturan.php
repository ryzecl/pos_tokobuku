<?php
class Pengaturan
{
    private $conn;
    private $table_name = "pengaturan";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function get(string $kunci)
    {
        try {
            $stmt = $this->conn->prepare("SELECT nilai FROM " . $this->table_name . " WHERE kunci = :kunci LIMIT 1");
            $stmt->execute([':kunci' => $kunci]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['nilai'] : null;
        } catch (PDOException $e) {
            // jangan lempar lagi agar tidak fatal; kembalikan null pada error DB
            return null;
        }
    }

    public function set($key, $value)
    {
        $query = "INSERT INTO " . $this->table_name . " (kunci, nilai) 
                  VALUES (:kunci, :nilai)
                  ON DUPLICATE KEY UPDATE nilai = :nilai";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':kunci', $key);
        $stmt->bindParam(':nilai', $value);

        return $stmt->execute();
    }

    public function getAll()
    {
        try {
            // gunakan kolom `nama` dari DB dan alias jadi `kunci` supaya kode pemanggil tidak berubah
            $stmt = $this->conn->prepare("SELECT nama AS kunci, nilai FROM pengaturan");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($rows as $row) {
                $result[$row['kunci']] = $row['nilai'];
            }
            return $result;
        } catch (PDOException $e) {
            return []; // jangan lempar agar tidak fatal
        }
    }

    public function updateAll($settings)
    {
        try {
            $this->conn->beginTransaction();

            foreach ($settings as $key => $value) {
                $this->set($key, $value);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
