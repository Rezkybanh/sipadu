<?php
require '../vendor/autoload.php'; // Pastikan composer autoload dipanggil
require '../koneksi.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Ambil data pengaduan yang berstatus "Diproses" dengan username pelapor dan petugas
$query = "SELECT 
            p.id_pengaduan, 
            p.judul, 
            p.deskripsi, 
            p.status, 
            p.tanggal_pengaduan,
            u1.username AS pelapor, 
            COALESCE(u2.username, 'Belum Ditugaskan') AS petugas
          FROM pengaduan p
          JOIN user u1 ON p.id = u1.id
          LEFT JOIN user u2 ON p.id_petugas = u2.id
          WHERE p.status = 'Diproses'
          ORDER BY p.tanggal_pengaduan DESC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$pengaduan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Siapkan HTML untuk PDF
$html = '<h2 style="text-align: center;">Laporan Pengaduan (Status: Diproses)</h2>';
$html .= "<p><strong>Tanggal Laporan: </strong>" . date('d-m-Y') . "</p>";
$html .= '<table border="1" width="100%" cellspacing="0" cellpadding="5">
            <tr>
                <th>ID Pengaduan</th>
                <th>Pelapor</th>
                <th>Petugas</th>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Tanggal Pengaduan</th>
            </tr>';

// Tambahkan data pengaduan ke tabel
foreach ($pengaduan as $row) {
    $html .= "<tr>
                <td>{$row['id_pengaduan']}</td>
                <td>{$row['pelapor']}</td>
                <td>{$row['petugas']}</td>
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
$dompdf->setPaper('A4', 'potrait');
$dompdf->render();

// Unduh file PDF
ob_clean();
$dompdf->stream("Laporan_Pengaduan_Diproses.pdf", array("Attachment" => true));

exit();
?>
