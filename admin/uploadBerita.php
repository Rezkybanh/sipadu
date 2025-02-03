<?php
require '../koneksi.php'; // Koneksi ke database

$pesan = "";
$status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = $_POST['judulBerita'];
    $artikel = $_POST['isiBerita'];
    $gambarNama = null;

    // Cek apakah file diunggah
    if (!empty($_FILES['gambarBerita']['name'])) {
        $targetDir = "../gambarBerita/"; // Direktori penyimpanan gambar
        $gambarNama = basename($_FILES["gambarBerita"]["name"]);
        $targetFilePath = $targetDir . $gambarNama;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Validasi tipe file (hanya JPG, JPEG, PNG, GIF)
        $allowTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($_FILES["gambarBerita"]["tmp_name"], $targetFilePath)) {
                // Berhasil diupload
            } else {
                $pesan = "Gagal mengunggah gambar.";
                $status = "error";
            }
        } else {
            $pesan = "Format file tidak valid. Gunakan JPG, JPEG, PNG, atau GIF.";
            $status = "error";
        }
    }

    // Simpan data ke database jika tidak ada error
    if ($pesan == "") {
        try {
            $stmt = $pdo->prepare("INSERT INTO berita (judul, artikel, gambar) VALUES (:judul, :artikel, :gambar)");
            $stmt->execute([
                ':judul' => $judul,
                ':artikel' => $artikel,
                ':gambar' => $gambarNama
            ]);
            $pesan = "Berita berhasil diunggah!";
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
            <?php if ($status === "success") echo "window.location.href='index.php?page=uploadBerita';"; ?>
        });
    </script>
<?php endif; ?>

<div class="container form-container mt-5">
    <div class="form-title text-center fw-bold fs-4 mb-3">Form Input Berita</div>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="judulBerita" class="form-label">Judul Berita</label>
            <input type="text" class="form-control" id="judulBerita" name="judulBerita" required>
        </div>
        <div class="mb-3">
            <label for="isiBerita" class="form-label">Isi Berita</label>
            <textarea class="form-control" id="isiBerita" name="isiBerita" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label for="gambarBerita" class="form-label d-block">Upload Gambar</label>
            <input type="file" id="gambarBerita" name="gambarBerita" class="d-none" accept="image/*">
            <button type="button" class="btn btn-custom d-flex align-items-center gap-2" onclick="document.getElementById('gambarBerita').click()">
                <i class='bx bx-cloud-upload'></i> Pilih Gambar
            </button>
            <span id="namaFile" class="ms-2"></span>
        </div>
        <div class="d-flex">
            <button type="submit" class="btn btn-primary w-100">Submit Berita</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('gambarBerita').addEventListener('change', function() {
        let fileName = this.files[0] ? this.files[0].name : "Tidak ada file dipilih";
        document.getElementById('namaFile').textContent = fileName;
    });
</script>
