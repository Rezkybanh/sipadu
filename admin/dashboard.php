<?php
// Koneksi ke database menggunakan PDO
require '../koneksi.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query untuk menghitung jumlah laporan masuk per bulan
    $stmtMasuk = $pdo->prepare("SELECT MONTH(tanggal_pengaduan) AS bulan, COUNT(*) AS total FROM pengaduan GROUP BY bulan");
    $stmtMasuk->execute();
    $laporanMasuk = array_fill(0, 12, 0);
    while ($row = $stmtMasuk->fetch(PDO::FETCH_ASSOC)) {
        $laporanMasuk[$row['bulan'] - 1] = $row['total'];
    }

    // Query untuk menghitung jumlah laporan diproses per bulan
    $stmtDiproses = $pdo->prepare("SELECT MONTH(tanggal_pengaduan) AS bulan, COUNT(*) AS total FROM pengaduan WHERE status = 'Diproses' GROUP BY bulan");
    $stmtDiproses->execute();
    $laporanDiproses = array_fill(0, 12, 0);
    while ($row = $stmtDiproses->fetch(PDO::FETCH_ASSOC)) {
        $laporanDiproses[$row['bulan'] - 1] = $row['total'];
    }

    // Query untuk menghitung jumlah pengaduan selesai per bulan
    $stmtSelesai = $pdo->prepare("SELECT MONTH(tanggal_selesai) AS bulan, COUNT(*) AS total FROM pengaduan WHERE status = 'Selesai' GROUP BY bulan");
    $stmtSelesai->execute();
    $pengaduanSelesai = array_fill(0, 12, 0);
    while ($row = $stmtSelesai->fetch(PDO::FETCH_ASSOC)) {
        $pengaduanSelesai[$row['bulan'] - 1] = $row['total'];
    }

    // Konversi data ke JSON agar bisa digunakan di Chart.js
    $laporanMasukJSON = json_encode($laporanMasuk);
    $laporanDiprosesJSON = json_encode($laporanDiproses);
    $pengaduanSelesaiJSON = json_encode($pengaduanSelesai);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<div class="container mt-4">
    <div class="row">
        <!-- Total Laporan Masuk -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title text-center">Total Laporan Masuk Per Bulan</h5>
                    <canvas id="laporanMasukChart"></canvas>
                    <div class="text-center mt-3">
                        <a href="unduhLapMasuk.php" class="btn btn-primary btn-sm">
                            Download Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Laporan Diproses -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title text-center">Total Laporan Diproses Per Bulan</h5>
                    <canvas id="laporanDiprosesChart"></canvas>
                    <div class="text-center mt-3">
                        <a href="unduhLapProses.php" class="btn btn-primary btn-sm">
                            Download Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pengaduan Selesai -->
        <div class="col-md-12 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title text-center">Total Pengaduan yang Telah Selesai</h5>
                    <canvas id="pengaduanSelesaiChart"></canvas>
                    <div class="text-center mt-3">
                        <a href="unduhLapSelesai.php" class="btn btn-primary btn-sm">
                            Download Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data dari PHP
    const laporanMasukData = <?php echo $laporanMasukJSON; ?>;
    const laporanDiprosesData = <?php echo $laporanDiprosesJSON; ?>;
    const pengaduanSelesaiData = <?php echo $pengaduanSelesaiJSON; ?>;

    const labelsBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    // Grafik Total Laporan Masuk
    new Chart(document.getElementById('laporanMasukChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labelsBulan,
            datasets: [{
                label: 'Laporan Masuk',
                data: laporanMasukData,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Grafik Total Laporan Diproses
    new Chart(document.getElementById('laporanDiprosesChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labelsBulan,
            datasets: [{
                label: 'Laporan Diproses',
                data: laporanDiprosesData,
                backgroundColor: 'rgba(255, 206, 86, 0.5)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Grafik Total Pengaduan yang Telah Selesai
    new Chart(document.getElementById('pengaduanSelesaiChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: labelsBulan,
            datasets: [{
                label: 'Pengaduan Selesai',
                data: pengaduanSelesaiData,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
</script>
