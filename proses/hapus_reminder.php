<?php
session_start();
include __DIR__ . '/koneksi.php';

// 1. Proteksi keamanan: Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../loginNregist.php");
    exit();
}

// 2. Cek apakah parameter 'id' (id_reminder) dikirimkan melalui URL
if (isset($_GET['id'])) {
    $id_reminder = mysqli_real_escape_string($conn, $_GET['id']);
    $id_user = $_SESSION['user_id'];

    // 3. Validasi Keamanan Berlapis: Pastikan pengingat yang dihapus adalah benar milik user yang sedang login
    // Kita lakukan JOIN ke tabel wishlists untuk mencocokkan id_user
    $check_ownership = "SELECT r.id_reminder FROM reminders r 
                        JOIN wishlists w ON r.id_wishlist = w.id_wishlist 
                        WHERE r.id_reminder = '$id_reminder' AND w.id_user = '$id_user'";
    $result_check = mysqli_query($conn, $check_ownership);

    if (mysqli_num_rows($result_check) > 0) {
        // Jika valid, eksekusi perintah DELETE
        $query_delete = "DELETE FROM reminders WHERE id_reminder = '$id_reminder'";

        if (mysqli_query($conn, $query_delete)) {
            echo "<script>
                    alert('Pengingat berhasil dihapus!');
                    window.location.href = '../dashboard.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus pengingat: " . mysqli_error($conn) . "');
                    window.location.href = '../dashboard.php';
                  </script>";
        }
    } else {
        // Jika user mencoba menghapus ID milik orang lain secara ilegal via URL
        echo "<script>
                alert('Akses ditolak! Anda tidak memiliki hak menghapus pengingat ini.');
                window.location.href = '../dashboard.php';
              </script>";
    }
} else {
    // Jika diakses langsung tanpa membawa ID parameter
    header("Location: ../dashboard.php");
    exit();
}
