<?php
include 'proses/koneksi.php';

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    $current_time = date('Y-m-d H:i:s');

    // Validasi apakah token cocok dan belum kedaluwarsa
    $query = "SELECT * FROM users WHERE reset_token = '$token' AND token_expires_at > '$current_time'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 0) {
        die("<h3 style='text-align:center; margin-top:50px; font-family:sans-serif;'>Link tidak valid atau sudah kedaluwarsa! Silakan minta link baru.</h3>");
    }
} else {
    header("Location: loginNregist.php");
    exit();
}

// Proses update ke database saat password baru di-submit
if (isset($_POST['update_password'])) {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Konfirmasi password tidak cocok!');</script>";
    } else {
        // Enkripsi password baru dengan BCRYPT
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update password baru dan hapus tokennya (set jadi NULL) agar tidak bisa dipakai lagi
        $update_query = "UPDATE users SET password = '$hashed_password', reset_token = NULL, token_expires_at = NULL WHERE reset_token = '$token'";

        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('Password berhasil diperbarui! Silakan login.'); window.location.href='loginNregist.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui password. Coba lagi.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CekC!ng</title>
    <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <div class="form-login" style="display: flex;">
            <h1>New Password</h1>
            <form action="" method="POST">
                <div class="isi_form">
                    <input type="password" name="password" placeholder="Password Baru" required minlength="6">
                </div>
                <div class="isi_form">
                    <input type="password" name="confirm_password" placeholder="Konfirmasi Password Baru" required>
                </div>
                <button type="submit" name="update_password">Simpan Password</button>
            </form>
        </div>
    </div>
</body>

</html>