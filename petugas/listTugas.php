<?php
try {
    // Pastikan user sudah login
    $id_user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if (!$id_user) {
        die("Error: User tidak terautentikasi.");
    }

    // Cek apakah user yang login adalah petugas
    $queryRole = "SELECT role FROM user WHERE id = :id_user";
    $stmtRole = $pdo->prepare($queryRole);
    $stmtRole->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmtRole->execute();
    $userRole = $stmtRole->fetch(PDO::FETCH_ASSOC);

    if (!$userRole || $userRole['role'] !== 'Petugas') {
        die("Error: Anda bukan petugas.");
    }

    // Query untuk mengambil data pengaduan sesuai dengan id_petugas yang login
    $query = "SELECT id_pengaduan, judul, tanggal_pengaduan, status 
              FROM pengaduan 
              WHERE id_petugas = :id_user 
              ORDER BY tanggal_pengaduan DESC";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch semua data
    $pengaduan = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
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
    <div class="col-md-3">
        <select id="statusFilter" class="form-control"> 
            <option value="all">Semua Status Pengaduan</option>
            <option value="Baru">Baru</option>
            <option value="Diproses">Diproses</option>
            <option value="Selesai">Selesai</option>
            <option value="Ditolak">Ditolak</option>
            <option value="Revisi">Revisi</option>
        </select>
    </div>


    <!-- Table -->
    <div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Pengaduan</th>
                <th>Tanggal Pengaduan</th>
                <th>Status</th>
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
                        <td><?= htmlspecialchars($row['status']); ?></td>
                        <td>
                            <a href="index.php?page=detailTugas&id_pengaduan=<?= $row['id_pengaduan']; ?>" 
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
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#pengaduanTable tr');
        rows.forEach(row => {
            const title = row.cells[1].textContent.toLowerCase();
            row.style.display = title.includes(searchTerm) ? '' : 'none';
        });
    });

    // Filter Status
    document.getElementById('statusFilter').addEventListener('change', function() {
        const selectedStatus = this.value.toLowerCase();
        document.querySelectorAll('#pengaduanTable tr').forEach(row => {
            const status = row.cells[3].textContent.trim().toLowerCase();
            row.style.display = selectedStatus === 'all' || status === selectedStatus ? '' : 'none';
        });
    });
</script>

<!-- Bootstrap Icon -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
