<?php
include '../koneksi.php';

// Pastikan ID berita tersedia di URL
if (!isset($_GET['id_berita']) || empty($_GET['id_berita'])) {
    echo "<div class='alert alert-danger'>ID berita tidak ditemukan!</div>";
    exit;
}

$id = $_GET['id_berita'];

// Ambil data berita dari database
$stmt = $pdo->prepare("SELECT * FROM berita WHERE id_berita = ?");
$stmt->execute([$id]);
$berita = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika berita tidak ditemukan
if (!$berita) {
    echo "<div class='alert alert-warning'>Berita tidak ditemukan!</div>";
    exit;
}
?>

<div class="container mt-5">
    <div class="card shadow-lg overflow-hidden">
        <div class="position-relative">
            <div class="card-header bg-dark text-white text-center py-3">
                <h2 class="m-0"><?php echo htmlspecialchars($berita['judul']); ?></h2>
            </div>
            <img src="<?php echo !empty($berita['gambar']) ? '../gambarBerita/' . htmlspecialchars($berita['gambar']) : 'https://placehold.co/600x300'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($berita['judul']); ?>">
        </div>
        <div class="card-body">
            <p class="text-muted"><strong><?php echo date('l, d F Y', strtotime($berita['tanggalUpload'])); ?></strong></p>
            <p class="card-text">
                <?php echo nl2br(htmlspecialchars($berita['artikel'])); ?>
            </p>
        </div>
    </div>
</div>