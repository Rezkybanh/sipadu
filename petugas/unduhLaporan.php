<?php
require '../vendor/autoload.php'; // Pastikan composer autoload dipanggil
require '../koneksi.php';
require_once '../middleware.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    die("Error: User tidak terautentikasi.");
}

$id_user = $_SESSION['user_id'];

// Cek apakah user adalah petugas
$queryRole = "SELECT role FROM user WHERE id = :id_user";
$stmtRole = $pdo->prepare($queryRole);
$stmtRole->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$stmtRole->execute();
$userRole = $stmtRole->fetch(PDO::FETCH_ASSOC);

if (!$userRole || $userRole['role'] !== 'Petugas') {
    die("Error: Anda bukan petugas.");
}

// Ambil data petugas untuk ditampilkan (Nama Petugas)
$queryPetugas = "SELECT username FROM user WHERE id = :id_user";
$stmtPetugas = $pdo->prepare($queryPetugas);
$stmtPetugas->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$stmtPetugas->execute();
$petugas = $stmtPetugas->fetch(PDO::FETCH_ASSOC);

// Ambil data pengaduan berdasarkan id_petugas
$query = "SELECT id_pengaduan, judul, deskripsi, status, tanggal_pengaduan 
          FROM pengaduan 
          WHERE id_petugas = :id_user 
          ORDER BY tanggal_pengaduan DESC";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$stmt->execute();
$pengaduan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Siapkan HTML untuk PDF
$html = '<h2 style="text-align: center;">Laporan Pengaduan</h2>';

// Menampilkan Nama Petugas dan Tanggal Laporan
$html .= "<p><strong>Petugas: </strong>{$petugas['username']}</p>";
$html .= "<p><strong>Tanggal Laporan: </strong>" . date('d-m-Y') . "</p>";
$html .= '<table border="1" width="100%" cellspacing="0" cellpadding="5">
            <tr>
                <th>ID Pengaduan</th>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Tanggal Pengaduan</th>
            </tr>';

foreach ($pengaduan as $row) {
    $html .= "<tr>
                <td>{$row['id_pengaduan']}</td>
                <td>{$row['judul']}</td>
                <td>{$row['deskripsi']}</td>
                <td>{$row['status']}</td>
                <td>{$row['tanggal_pengaduan']}</td>
              </tr>";
}
$html .= '</table>';

// Konfigurasi DomPDF
$options = new Options();
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Unduh file PDF
ob_clean();
$dompdf->stream("Laporan_Pengaduan.pdf", array("Attachment" => true));

exit();
?>
