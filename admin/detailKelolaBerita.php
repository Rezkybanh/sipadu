<?php
require '../koneksi.php'; // Koneksi ke database

$pesan = "";
$status = "";
$berita = [
    'id' => '',
    'judul' => '',
    'artikel' => '',
    'gambar' => ''
];

// Ambil ID dari URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data berita berdasarkan ID
    try {
        $stmt = $pdo->prepare("SELECT * FROM berita WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $berita = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$berita) {
            die("Berita tidak ditemukan.");
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Proses update berita saat form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];
    $judul = $_POST['judulBerita'];
    $artikel = $_POST['isiBerita'];
    $gambarNama = $berita['gambar']; // Default tetap gambar lama

    // Jika ada gambar baru diunggah
    if (!empty($_FILES['gambarBerita']['name'])) {
        $targetDir = "../gambarBerita/";
        $gambarNamaBaru = basename($_FILES["gambarBerita"]["name"]);
        $targetFilePath = $targetDir . $gambarNamaBaru;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Validasi tipe file
        $allowTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileType, $allowTypes)) {
            // Hapus gambar lama jika ada
            if (!empty($berita['gambar']) && file_exists($targetDir . $berita['gambar'])) {
                unlink($targetDir . $berita['gambar']);
            }

            // Upload gambar baru
            if (move_uploaded_file($_FILES["gambarBerita"]["tmp_name"], $targetFilePath)) {
                $gambarNama = $gambarNamaBaru;
            } else {
                $pesan = "Gagal mengunggah gambar.";
                $status = "error";
            }
        } else {
            $pesan = "Format file tidak valid. Gunakan JPG, JPEG, PNG, atau GIF.";
            $status = "error";
        }
    }

    // Update data ke database
    if ($pesan == "") {
        try {
            $stmt = $pdo->prepare("UPDATE berita SET judul = :judul, artikel = :artikel, gambar = :gambar WHERE id = :id");
            $stmt->execute([
                ':id' => $id,
                ':judul' => $judul,
                ':artikel' => $artikel,
                ':gambar' => $gambarNama
            ]);
            $pesan = "Berita berhasil diperbarui!";
            $status = "success";
        } catch (PDOException $e) {
            $pesan = "Error: " . $e->getMessage();
            $status = "error";
        }
    }
}
?>

<?php if (!empty($pesan)) : ?>
    <script>
        Swal.fire({
            icon: "<?php echo $status; ?>",
            title: "<?php echo $pesan; ?>",
            showConfirmButton: false,
            timer: 2000
        }).then(() => {
            <?php if ($status === "success") echo "window.location.href='index.php?page=kelolaBerita&id=" . $berita['id'] . "';"; ?>
        });
    </script>
<?php endif; ?>

<div class="container form-container mt-5">
    <div class="form-title text-center fw-bold fs-4 mb-3">Edit Berita</div>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= htmlspecialchars($berita['id']); ?>">

        <div class="mb-3">
            <label for="judulBerita" class="form-label">Judul Berita</label>
            <input type="text" class="form-control" id="judulBerita" name="judulBerita" required value="<?= htmlspecialchars($berita['judul']); ?>">
        </div>

        <div class="mb-3">
            <label for="isiBerita" class="form-label">Isi Berita</label>
            <textarea class="form-control" id="isiBerita" name="isiBerita" rows="4" required
                oninput="adjustHeight(this)" style="max-height: 200px; overflow-y: auto;">
                <?= htmlspecialchars($berita['artikel']); ?>
            </textarea>
        </div>

        <div class="mb-3">
            <label for="gambarBerita" class="form-label d-block">Upload Gambar</label>
            <?php if (!empty($berita['gambar'])) : ?>
                <div class="mb-2">
                    <img src="../gambarBerita/<?= htmlspecialchars($berita['gambar']); ?>" alt="Gambar Berita" class="img-fluid rounded" style="max-width: 200px;">
                </div>
            <?php endif; ?>
            <input type="file" id="gambarBerita" name="gambarBerita" class="d-none" accept="image/*">
            <button type="button" class="btn btn-custom d-flex align-items-center gap-2" onclick="document.getElementById('gambarBerita').click()">
                <i class='bx bx-cloud-upload'></i> Pilih Gambar
            </button>
            <span id="namaFile" class="ms-2"></span>
        </div>

        <div class="d-flex">
            <button type="submit" class="btn btn-primary w-100">Update Berita</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('gambarBerita').addEventListener('change', function() {
        let fileName = this.files[0] ? this.files[0].name : "Tidak ada file dipilih";
        document.getElementById('namaFile').textContent = fileName;
    });
    function adjustHeight(element) {
        element.style.height = "auto"; // Reset height terlebih dahulu
        let newHeight = element.scrollHeight; // Ambil tinggi aktual konten
        element.style.height = (newHeight > 200 ? 200 : newHeight) + "px"; // Set tinggi dengan batas maksimal
    }

    // Panggil fungsi saat halaman dimuat agar langsung menyesuaikan
    document.addEventListener("DOMContentLoaded", function() {
        adjustHeight(document.getElementById("isiBerita"));
    });
</script>
