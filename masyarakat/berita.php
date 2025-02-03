<?php
require '../koneksi.php'; // Pastikan koneksi menggunakan PDO

try {
    // Ambil semua berita dari database
    $stmt = $pdo->query("SELECT id, judul, tanggalUpload FROM berita ORDER BY tanggalUpload DESC");
    $beritaList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<div class="container mt-5">
    <?php foreach ($beritaList as $berita): ?>
        <div class="news-item p-3 mb-3 border rounded shadow-sm bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="news-title fw-bold fs-5"><?= htmlspecialchars($berita['judul']); ?></div>
                    <div class="news-date text-muted"><?= date("l, d F Y", strtotime($berita['tanggalUpload'])); ?></div>
                </div>
                <a href="index.php?page=detailBerita&id=<?= $berita['id']; ?>" class="news-link text-decoration-none text-primary fw-semibold">
                    Selengkapnya
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
