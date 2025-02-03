<?php
// Pastikan ob_start() ada di awal file, sebelum ada output apapun
ob_start();

include '../koneksi.php'; // Koneksi ke database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $id = $_POST['id'] ?? null;

    if ($action === 'update' && $id) {
        $field = $_POST['field'];
        $value = $_POST['value'];

        if ($field === 'password') {
            $value = password_hash($value, PASSWORD_DEFAULT); // Hash password
        }

        $stmt = $pdo->prepare("UPDATE user SET $field = :value WHERE id = :id");
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':id', $id);

        echo $stmt->execute() ? "success" : "error";
        exit;
    }
}
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
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM pengaduan WHERE id = :id"); // Ganti id_user dengan user_id
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
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr style="background-color:cornflowerblue;">
                    <th>No</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="userTable">
                <?php
                // Mengambil data pengguna dari database
                $stmt = $pdo->query("SELECT * FROM user ORDER BY id ASC");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $no = 1;
                foreach ($users as $user): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td class="editable" data-field="username" data-id="<?= $user['id']; ?>">
                            <?= htmlspecialchars($user['username']); ?>
                        </td>
                        <td class="editable" data-field="password" data-id="<?= $user['id']; ?>">
                            ********************
                        </td>
                        <td class="editable-dropdown" data-field="role" data-id="<?= $user['id']; ?>">
                            <span>
                                <?= htmlspecialchars($user['role']); ?>
                                <i class="fa fa-angle-down ms-2"></i>
                            </span>
                            <select class="form-control d-none">
                                <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : ''; ?>>Admin<i class="fa fa-angle-down ms-2"></i></option>
                                <option value="Petugas" <?= $user['role'] === 'Petugas' ? 'selected' : ''; ?>>Petugas<i class="fa fa-angle-down ms-2"></i></option>
                                <option value="Masyarakat" <?= $user['role'] === 'Masyarakat' ? 'selected' : ''; ?>>Masyarakat</option>
                            </select>
                        </td>
                        <td>
                            <!-- Tombol Hapus -->
                            <form method="POST" action="" class="delete-form">
                                <input type="hidden" name="id" value="<?= $user['id']; ?>">
                                <input type="hidden" name="action" value="delete"> <!-- Tambahkan aksi hapus -->
                                <button type="button" class="btn btn-danger btn-sm delete-button">Hapus</button>
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
    // Editable text fields
    const userTable = document.getElementById('userTable');
    userTable.addEventListener('click', (e) => {
        if (e.target.classList.contains('editable')) {
            const field = e.target;
            const input = document.createElement('input');
            input.type = 'text';
            input.value = field.textContent.trim();
            input.addEventListener('blur', () => {
                const newValue = input.value;
                const userId = field.getAttribute('data-id');
                const fieldName = field.getAttribute('data-field');

                fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            action: 'update',
                            id: userId,
                            field: fieldName,
                            value: newValue
                        })
                    }).then(response => response.text())
                    .then(response => {
                        if (response === "success") {
                            field.textContent = (fieldName === 'password') ? '********' : newValue;
                        }
                    });

                field.classList.remove('d-none');
            });
            field.textContent = '';
            field.appendChild(input);
            input.focus();
        } else if (e.target.classList.contains('editable-dropdown')) {
            const field = e.target;
            const span = field.querySelector('span');
            const select = field.querySelector('select');
            span.classList.add('d-none');
            select.classList.remove('d-none');
            select.value = span.textContent.trim();
            select.addEventListener('change', () => {
                const newValue = select.value;
                const userId = field.getAttribute('data-id');
                const fieldName = field.getAttribute('data-field');

                fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            action: 'update',
                            id: userId,
                            field: fieldName,
                            value: newValue
                        })
                    }).then(response => response.text())
                    .then(response => {
                        if (response === "success") {
                            span.textContent = newValue;
                        }
                    });

                span.classList.remove('d-none');
                select.classList.add('d-none');
            });
        }
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', () => {
        const searchValue = searchInput.value.toLowerCase();
        Array.from(userTable.rows).forEach(row => {
            const cells = Array.from(row.cells);
            const matches = cells.some(cell => cell.textContent.toLowerCase().includes(searchValue));
            row.style.display = matches ? '' : 'none';
        });
    });
</script>