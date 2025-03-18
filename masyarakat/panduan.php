<div class="container mt-5">
    <h2 class="text-center text-primary">Panduan Tata Cara Pengaduan</h2>
    <p class="lead text-center">Ikuti langkah-langkah berikut untuk mengajukan pengaduan Anda secara mudah.</p>

    <div class="steps">
        <div class="step mb-4">
            <div class="step-icon">
                <i class="bx bx-edit-alt"></i>
            </div>
            <div class="step-content">
                <h4>Langkah 1: Isi Form Pengaduan</h4>
                <p>Isi form pengaduan dengan informasi yang lengkap, termasuk nama pengaduan dan deskripsi masalah.</p>
            </div>
        </div>

        <div class="step mb-4">
            <div class="step-icon">
                <i class="bx bx-file"></i>
            </div>
            <div class="step-content">
                <h4>Langkah 2: Lampirkan Dokumen Pendukung</h4>
                <p>Jika ada dokumen pendukung dengan format PDF, lampirkan file tersebut di form pengaduan.</p>
            </div>
        </div>

        <div class="step mb-4">
            <div class="step-icon">
                <i class="bx bx-send"></i>
            </div>
            <div class="step-content">
                <h4>Langkah 3: Kirim Pengaduan</h4>
                <p>Setelah semua data terisi, klik tombol kirim untuk mengajukan pengaduan Anda ke pihak terkait.</p>
            </div>
        </div>

        <div class="step mb-4">
            <div class="step-icon">
                <i class="bx bx-check"></i>
            </div>
            <div class="step-content">
                <h4>Langkah 4: Tunggu Konfirmasi Dan Lihat Progres</h4>
                <p>Setelah pengaduan dikirim, Anda akan menerima konfirmasi terkait status pengaduan anda di bagian progres.</p>
            </div>
        </div>
        
    <div class="text-center mt-4">
        <a href="index.php?page=pengaduan" class="btn btn-primary btn-lg responsive-btn">Ajukan Pengaduan</a>
        <a href="../pdf/FORMAT LAMPIRAN PENDUKUNG.pdf" class="btn btn-secondary btn-lg ms-3 responsive-btn" download>Download Template Laporan</a>
    </div>
    </div>




</div>

<style>
    .steps {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .step {
        display: flex;
        align-items: center;
        padding: 15px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        flex-direction: row;
        /* Default: ikon di samping teks */
    }

    .step-icon {
        font-size: 40px;
        background-color: #007bff;
        color: white;
        padding: 15px;
        border-radius: 50%;
        margin-right: 20px;
    }

    .step-content h4 {
        font-size: 1.2rem;
        color: #007bff;
    }

    .step-content p {
        font-size: 1rem;
        color: #555;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    /* Media Query untuk layar kecil */
    @media (max-width: 767px) {
        .step {
            flex-direction: column;
            /* Mengubah arah flex menjadi kolom pada layar kecil */
            align-items: center;
            /* Menyelaraskan elemen secara vertikal */
        }

        .step-icon {
            font-size: 30px;
            /* Mengurangi ukuran ikon pada layar kecil */
            margin-bottom: 10px;
            /* Memberikan jarak antara ikon dan teks */
        }

        .step-content h4 {
            font-size: 1rem;
            /* Mengurangi ukuran font h4 pada layar kecil */
        }

        .step-content p {
            font-size: 0.9rem;
            /* Mengurangi ukuran font paragraf pada layar kecil */
        }

        .btn-primary {
            font-size: 0.9rem;
            /* Mengurangi ukuran font tombol pada layar kecil */
        }
    }

     /* Default style untuk tombol */
     .responsive-btn {
            font-size: 1rem;
            /* Ukuran font default */
            padding: 10px 20px;
            /* Padding default */
        }

        /* Responsif untuk layar kecil */
        @media (max-width: 767px) {
            .responsive-btn {
                font-size: 0.85rem;
                /* Mengurangi ukuran font */
                padding: 8px 15px;
                margin-bottom: 10px;
                /* Memberikan jarak antar tombol */
            }

            .responsive-btn+.responsive-btn {
                margin-left: 0;
                /* Hilangkan margin kiri tombol kedua */
            }
        }
</style>