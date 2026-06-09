<?php
session_start();
require_once __DIR__ . '/proses/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Mencari data user berdasarkan email
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);

        // Memverifikasi password inputan dengan password_hash yang ada di database
        if (password_verify($password, $row['password'])) {
            // Membuat session
            $_SESSION['user_id']  = $row['id_user']; // Menggunakan id_user unik Anda
            $_SESSION['username'] = $row['username'];
            $_SESSION['email']    = $row['email'];
            // Alihkan langsung ke dashboard
            header("Location: dashboard.php");
            exit(); // Wajib ada untuk menghentikan baris kode di bawahnya
        } else {
            echo "<script>
                    alert('Password salah!');
                    window.location.href = 'loginNregist.php';
                </script>";
        }
    } else {
        echo "<script>
                alert('Email tidak ditemukan!');
                window.location.href = 'loginNregist.php';
            </script>";
    }
}
