<?php
try {
    // Query untuk mengambil data dari tabel pengaduan
    $query = "SELECT id_pengaduan, judul, tanggal_pengaduan, status FROM pengaduan ORDER BY tanggal_pengaduan DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // Fetch semua data
    $pengaduan = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Hapus semua pengaduan dengan status "Selesai" jika tombol ditekan
if (isset($_POST['hapus_selesai'])) {
    try {
        // Ambil daftar file sebelum menghapus data
        $stmt = $pdo->prepare("SELECT bukti, laporan_petugas FROM pengaduan WHERE status = 'Selesai'");
        $stmt->execute();
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Hapus file dari folder ../pdf/
        foreach ($files as $file) {
            if (!empty($file['bukti']) && file_exists("../pdf/" . $file['bukti'])) {
                unlink("../pdf/" . $file['bukti']); // Hapus file bukti
            }
            if (!empty($file['laporan_petugas']) && file_exists("../pdf/" . $file['laporan_petugas'])) {
                unlink("../pdf/" . $file['laporan_petugas']); // Hapus file laporan petugas
            }
        }

        // Hapus data dari database
        $stmt = $pdo->prepare("DELETE FROM pengaduan WHERE status = 'Selesai'");
        $stmt->execute();

        $pesan = "Pengaduan dengan status Selesai dan file terkait telah dihapus, Silahkan Segarkan Halaman!";
    } catch (PDOException $e) {
        $pesan = "Gagal menghapus data: " . $e->getMessage();
    }
}

?>
<style>
    .table-responsive {
        overflow-x: auto;
    }

    .dropdown-status {
        position: relative;
        display: inline-block;
    }

    .dropdown-status .dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        display: none;
    }

    .dropdown-status:hover .dropdown-menu {
        display: block;
    }

    .status-filter {
        cursor: pointer;
        user-select: none;
    }

    .search-bar {
        margin-bottom: 15px;
    }
</style>

<div class="container-fluid">
    <!-- Search -->
    <div class="row align-items-center mb-3">
        <div class="col-md-6 search-bar">
            <input type="text" id="searchInput" class="form-control" placeholder="Cari berdasarkan judul...">
        </div>
    </div>

    <!-- Pesan Notifikasi -->
    <?php if (!empty($pesan)) : ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '<?= htmlspecialchars($pesan); ?>',
                timer: 3000,
                showConfirmButton: false
            });
        </script>
    <?php endif; ?>

    <!-- Form Hapus Pengaduan Selesai -->
    <form method="post" id="hapusForm" class="d-flex justify-content-between mb-3">
        <input type="hidden" name="hapus_selesai">
        <button type="button" id="btnHapus" class="btn btn-danger">
            <i class="bi bi-trash"></i> Hapus Pengaduan Selesai
        </button>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul Pengaduan</th>
                    <th>Tanggal Pengaduan</th>
                    <th>
                        <div class="dropdown-status">
                            <span class="status-filter">Status <i class="bi bi-caret-down-fill"></i></span>
                            <ul class="dropdown-menu">
                                <li><button class="dropdown-item" data-status="all">Semua Status</button></li>
                                <li><button class="dropdown-item" data-status="Baru">Baru</button></li>
                                <li><button class="dropdown-item" data-status="Diproses">Diproses</button></li>
                                <li><button class="dropdown-item" data-status="Selesai">Selesai</button></li>
                                <li><button class="dropdown-item" data-status="Ditolak">Ditolak</button></li>
                                <li><button class="dropdown-item" data-status="Revisi">Revisi</button></li>
                            </ul>
                        </div>
                    </th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="pengaduanTable">
                <?php if (!empty($pengaduan)) : ?>
                    <?php foreach ($pengaduan as $index => $row) : ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><?= htmlspecialchars($row['judul']); ?></td>
                            <td><?= htmlspecialchars($row['tanggal_pengaduan']); ?></td>
                            <td>
                                <?php
                                $badgeClass = match ($row['status']) {
                                    'Baru' => 'bg-warning',
                                    'Diproses' => 'bg-info',
                                    'Selesai' => 'bg-success',
                                    'Ditolak' => 'bg-danger',
                                    'Revisi' => 'bg-secondary',
                                    default => 'bg-light',
                                };
                                ?>
                                <span class="badge <?= $badgeClass; ?>">
                                    <?= htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?page=detailPengaduan&id_pengaduan=<?= $row['id_pengaduan']; ?>"
                                    class="btn btn-sm btn-primary">Lihat Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data pengaduan</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Search Functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#pengaduanTable tr');
        rows.forEach(row => {
            const title = row.cells[1].textContent.toLowerCase();
            row.style.display = title.includes(searchTerm) ? '' : 'none';
        });
    });

    // Filter Status
    document.querySelectorAll('.dropdown-item').forEach(button => {
        button.addEventListener('click', function() {
            const selectedStatus = this.getAttribute('data-status');
            const rows = document.querySelectorAll('#pengaduanTable tr');
            rows.forEach(row => {
                const status = row.cells[3].textContent.toLowerCase();
                row.style.display =
                    selectedStatus === 'all' || status === selectedStatus ? '' : 'none';
            });
        });
    });
    document.getElementById('btnHapus').addEventListener('click', function() {
        Swal.fire({
            title: 'Konfirmasi',
            text: "Yakin ingin menghapus semua pengaduan dengan status 'Selesai'?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('hapusForm').submit();
            }
        });
    });
</script>


<!-- Bootstrap Icon -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">