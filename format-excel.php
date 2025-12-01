<?php
require_once 'config/config.php';
requireRole(['admin', 'kasir']);
require_once 'models/Penjualan.php';

$database = new Database();
$db = $database->getConnection();

$penjualan = new Penjualan($db);

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Get laporan data
$stmt = $penjualan->getLaporanPenjualan($start_date, $end_date);

// Set headers for Excel download
$filename = "laporan_penjualan_" . $start_date . "_" . $end_date . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Output data in HTML table format (Excel compatible)
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 5px; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h3>Laporan Penjualan</h3>
    <p>Periode: <?php echo date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)); ?></p>
    
    <table>
        <thead>
            <tr>
                <th>No. Transaksi</th>
                <th>Kasir</th>
                <th>Diskon</th>
                <th>Total Harga</th>
                <th>Total Bayar</th>
                <th>Kembalian</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_penjualan = 0;
            $total_diskon = 0;
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                $total_penjualan += $row['total_harga'];
                $total_diskon += $row['diskon'] ?? 0;
            ?>
            <tr>
                <td><?php echo $row['no_transaksi']; ?></td>
                <td><?php echo $row['kasir']; ?></td>
                <td class="text-right"><?php echo $row['diskon']; ?></td>
                <td class="text-right"><?php echo $row['total_harga']; ?></td>
                <td class="text-right"><?php echo $row['total_bayar']; ?></td>
                <td class="text-right"><?php echo $row['kembalian']; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_penjualan'])); ?></td>
            </tr>
            <?php endwhile; ?>
            
            <!-- Summary Row -->
            <tr style="font-weight: bold; background-color: #e6e6e6;">
                <td colspan="2" class="text-center">Total</td>
                <td class="text-right"><?php echo $total_diskon; ?></td>
                <td class="text-right"><?php echo $total_penjualan; ?></td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
