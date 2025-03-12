<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm-password']);

    $errors = [];
    if (empty($username)) {
        $errors[] = "Username harus diisi.";
    }
    if (empty($password)) {
        $errors[] = "Password harus diisi.";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "Password dan Konfirmasi Password tidak cocok.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM user WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'Username sudah digunakan.']);
                exit();
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO user (username, password, role) VALUES (:username, :password, 'Masyarakat')");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->execute();

                // Simpan pesan keberhasilan ke session
                $_SESSION['success_message'] = 'Registrasi berhasil! Silakan login.';
                echo json_encode(['success' => true, 'redirect' => 'index.php']);
                exit();
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SIPADU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/register.css">
    <link rel="icon" href="assets/NewLogoBapenda-removebg.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="form-bg">
        <div class="form-container">
            <h3 class="title">Sign Up SIPADU</h3>
            <form class="form-horizontal" id="signup-form">
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
                    <span class="password-toggle" onclick="togglePassword('password', 'toggle-icon')">
                        <i id="toggle-icon" class="fa fa-eye"></i>
                    </span>
                </div>
                <div class="form-group">
                    <span class="input-icon"><i class="fa fa-lock"></i></span>
                    <input type="password" name="confirm-password" class="form-control" placeholder="Confirm Password" id="confirm-password" required>
                    <span class="password-toggle" onclick="togglePassword('confirm-password', 'toggle-icon-confirm')">
                        <i id="toggle-icon-confirm" class="fa fa-eye"></i>
                    </span>
                    <span class="forgot">Sudah punya akun? <a style="text-decoration: none;" href="index.php">Sign In</a></span>
                </div>
                <button class="btn signin" type="button" onclick="confirmSignUp()">Sign Up</button>
            </form>
        </div>
    </div>
    <script>
        function togglePassword(inputId, iconId) {
            const passwordField = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
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

        function confirmSignUp() {
            Swal.fire({
                title: 'Konfirmasi Registrasi',
                text: 'Apakah Anda yakin ingin mendaftar?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Daftar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitForm();
                }
            });
        }

        function submitForm() {
            const form = document.getElementById('signup-form');
            const formData = new FormData(form);

            fetch('register.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Registrasi Berhasil',
                            text: 'Anda akan diarahkan ke halaman login.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = data.redirect;
                        });
                    } else {
                        Swal.fire({
                            title: 'Registrasi Gagal',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: 'Terjadi kesalahan saat mengirim data.',
                        icon: 'error'
                    });
                });
        }
    </script>
</body>

</html>