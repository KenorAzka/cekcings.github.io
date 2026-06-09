<?php
session_start();
include __DIR__ . '/koneksi.php';

// Proteksi akses langsung tanpa login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../loginNregist.php");
    exit();
}

// Fungsi helper untuk generate UUID v4 secara manual (karena tipe id_reminder adalah char(36))
function generate_uuid4()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

// Memproses data ketika form di-submit
if (isset($_POST['submit_reminder'])) {
    // Ambil dan bersihkan data inputan mencegah SQL Injection
    $id_wishlist = mysqli_real_escape_string($conn, $_POST['id_wishlist']);
    $hari        = mysqli_real_escape_string($conn, $_POST['hari']);
    $jam         = mysqli_real_escape_string($conn, $_POST['jam']);

    // Validasi data tidak boleh kosong
    if (empty($id_wishlist) || empty($hari) || empty($jam)) {
        echo "<script>
                alert('Semua bidang formulir wajib diisi!');
                window.location.href = '../dashboard.php';
              </script>";
        exit();
    }

    // Generate ID unik berformat UUID
    $id_reminder = generate_uuid4();
    $is_active   = 1; // Default diset aktif langsung

    // Query INSERT menyesuaikan struktur kolom asli: id_reminder, id_wishlist, jam, hari, is_active
    $query_insert = "INSERT INTO reminders (id_reminder, id_wishlist, jam, hari, is_active) 
                     VALUES ('$id_reminder', '$id_wishlist', '$jam', '$hari', '$is_active')";

    if (mysqli_query($conn, $query_insert)) {
        // Berhasil disimpan, alihkan halaman kembali ke dashboard utama
        echo "<script>
                alert('Pengingat menabung berhasil ditambahkan!');
                window.location.href = '../dashboard.php';
              </script>";
    } else {
        // Gagal menyimpan ke database
        echo "<script>
                alert('Gagal menambahkan pengingat: " . mysqli_error($conn) . "');
                window.location.href = '../dashboard.php';
              </script>";
    }
} else {
    // Jika diakses ilegal tanpa submit form
    header("Location: ../dashboard.php");
    exit();
}
