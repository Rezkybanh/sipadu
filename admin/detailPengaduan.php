<?php
// Periksa apakah id_pengaduan ada di URL
if (!isset($_GET['id_pengaduan']) || empty($_GET['id_pengaduan'])) {
    die("ID pengaduan tidak ditemukan.");
}

$id_pengaduan = $_GET['id_pengaduan'];

try {
    // Query untuk mengambil data pengaduan
    $query = "SELECT id,id_pengaduan, id_petugas, judul, deskripsi, bukti, tanggal_pengaduan,tanggal_selesai,keteranganRevisi, status FROM pengaduan WHERE id_pengaduan = :id_pengaduan";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_pengaduan', $id_pengaduan, PDO::PARAM_INT);
    $stmt->execute();
    $pengaduan = $stmt->fetch(PDO::FETCH_ASSOC);

    // Periksa apakah data pengaduan ditemukan
    if (!$pengaduan) {
        die("Data pengaduan tidak ditemukan.");
    }

    // Jika pengaduan dalam status 'Baru', ambil daftar petugas untuk dropdown
    $petugasList = [];
    if ($pengaduan['status'] === 'Baru') {
        $query_petugas = "SELECT id, username FROM user WHERE role = 'Petugas'";
        $stmt_petugas = $pdo->prepare($query_petugas);
        $stmt_petugas->execute();
        $petugasList = $stmt_petugas->fetchAll(PDO::FETCH_ASSOC);
    }

    // Jika id_petugas ada, ambil nama petugas
    $nama_petugas = null;
    if (!empty($pengaduan['id_petugas'])) {
        $query_nama_petugas = "SELECT username FROM user WHERE id = :id_petugas";
        $stmt_nama_petugas = $pdo->prepare($query_nama_petugas);
        $stmt_nama_petugas->bindParam(':id_petugas', $pengaduan['id_petugas'], PDO::PARAM_INT);
        $stmt_nama_petugas->execute();
        $nama_petugas = $stmt_nama_petugas->fetchColumn();
    }

    $nama_masyarakat = null;
    if (!empty($pengaduan['id'])) {
        $query_nama_masyarakat = "SELECT username FROM user WHERE id = :id";
        $stmt_nama_masyarakat = $pdo->prepare($query_nama_masyarakat);
        $stmt_nama_masyarakat->bindParam(':id', $pengaduan['id'], PDO::PARAM_INT);
        $stmt_nama_masyarakat->execute();
        $nama_masyarakat = $stmt_nama_masyarakat->fetchColumn();
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Tangani aksi POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_pengaduan = $_POST['id_pengaduan'];
        $action = $_POST['action'];
        $id_petugas = $_POST['id_petugas'] ?? null;

        if ($action === 'submit' && $id_petugas) {
            $query = "UPDATE pengaduan SET id_petugas = :id_petugas, status = 'Diproses' WHERE id_pengaduan = :id_pengaduan";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id_petugas', $id_petugas, PDO::PARAM_INT);
            $stmt->bindParam(':id_pengaduan', $id_pengaduan, PDO::PARAM_INT);
            $stmt->execute();

            // Dengan Sweet Alert (pastikan sudah include library)
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Pengaduan berhasil diproses',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'index.php?page=kelolaPengaduan';
                });
            </script>";
            exit();
        } elseif ($action === 'tolak') {
            $query = "UPDATE pengaduan SET status = 'Ditolak' WHERE id_pengaduan = :id_pengaduan";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id_pengaduan', $id_pengaduan, PDO::PARAM_INT);
            $stmt->execute();
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Ditolak!',
                    text: 'Berhasil Menolak Pengaduan!',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'index.php?page=kelolaPengaduan';
                });
            </script>";
            exit();
        } elseif ($action === 'revisi') {
            $keterangan_revisi = $_POST['keterangan_revisi'] ?? '';
            $query = "UPDATE pengaduan SET status = 'Revisi', keteranganRevisi = :keterangan_revisi WHERE id_pengaduan = :id_pengaduan";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':keterangan_revisi', $keterangan_revisi, PDO::PARAM_STR);
            $stmt->bindParam(':id_pengaduan', $id_pengaduan, PDO::PARAM_INT);
            $stmt->execute();
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<style>
    body {
        margin: 20px;
    }

    .detail-container {
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
    }

    .detail-container h2 {
        margin-bottom: 20px;
    }

    .detail-item {
        margin-bottom: 15px;
    }

    .detail-item strong {
        display: block;
        margin-bottom: 5px;
    }

    .pdf-container iframe {
        width: 100%;
        height: 600px;
    }

    .form-actions {
        margin-top: 20px;
    }
</style>

<div class="detail-container">
    <h2>
        <a href="index.php?page=kelolaPengaduan">
            <i class="fas fa-chevron-left"></i>
        </a>
        Detail Pengaduan
    </h2>

    <div class="detail-item">
        <strong>Judul:</strong>
        <span><?= htmlspecialchars($pengaduan['judul']); ?></span>
    </div>
    <?php if (!empty($nama_masyarakat)) : ?>
        <div class="detail-item">
            <strong>Nama Pengadu:</strong>
            <span><?= htmlspecialchars($nama_masyarakat); ?></span>
        </div>
    <?php endif; ?>
    <div class="detail-item">
        <strong>Tanggal Pengaduan:</strong>
        <span><?= htmlspecialchars($pengaduan['tanggal_pengaduan']); ?></span>
    </div>
    <div class="detail-item">
        <strong>Tanggal Selesai:</strong>
        <?php if (is_null($pengaduan['tanggal_selesai'])) : ?>
            <span>Belum Selesai</span>
        <?php else : ?>
            <span><?= htmlspecialchars($pengaduan['tanggal_selesai']); ?></span>
        <?php endif; ?>
    </div>
    <div class="detail-item">
        <strong>Deskripsi:</strong>
        <span><?= htmlspecialchars($pengaduan['deskripsi']); ?></span>
    </div>
    <div class="detail-item">
        <strong>Bukti:</strong>
        <div class="pdf-container">
            <?php if ($pengaduan['bukti']) : ?>
                <iframe src="../pdf/<?= htmlspecialchars($pengaduan['bukti']); ?>" frameborder="0"></iframe>
            <?php else : ?>
                <span class="text-muted">Tidak ada bukti.</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="detail-item">
        <strong>Status:</strong>
        <span class="badge bg-<?= match (strtolower($pengaduan['status'])) {
                                    'baru' => 'warning',
                                    'diproses' => 'info',
                                    'ditolak' => 'danger',
                                    'revisi' => 'secondary',
                                    'selesai' => 'success',
                                    default => 'light',
                                } ?>">
            <?= htmlspecialchars($pengaduan['status']); ?>
        </span>
    </div>

    <?php if (strtolower($pengaduan['status']) === 'revisi' && !empty($pengaduan['keteranganRevisi'])) : ?>
        <div class="detail-item">
            <strong>Keterangan Revisi:</strong>
            <div class="alert alert-warning" role="alert">
                <?= nl2br(htmlspecialchars($pengaduan['keteranganRevisi'])); ?>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="id_pengaduan" value="<?= htmlspecialchars($id_pengaduan); ?>">
        <?php if ($pengaduan['status'] === 'Baru') : ?>
            <div class="detail-item">
                <strong>Petugas:</strong>
                <select name="id_petugas" class="form-select" required>
                    <option value="" selected disabled>Pilih Petugas</option>
                    <?php foreach ($petugasList as $petugas) : ?>
                        <option value="<?= htmlspecialchars($petugas['id']); ?>">
                            <?= htmlspecialchars($petugas['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php elseif (!empty($nama_petugas)) : ?>
            <div class="detail-item">
                <strong>Petugas:</strong>
                <span><?= htmlspecialchars($nama_petugas); ?></span>
            </div>
        <?php endif; ?>

        <div class="form-actions">
            <?php if ($pengaduan['status'] === 'Baru') : ?>
                <button type="submit" name="action" value="submit" class="btn btn-success">Submit</button>
                <button type="submit" name="action" value="tolak" class="btn btn-danger">Tolak</button>
            <?php endif; ?>
        </div>
    </form>
</div>