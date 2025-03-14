<?php   
ob_start();
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $field = $_POST['field'] ?? null;
    $value = $_POST['value'] ?? null;

    if ($id && $field && $value !== null) {
        if ($field === 'password') {
            $value = password_hash($value, PASSWORD_DEFAULT);
        }

        $stmt = $pdo->prepare("UPDATE user SET $field = :value WHERE id = :id");
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo "<script>window.location.href='index.php?page=kelolaPengguna';</script>";
            exit;
        } else {
            echo "Gagal memperbarui data.";
        }
    }
}

$stmt = $pdo->query("SELECT * FROM user ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
$loggedInUserId = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'] ?? null;

    if ($id) {
        // Cek apakah ID yang akan dihapus adalah ID pengguna yang sedang login
        if ($id == $loggedInUserId) {
            // Menampilkan peringatan jika pengguna mencoba menghapus dirinya sendiri
            echo "<script>
                    Swal.fire({
                        title: 'Tidak Bisa Menghapus Diri Sendiri!',
                        text: 'Anda tidak bisa menghapus akun Anda sendiri.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                  </script>";
        } else {
            // Cek apakah pengguna memiliki pengaduan yang terkait
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM pengaduan WHERE id = :id"); 
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            $count = $checkStmt->fetchColumn();

            if ($count > 0) {
                // Menampilkan peringatan jika pengguna memiliki pengaduan yang terkait
                echo "<script>
                        Swal.fire({
                            title: 'Gagal!',
                            text: 'Anda tidak bisa menghapus pengguna yang sedang melakukan pengaduan.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                      </script>";
            } else {
                // Menyiapkan query untuk menghapus pengguna berdasarkan ID
                $stmt = $pdo->prepare("DELETE FROM user WHERE id = :id");
                $stmt->bindParam(':id', $id);

                if ($stmt->execute()) {
                    echo "<script>
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'User  berhasil dihapus.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(function() {
                                window.location.href = 'index.php?page=kelolaPengguna'; 
                            });
                          </script>";
                } else {
                    echo "<script>
                            Swal.fire({
                                title: 'Gagal!',
                                text: 'Gagal menghapus user.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                          </script>";
                }
            }
        }
    }
}
?>
<div class="container mt-5">
    <div class="d-flex justify-content-between mb-3">
        <input type="text" class="form-control w-50" id="searchInput" placeholder="Cari user...">
        <a href="index.php?page=tambahUser" class="btn btn-primary">Tambah User</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="userTable">
            <thead class="table-primary">
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($users as $user): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td>
                            <form method="POST">
                                <input class="form-control" type="hidden" name="id" value="<?= $user['id']; ?>">
                                <input class="form-control" type="hidden" name="field" value="username">
                                <input class="form-control" type="text" name="value" value="<?= htmlspecialchars($user['username']); ?>" onblur="this.form.submit();">
                            </form>
                        </td>
                        <td>
                            <form method="POST">
                                <input class=form-control type="hidden" name="id" value="<?= $user['id']; ?>">
                                <input class=form-control type="hidden" name="field" value="password">
                                <input class=form-control type="password" name="value" placeholder="************" onblur="this.form.submit();">
                            </form>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="id" value="<?= $user['id']; ?>">
                                <input type="hidden" name="field" value="role">
                                <select class="form-select" name="value" onchange="this.form.submit();">
                                    <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="Petugas" <?= $user['role'] === 'Petugas' ? 'selected' : ''; ?>>Petugas</option>
                                    <option value="Masyarakat" <?= $user['role'] === 'Masyarakat' ? 'selected' : ''; ?>>Masyarakat</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="id" value="<?= $user['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .editable:hover,
    .editable-dropdown:hover {
        cursor: pointer;
        background-color: #f8f9fa;
    }
    .table-primary {
        background-color: #add8e6 !important;
    }

    .editable input,
    .editable-dropdown select {
        width: 100%;
        border: none;
        padding: 0;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Menangani klik tombol hapus
        const deleteButtons = document.querySelectorAll('.delete-button');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('form'); // Mendapatkan form terkait
                const userId = form.querySelector('input[name="id"]').value; // Mendapatkan ID pengguna

                // SweetAlert konfirmasi
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data ini akan dihapus dan tidak dapat dipulihkan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika dikonfirmasi, kirim form untuk hapus
                        form.submit();
                    }
                });
            });
        });
    });
</script>

<script>
   searchInput.addEventListener('input', function() {
    const searchValue = searchInput.value.toLowerCase();
    const rows = userTable.getElementsByTagName('tr');

    for (let row of rows) {
        let textContent = row.textContent.toLowerCase();

        // Ambil nilai dari input dan select di dalam row
        row.querySelectorAll('input, select').forEach(input => {
            textContent += ' ' + input.value.toLowerCase();
        });

        row.style.display = textContent.includes(searchValue) ? '' : 'none';
    }
});

</script>