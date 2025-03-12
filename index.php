<?php
include 'koneksi.php';
session_start(); // Memulai sesi untuk mengakses $_SESSION

// Cek apakah ada pesan error dari session
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Menghapus pesan error setelah ditampilkan
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            // Cek username di database
            $stmt = $pdo->prepare("SELECT id, password, role FROM user WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect berdasarkan role
                    if ($user['role'] === 'Admin') {
                        header("Location: admin/");
                    } elseif ($user['role'] === 'Petugas') {
                        header("Location: petugas/");
                    } elseif ($user['role'] === 'Masyarakat') {
                        header("Location: masyarakat/");
                    }
                    exit();
                } else {
                    $error = "Password salah.";
                }
            } else {
                $error = "Username tidak ditemukan.";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    } else {
        $error = "Username dan password harus diisi.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - SIPADU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/indexLogin.css">
    <link rel="icon" href="assets/NewLogoBapenda-removebg.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="form-bg">
    <div class="form-container">
        <h2 class="title">Sign In SIPADU</h2>

        <?php if (isset($error)): ?>
            <script>
                Swal.fire({
                    title: 'Login Gagal',
                    text: '<?php echo $error; ?>',
                    icon: 'error',
                });
            </script>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <script>
                Swal.fire({
                    title: 'Akses Ditolak',
                    text: '<?php echo $error_message; ?>',
                    icon: 'error',
                });
            </script>
        <?php endif; ?>

        <form class="form-horizontal" method="POST">
            <div class="form-icon">
                <img src="assets/NewLogoBapenda.png" alt="User Icon">
            </div>
            <div class="form-group">
                <span class="input-icon"><i class="fa fa-user"></i></span>
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="form-group">
                <span class="input-icon"><i class="fa fa-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Password" id="password" required>
                <span class="password-toggle" onclick="togglePassword()">
                    <i id="toggle-icon" class="fa fa-eye"></i>
                </span>
                <span class="forgot">Belum punya akun? <a style="text-decoration: none;" href="register.php">Sign Up</a></span>
            </div>
            <button class="btn signin" type="submit">Sign In</button>
        </form>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('toggle-icon');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
</script>
</body>
</html>
