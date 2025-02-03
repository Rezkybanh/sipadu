<?php
include '../koneksi.php';

// Jika tombol hapus diklik
if (isset($_POST['hapus'])) {
    $id = $_POST['id'];
    
    // Ambil nama file gambar dari database
    $stmt = $pdo->prepare("SELECT gambar FROM berita WHERE id = ?");
    $stmt->execute([$id]);
    $berita = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($berita && $berita['gambar']) {
        $gambarPath = '../gambarBerita/' . $berita['gambar'];
        if (file_exists($gambarPath)) {
            unlink($gambarPath); // Hapus file gambar
        }
    }
    
    // Hapus berita dari database
    $stmt = $pdo->prepare("DELETE FROM berita WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo "<script>
            Swal.fire({
                title: 'Berhasil!',
                text: 'Berita berhasil dihapus!',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href='index.php?page=kelolaBerita';
                }
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                title: 'Gagal!',
                text: 'Gagal menghapus berita!',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}

// Ambil data berita dari database
$stmt = $pdo->query("SELECT * FROM berita ORDER BY tanggalUpload DESC");
$beritaList = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .btn-sm {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px 10px;
        height: 32px;
    }
</style>

<div class="container mt-5">
    <?php if (empty($beritaList)) : ?>
        <div class="alert alert-warning" role="alert">
            Tidak ada berita yang tersedia.
        </div>
    <?php else : ?>
        <div class="list-group">
            <?php foreach ($beritaList as $berita) : ?>
                <div class="list-group-item d-flex justify-content-between align-items-center mb-3 w-100">
                    <div>
                        <h5 class="mb-1"><?php echo htmlspecialchars($berita['judul']); ?></h5>
                        <p class="mb-1 text-muted"><?php echo date('l, d F Y', strtotime($berita['tanggalUpload'])); ?></p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="index.php?page=detailKelolaBerita&id=<?php echo $berita['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $berita['id']; ?>)"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: 'Berita yang dihapus tidak bisa dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id';
            input.value = id;
            
            let submit = document.createElement('input');
            submit.type = 'hidden';
            submit.name = 'hapus';
            submit.value = '1';
            
            form.appendChild(input);
            form.appendChild(submit);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>