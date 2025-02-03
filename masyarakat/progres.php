<?php
include '../koneksi.php'; // File koneksi database

// Ambil ID user yang sedang login dari sesi
$id_user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
checkAccess('Masyarakat');

// Ambil ID pengaduan dari parameter GET
$id_pengaduan = isset($_GET['id_pengaduan']) ? $_GET['id_pengaduan'] : null;

if (!isset($_GET['id_pengaduan']) || empty($_GET['id_pengaduan'])) {
    echo "<script>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'ID pengaduan tidak ditemukan di URL!',
    }).then(() => {
        window.location.href = 'index.php?page=pengaduan';
    });
    </script>";
    exit;
}

// Proses penghapusan pengaduan ketika tombol delete ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_pengaduan'])) {
    try {
        // Ambil nama file bukti dari database
        $query = "SELECT bukti FROM pengaduan WHERE id_pengaduan = :id_pengaduan";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_pengaduan', $id_pengaduan, PDO::PARAM_INT);
        $stmt->execute();
        $pengaduan = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pengaduan && $pengaduan['bukti']) {
            $filePath = '../pdf/' . $pengaduan['bukti'];
            // Cek apakah file bukti ada di direktori penyimpanan
            if (file_exists($filePath)) {
                unlink($filePath); // Hapus file bukti
            }
        }

        // Query untuk menghapus pengaduan dari database
        $query = "DELETE FROM pengaduan WHERE id_pengaduan = :id_pengaduan";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_pengaduan', $id_pengaduan, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Pengaduan Anda Akan Dihapus, Silahkan Ajukan Pengaduan Baru!',
                }).then(() => {
                    window.location.href = 'index.php?page=pengaduan';
                });
            </script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan saat menghapus pengaduan.',
            });
        </script>";
    }
}


// Tangani submit form Revisi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_revisi'])) {
    $keteranganRevisi = isset($_POST['keteranganRevisi']) ? trim($_POST['keteranganRevisi']) : null;

    if ($keteranganRevisi) {
        try {
            $query = "UPDATE pengaduan SET status = 'Revisi', keteranganRevisi = :keteranganRevisi WHERE id_pengaduan = :id_pengaduan";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'keteranganRevisi' => $keteranganRevisi,
                'id_pengaduan' => $id_pengaduan
            ]);

            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Pengaduan berhasil direvisi!',
                }).then(() => {
                    window.location.href = 'index.php?page=progres&id_pengaduan=$id_pengaduan';
                });
            </script>";
            exit;
        } catch (PDOException $e) {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi kesalahan saat menyimpan revisi.',
                });
            </script>";
        }
    }
}


// Lanjutkan pengolahan jika ID pengaduan tersedia
$id_pengaduan = $_GET['id_pengaduan'];

// Debug untuk memastikan nilai ID pengaduan diterima
if (!$id_pengaduan) {
    echo "<p class='text-danger'>ID pengaduan tidak ditemukan di URL.</p>";
    var_dump($_GET); // Debugging
    exit;
}

// Cek apakah pengguna telah login
if (!$id_user) {
    echo "<p class='text-danger'>Anda harus login untuk mengakses halaman ini.</p>";
    exit;
}

// Query untuk mengambil data pengaduan berdasarkan id_pengaduan dan id user
$query = "SELECT * FROM pengaduan WHERE id_pengaduan = :id_pengaduan AND id = :id_user";
$stmt = $pdo->prepare($query);
$stmt->execute(['id_pengaduan' => $id_pengaduan, 'id_user' => $id_user]);
$pengaduan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pengaduan) {
    echo "<p class='text-danger'>Data pengaduan tidak ditemukan atau Anda tidak memiliki akses.</p>";
    exit;
}



$status = $pengaduan['status'];
?>

<style>
    .progress-bar {
        font-weight: bold;
    }

    .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .img-thumbnail {
        max-height: 200px;
        object-fit: cover;
    }

    .download-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: #007bff;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.3s ease;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .download-button i {
        margin-right: 10px;
    }

    .download-button:hover {
        background-color: #ffff;
        transform: translateY(-3px);
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
    }

    .download-button:active {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
    }

    .pdf-container iframe {
        width: 100%;
        height: 600px;
    }
</style>


<div class="container mt-5">
    <div class="feed-title text-center">
        <h2>Progres Laporan Anda</h2>
        <p class="lead">Pantau perkembangan laporan Anda di sini.</p>
    </div>

    <!-- Form Informasi -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Informasi Pengaduan</h5>
            <p><strong>Nama:</strong> <?= htmlspecialchars($id_user); ?></p>
            <p><strong>Judul Pengaduan:</strong> <?= htmlspecialchars($pengaduan['judul']); ?></p>
            <p><strong>Deskripsi:</strong> <?= htmlspecialchars($pengaduan['deskripsi']); ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($status); ?></p>
            <strong>Bukti:</strong>
            <div class="pdf-container">
                <?php if ($pengaduan['bukti']) : ?>
                    <iframe src="../pdf/<?= htmlspecialchars($pengaduan['bukti']); ?>" frameborder="0"></iframe>
                <?php else : ?>
                    <span class="text-muted">Tidak ada bukti.</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Progress Bar dan Status Laporan -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Status Laporan</h5>
            <div class="progress mb-3">
                <div class="progress-bar 
                    <?= $status === 'Baru' ? 'bg-warning' : ($status === 'Diproses' ? 'bg-primary' : ($status === 'Selesai' ? 'bg-success' : ($status === 'Ditolak' ? 'bg-danger' : ($status === 'Revisi' ? 'bg-secondary' : '')))); ?>"
                    role="progressbar"
                    style="width: <?= $status === 'Baru' ? '10%' : ($status === 'Diproses' ? '50%' : ($status === 'Ditolak' ? '100%' : ($status === 'Selesai' ? '100%' : '25%'))); ?>;">
                    <?= htmlspecialchars($status); ?>
                </div>
            </div>


            <?php if ($status === 'Revisi'): ?>
                <p><strong>Keterangan Revisi:</strong> <?= htmlspecialchars($pengaduan['keteranganRevisi']); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Button dan Form Sesuai Status -->
    <?php if ($status === 'Baru'): ?>
        <p class="text-info">Laporan Anda baru saja masuk.</p>
    <?php elseif ($status === 'Diproses'): ?>
        <p class="text-warning">Laporan Anda sedang diproses.</p>
    <?php elseif ($status === 'Selesai'): ?>
        <p class="text-success">Pengaduan Anda telah selesai diproses! Berikut bukti bahwa pengaduan Anda selesai.</p>
        <div class="pdf-container mb-2">
            <?php if ($pengaduan['laporan_petugas']) : ?>
                <iframe src="../pdf/<?= htmlspecialchars($pengaduan['laporan_petugas']); ?>" frameborder="0" width="100%" height="600px"></iframe>
            <?php else : ?>
                <span class="text-muted">Tidak ada laporan tersedia.</span>
            <?php endif; ?>
            <!-- Form Input Revisi -->
            <div class="mt-3">
                <h5 class="card-title">Ajukan Revisi</h5>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="keteranganRevisi">Keterangan Revisi</label>
                        <textarea name="keteranganRevisi" id="keteranganRevisi" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" name="submit_revisi" class="btn btn-primary mt-3">Kirim Revisi</button>
                </form>
            </div>
        </div>
    <?php elseif ($status === 'Ditolak'): ?>
        <p class="text-danger">Laporan Anda tidak valid.</p>
        <form action="" method="POST">
            <button type="submit" name="delete_pengaduan" class="btn btn-danger">Ajukan Kembali Pengaduan</button>
        </form>
    <?php endif; ?>

    <!-- Hubungi CS -->
    <div class="text-center mb-3">
        <a href="https://wa.me/088809632140" class="btn btn-success btn-lg" target="_blank">
            <i class="fab fa-whatsapp"></i> Hubungi CS
        </a>
    </div>
</div>