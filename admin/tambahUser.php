<?php
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (empty($username) || empty($password) || empty($role)) {
        $error = "Semua field harus diisi.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO user (username, password, role) VALUES (:username, :password, :role)");
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role);

            if ($stmt->execute()) {
                echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'User berhasil ditambahkan!',
                        }).then(() => {
                            window.location.href = 'index.php?page=kelolaPengguna';
                        });
                      </script>";
                exit;
            } else {
                $error = "Gagal menambahkan user.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-5">
    <h2 class="mb-4"><a href="index.php?page=kelolaPengguna"> <i class="fas fa-chevron-left"></i></a> Tambah User</h2>
    <?php if (!empty($error)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: "<?= htmlspecialchars($error); ?>",
            });
        </script>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select class="form-control" id="role" name="role" required>
                <option value="">Pilih Role</option>
                <option value="Admin">Admin</option>
                <option value="Petugas">Petugas</option>
                <option value="Masyarakat">Masyarakat</option>
            </select>
        </div><hr>
        <button type="submit" class="btn btn-primary">Tambah</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
