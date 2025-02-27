<?php
include '../koneksi.php'; // Memastikan koneksi database tersedia
include '../middleware.php'; // Memastikan akses berdasarkan role

checkAccess('Admin');

// Tentukan halaman default
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

if (isset($_POST['logout'])) {
    // Menghapus semua data sesi
    session_unset();

    // Menghancurkan sesi
    session_destroy();

    // Arahkan kembali ke halaman utama (index.php)
    header('Location: ../index.php');
    exit;
}

?>
<style>
    .nav_logo-img {
        width: 28px;
        /* Sesuaikan ukuran gambar */
        height: 28px;
        /* Sesuaikan ukuran gambar */
        border-radius: 50%;
        /* Membuat gambar menjadi bulat */
        object-fit: cover;
        /* Agar gambar tidak terdistorsi */
    }
</style>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS9U3kAT8-QOWyzlekr4HzYrxU9OVFlxU89rA&s" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../style/index.css">
    <title>SIPADU</title>
</head>

<body id="body-pd">
    <header class="header" id="header">
        <div class="header_toggle"> <i class='bx bx-menu' id="header-toggle"></i> </div>
        <div class="header_img">
            <img src="../assets/NewLogoBapenda-removebg.png" alt="">
        </div>
    </header>

    <div class="l-navbar" id="nav-bar">
        <nav class="nav">
            <div>
                <a href="index.php" class="nav_logo">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSHHyRCv_w7HxPrGeYgNW54OT3KfJlsW5RJvIKiPXyftXGnwqpBZ-DGQjLL6WZhancUcuA&usqp=CAU"
                        alt="Logo" class="nav_logo-img">
                    <span class="nav_logo-name">SIPADU</span>
                </a>

                <div class="nav_list">
                    <a href="index.php?page=dashboard" class="nav_link <?= $page == 'dashboard' ? 'active' : '' ?>">
                        <i class='bx bx-tachometer nav_icon'></i>
                        <span class="nav_name">Dashboard</span>
                    </a>
                    <a href="index.php?page=kelolaPengguna" class="nav_link <?= $page == 'kelolaPengguna' ? 'active' : '' ?>">
                        <i class='bx bx-user-check nav_icon'></i>
                        <span class="nav_name">Kelola Pengguna</span>
                    </a>
                    <a href="index.php?page=kelolaPengaduan" class="nav_link <?= $page == 'kelolaPengaduan' ? 'active' : '' ?>">
                        <i class='bx bx-edit-alt nav_icon'></i>
                        <span class="nav_name">Kelola Pengaduan</span>
                    </a>
                    <a href="index.php?page=uploadBerita" class="nav_link <?= $page == 'uploadBerita' ? 'active' : '' ?>">
                        <i class='bx bx-news nav_icon'></i>
                        <span class="nav_name">Upload Berita</span>
                    </a>
                    <a href="index.php?page=kelolaBerita" class="nav_link <?= $page == 'kelolaBerita' ? 'active' : '' ?>">
                        <i class='bx bx-edit-alt nav_icon'></i>
                        <span class="nav_name">Kelola Berita</span>
                    </a>
                    <a href="#" class="nav_link" id="logoutLink">
                        <i class='bx bx-log-out nav_icon'></i>
                        <span class="nav_name">Logout</span>
                    </a>

                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    <script>
                        document.getElementById('logoutLink').addEventListener('click', function(e) {
                            e.preventDefault(); // Mencegah pengalihan langsung

                            // Tampilkan konfirmasi SweetAlert2
                            Swal.fire({
                                title: 'Apakah Anda yakin ingin logout?',
                                text: "Anda akan keluar dari akun ini!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Logout',
                                cancelButtonText: 'Batal',
                                reverseButtons: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Kirim form logout
                                    var form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = ''; // Kirim ke file yang sama
                                    var input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = 'logout';
                                    form.appendChild(input);
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            });
                        });
                    </script>
                </div>
            </div>
        </nav>
    </div>

    <!-- Container Main -->
    <main class="height-100 bg-light">
        <div class="container mt-5">
            <?php
            // Include halaman berdasarkan parameter ?page=
            if ($page == 'kelolaPengguna') {
                include 'kelolaPengguna.php';
            } elseif ($page == 'unduhLapMasuk') {
                include 'unduhLapMasuk.php';
            } elseif ($page == 'unduhLapSelesai') {
                include 'unduhLapSelesai.php';
            } elseif ($page == 'kelolaPengaduan') {
                include 'kelolaPengaduan.php';
            } elseif ($page == 'dashboard') {
                include 'dashboard.php';
            } elseif ($page == 'unduhLapProses') {
                include 'unduhLapProses.php';
            } elseif ($page == 'tambahUser') {
                include 'tambahUser.php';
            } elseif ($page == 'detailPengaduan') {
                include 'detailPengaduan.php';
            } elseif ($page == 'uploadBerita') {
                include 'uploadBerita.php';
            } elseif ($page == 'kelolaBerita') {
                include 'kelolaBerita.php';
            } elseif ($page == 'detailKelolaBerita') {
                include 'detailKelolaBerita.php';
            } else {
                echo "<div class='alert alert-danger'>Halaman tidak ditemukan!</div>";
            }
            ?>
        </div>
    </main>

    <!-- Include JS Files -->
    <script src="../js/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
</body>

</html>