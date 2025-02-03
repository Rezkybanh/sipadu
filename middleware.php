<?php
session_start(); // Memulai sesi
include 'koneksi.php'; // Sertakan koneksi database

// Fungsi untuk memeriksa akses berdasarkan role
function checkAccess($requiredRole, $redirectPath = '../index.php') {
    // Periksa apakah user sudah login (dengan mengecek session)
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        $_SESSION['error_message'] = 'Anda harus login terlebih dahulu.';
        header("Location: $redirectPath");
        exit();
    }

    // Periksa apakah role user sesuai dengan role yang diperlukan
    if ($_SESSION['role'] !== $requiredRole) {
        $_SESSION['error_message'] = 'Akses ditolak. Role tidak sesuai.';
        header("Location: $redirectPath");
        exit();
    }

    // Jika Anda ingin memverifikasi role user dari database (optional)
    global $pdo; // Menggunakan koneksi PDO yang sudah ada

    // Verifikasi dari database jika diperlukan
    $stmt = $pdo->prepare("SELECT role FROM user WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || $user['role'] !== $requiredRole) {
        $_SESSION['error_message'] = 'Akses ditolak. Role tidak sesuai.';
        header("Location: $redirectPath");
        exit();
    }
}
?>
