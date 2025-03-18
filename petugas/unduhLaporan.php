<?php
require '../vendor/autoload.php';
require '../koneksi.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Konfigurasi DomPDF
$options = new Options();
$options->set('defaultFont', 'Arial');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

// Konversi bulan ke bahasa Indonesia
$bulan = [
    'January' => 'Januari',
    'February' => 'Februari',
    'March' => 'Maret',
    'April' => 'April',
    'May' => 'Mei',
    'June' => 'Juni',
    'July' => 'Juli',
    'August' => 'Agustus',
    'September' => 'September',
    'October' => 'Oktober',
    'November' => 'November',
    'December' => 'Desember'
];

$tanggalSekarang = date('d') . ' ' . $bulan[date('F')] . ' ' . date('Y');
$bulanIni = strtoupper($bulan[date('F')]);
$tahun = date('Y');

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
$query = "SELECT id_pengaduan, judul, deskripsi, status, tanggal_pengaduan, tanggal_selesai
          FROM pengaduan 
          WHERE id_petugas = :id_user 
          ORDER BY tanggal_pengaduan DESC";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$stmt->execute();
$pengaduan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert Gambar ke Base64
$logoPath = '../assets/logoPnd.png';
$logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : "";
$logoImg = $logoData ? '<img src="data:image/png;base64,' . $logoData . '" style="width:90px;">' : '';

// HTML untuk PDF
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengaduan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .kop-surat { text-align: center; margin-bottom: 10px; }
        .judul-laporan { text-align: center; font-size: 16px; font-weight: bold; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 6px; text-align: center; font-size: 10px; }
        th { background-color: #f2f2f2; }
        .footer { margin-top: 20px; text-align: left; font-size: 12px; }
        .footer2 { margin-top: 20px; text-align: right; font-size: 12px; }
    </style>
</head>
<body>
    <table style="border-collapse: collapse; border: none; width: 100%;">
        <tr>
            <td width="15%" align="center" style="border: none;">' . $logoImg . '</td>
            <td align="center" style="border: none;">
                <div style="font-size: 16px; font-weight: bold;">PEMERINTAH KABUPATEN PANGANDARAN</div>
                <div style="font-size: 16px; font-weight: bold;">BADAN PENDAPATAN DAERAH</div>
                <div style="font-size: 14px;">Jln. Kidang Pananjung No. 03 Pangandaran Kode Pos 46396</div>
                <div style="font-size: 14px;">Email: <i>bapendakabupatenpangandaran@gmail.com</i></div>
            </td>
            <td width="15%" style="border: none;"></td>
        </tr>
    </table>
    <hr>
    <hr style="border: 1px solid black; margin-top: -5px;">

    <div class="judul-laporan">LAPORAN PENGADUAN MASUK</div>

    <table>
        <tr>
            <th>No.</th>
            <th>Judul</th>
            <th>Status</th>
            <th>Tanggal Pengaduan</th>
            <th>Tanggal Selesai</th>
        </tr>';

// Tambahkan data pengaduan ke tabel
$no = 1;
foreach ($pengaduan as $row) {
    $tglPengaduan = date('d', strtotime($row['tanggal_pengaduan'])) . ' ' . $bulan[date('F', strtotime($row['tanggal_pengaduan']))] . ' ' . date('Y', strtotime($row['tanggal_pengaduan']));
    $tglSelesai = date('d', strtotime($row['tanggal_selesai'])) . ' ' . $bulan[date('F', strtotime($row['tanggal_selesai']))] . ' ' . date('Y', strtotime($row['tanggal_selesai']));
    $html .= "<tr>
                <td>{$no}</td>
                <td>{$row['judul']}</td>
                <td>{$row['status']}</td>
                <td>{$tglPengaduan}</td>
                <td>{$tglSelesai}</td>
              </tr>";
    $no++;
}

$html .= '</table>
    <p class="footer">
            Demikian laporan ini saya buat, semoga menjadi bahan pertimbangan selanjutnya. <br>
        Atas perhatiannya, terima kasih.
    </p>
    <div class="footer2">
        Pangandaran, ' . $tanggalSekarang . '<br><br><br><br>
        <strong>' . htmlspecialchars($petugas['username']) . '</strong>
    </div>
</body>
</html>';

// Load HTML ke DomPDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output PDF
ob_clean();
$dompdf->stream("Laporan_Pengaduan.pdf", array("Attachment" => true));
exit();
