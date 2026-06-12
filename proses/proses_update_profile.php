<?php
session_start();
include __DIR__ . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../loginNregist.php");
        exit;
    }

    $id_user = $_SESSION['user_id'];
    $username_baru = mysqli_real_escape_string($conn, $_POST['username']);

    // Tarik info record lama untuk pengecekan file
    $check_query = mysqli_query($conn, "SELECT foto_profil FROM users WHERE id_user = '$id_user'");
    $current_data = mysqli_fetch_assoc($check_query);
    $foto_simpan = $current_data['foto_profil'];

    // Validasi apakah user mengunggah file baru
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === 0) {
        $file_tmp = $_FILES['foto_profil']['tmp_name'];
        $file_name = $_FILES['foto_profil']['name'];
        $file_size = $_FILES['foto_profil']['size'];

        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $allowed_ext)) {
            if ($file_size <= 2097152) { // Batasi maksimal ukuran file 2MB

                // Beri nama unik baru menggunakan timestamp agar nama file di server tidak duplikat
                $nama_file_baru = "AVATAR-" . $id_user . "-" . time() . "." . $file_ext;
                $target_dir = "../uploads/profile/";
                $target_file = $target_dir . $nama_file_baru;

                if (move_uploaded_file($file_tmp, $target_file)) {
                    if (!empty($current_data['foto_profil']) && file_exists("../" . $current_data['foto_profil'])) {
                        unlink("../" . $current_data['foto_profil']);
                    }

                    // PERBAIKAN 2: Tambahkan juga 'profile/' untuk jalur yang disimpan ke database
                    $foto_simpan = "uploads/profile/" . $nama_file_baru;
                } else {
                    header("Location: ../profile.php?status=error&msg=Gagal memindahkan file ke server.");
                    exit;
                }
            } else {
                header("Location: ../profile.php?status=error&msg=Ukuran foto terlalu besar. Maksimal 2MB.");
                exit;
            }
        } else {
            header("Location: ../profile.php?status=error&msg=Format file tidak diizinkan. Gunakan JPG/JPEG/PNG.");
            exit;
        }
    }

    // Update query ke database MySQL
    $update_query = "UPDATE users SET username = '$username_baru', foto_profil = '$foto_simpan' WHERE id_user = '$id_user'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['username'] = $username_baru; // Update session global
        header("Location: ../profile.php?status=success&msg=Profil Anda berhasil diperbarui!");
    } else {
        header("Location: ../profile.php?status=error&msg=Gagal menyimpan ke database: " . mysqli_error($conn));
    }
} else {
    header("Location: ../profile.php");
}
