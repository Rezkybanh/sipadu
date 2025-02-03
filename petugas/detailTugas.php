<?php
// Periksa apakah id_pengaduan ada di URL
if (!isset($_GET['id_pengaduan']) || empty($_GET['id_pengaduan'])) {
    die("ID pengaduan tidak ditemukan.");
}

$id_pengaduan = $_GET['id_pengaduan'];

try {
    $query = "SELECT id_pengaduan, id_petugas, judul, deskripsi, bukti, laporan_petugas, tanggal_pengaduan, tanggal_selesai, keteranganRevisi, status FROM pengaduan WHERE id_pengaduan = :id_pengaduan";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_pengaduan', $id_pengaduan, PDO::PARAM_INT);
    $stmt->execute();
    $pengaduan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pengaduan) {
        die("Data pengaduan tidak ditemukan.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Tangani upload file dan update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
    try {
        $uploadDir = '../pdf/';
        $fileName = basename($_FILES['pdf_file']['name']);
        $filePath = $uploadDir . $fileName;

        // Periksa apakah file benar-benar PDF
        $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($fileType !== 'pdf') {
            die("Hanya file PDF yang diperbolehkan.");
        }

        if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $filePath)) {
            $query = "UPDATE pengaduan SET laporan_petugas = :file_name, status = 'Selesai', tanggal_selesai = NOW() WHERE id_pengaduan = :id_pengaduan";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':file_name', $fileName, PDO::PARAM_STR);
            $stmt->bindParam(':id_pengaduan', $id_pengaduan, PDO::PARAM_INT);
            $stmt->execute();
            
            echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Laporan telah diunggah dan status diperbarui.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'index.php?page=listTugas';
                });
            </script>";
            exit();
        } else {
            echo "Gagal mengunggah file.";
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
        <a href="index.php?page=listTugas">
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

    <?php if ($pengaduan['status'] === 'Diproses' || $pengaduan['status'] === 'Revisi') : ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="detail-item">
                <strong>Upload Laporan (PDF):</strong>
                <input type="file" name="pdf_file" accept=".pdf" class="form-control" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-success">SELESAI</button>
            </div>
        </form>
    <?php endif; ?>
</div>