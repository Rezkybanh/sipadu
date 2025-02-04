<?php

include '../koneksi.php'; // Memastikan koneksi database tersedia

$id_user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Memeriksa apakah pengguna memiliki akses berdasarkan role
checkAccess('Masyarakat');

$formDisabled = false; // Default form aktif

try {
    // Ambil status dari pengaduan terakhir pengguna
    $query = "SELECT id_pengaduan, status FROM pengaduan WHERE id = :id_user ORDER BY id_pengaduan DESC LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt->execute();
    $pengaduan = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jika ada pengaduan dan statusnya bukan 'Selesai', form dinonaktifkan
    if ($pengaduan && $pengaduan['status'] !== 'Selesai') {
        $formDisabled = true;
        $id_pengaduan = $pengaduan['id_pengaduan']; // Simpan ID pengaduan terakhir untuk redirect
    }
} catch (PDOException $e) {
    echo "<script>Swal.fire('Error', 'Terjadi kesalahan: " . $e->getMessage() . "', 'error');</script>";
    exit;
}

// Proses pengaduan jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$formDisabled) {
    $namaPengaduan = $_POST['namaPengaduan'];
    $deskripsiPengaduan = $_POST['deskripsiPengaduan'];
    $lampiran = null;

    if (isset($_FILES['lampiranDokumen']) && $_FILES['lampiranDokumen']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['lampiranDokumen']['tmp_name'];
        $fileName = $_FILES['lampiranDokumen']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExtension === 'pdf') {
            $newFileName = $namaPengaduan . '.' . $fileExtension;
            $uploadPath = '../pdf/' . $newFileName;
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                $lampiran = $newFileName;
            } else {
                die("Gagal mengunggah file!");
            }
        } else {
            die("Hanya file PDF yang diperbolehkan!");
        }
    }

    try {
        $query = "INSERT INTO pengaduan (id, judul, deskripsi, bukti, status) 
                  VALUES (:id_user, :namaPengaduan, :deskripsiPengaduan, :lampiran, 'Baru')";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->bindParam(':namaPengaduan', $namaPengaduan, PDO::PARAM_STR);
        $stmt->bindParam(':deskripsiPengaduan', $deskripsiPengaduan, PDO::PARAM_STR);
        $stmt->bindParam(':lampiran', $lampiran, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Ambil ID pengaduan yang baru saja dimasukkan
            $id_pengaduan = $pdo->lastInsertId();

            echo "<script>
            Swal.fire({
                title: 'Berhasil',
                text: 'Pengaduan berhasil diajukan!',
                icon: 'success'
            }).then(() => {
                window.location.href = 'index.php?page=progres&id_pengaduan={$id_pengaduan}';
            });
            </script>";
        } else {
            echo "<script>Swal.fire('Error', 'Gagal menyimpan pengaduan!', 'error');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>Swal.fire('Error', 'Terjadi kesalahan: " . $e->getMessage() . "', 'error');</script>";
    }
}
?>

<!-- HTML Formulir Pengaduan -->
<div class="feed-title text-center">
    <h2>Form Pengaduan Masyarakat</h2>
    <p class="lead">Silakan isi form berikut untuk mengajukan pengaduan Anda.</p>
</div>

<div class="card shadow-lg">
    <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="namaPengaduan" class="form-label">Nama Pengaduan</label>
                <input type="text" class="form-control" id="namaPengaduan" name="namaPengaduan" required <?= $formDisabled ? 'disabled' : ''; ?>>
            </div>
            <div class="mb-3">
                <label for="deskripsiPengaduan" class="form-label">Deskripsi Pengaduan</label>
                <textarea class="form-control" id="deskripsiPengaduan" name="deskripsiPengaduan" rows="4" required <?= $formDisabled ? 'disabled' : ''; ?>></textarea>
            </div>
            <div class="mb-3">
                <label for="lampiranDokumen" class="form-label">Lampiran Dokumen Pendukung</label>
                <input type="file" class="form-control" id="lampiranDokumen" name="lampiranDokumen" accept=".pdf" required <?= $formDisabled ? 'disabled' : ''; ?>>
            </div>
            <button type="submit" class="btn btn-primary w-100" <?= $formDisabled ? 'disabled' : ''; ?>>Ajukan Pengaduan</button>
        </form>
    </div>
</div>

<!-- Include SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Tampilkan alert jika form disabled
    <?php if ($formDisabled && $id_pengaduan): ?>
        Swal.fire({
            icon: 'info',
            title: 'Informasi',
            text: 'Anda sudah mengajukan pengaduan. Silakan tunggu proses penyelesaian.',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'index.php?page=progres&id_pengaduan=<?= $id_pengaduan ?>';
        });
    <?php endif; ?>


    // Function untuk menampilkan nama file
    function updateFileName() {
        const fileInput = document.getElementById('lampiranDokumen');
        const fileNameDisplay = document.getElementById('fileName');
        const fileName = fileInput.files.length > 0 ? fileInput.files[0].name : '';

        const fileExtension = fileName.split('.').pop().toLowerCase();
        if (fileExtension !== 'pdf') {
            Swal.fire('Error', 'Hanya file PDF yang diperbolehkan!', 'error');
            fileInput.value = '';
            fileNameDisplay.textContent = '';
        } else {
            fileNameDisplay.textContent = fileName ? `File Terpilih: ${fileName}` : '';
        }
    }
</script>