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

// Ambil data pengaduan
$query = "SELECT 
            p.id_pengaduan, 
            p.judul, 
            p.deskripsi, 
            p.status, 
            p.tanggal_pengaduan,
            p.tanggal_selesai,
            u1.username AS pelapor, 
            COALESCE(u2.username, 'Belum Ditugaskan') AS petugas
          FROM pengaduan p
          JOIN user u1 ON p.id = u1.id
          LEFT JOIN user u2 ON p.id_petugas = u2.id
          WHERE p.status = 'Selesai'
          ORDER BY p.tanggal_pengaduan DESC";

$stmt = $pdo->prepare($query);
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

    <div class="judul-laporan">LAPORAN PENGADUAN SELESAI</div>

    <table>
        <tr>
            <th>No.</th>
            <th>Pelapor</th>
            <th>Petugas</th>
            <th>Judul</th>
            <th>Status</th>
            <th>Tanggal Pengaduan</th>
            <th>Tanggal Selesai</th>
        </tr>';

// Tambahkan data pengaduan ke tabel
$no = 1;
foreach ($pengaduan as $row) {
  $tglPengaduan = date('d', strtotime($row['tanggal_pengaduan'])) . ' ' . $bulan[date('F', strtotime($row['tanggal_pengaduan']))] . ' ' . date('Y', strtotime($row['tanggal_pengaduan']));
  $tglSelesai = date('d', strtotime($row['tanggal_selesa'])) . ' ' . $bulan[date('F', strtotime($row['tanggal_selesa']))] . ' ' . date('Y', strtotime($row['tanggal_selesa']));
  $html .= "<tr>
                <td>{$no}</td>
                <td>{$row['pelapor']}</td>
                <td>{$row['petugas']}</td>
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
        <strong>Administrator</strong>
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
