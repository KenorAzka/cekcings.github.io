<?php
include 'koneksi.php';

// FUNGSI UNTUK MEMBUAT KODE UNIK (UUID v4)
function generate_custom_id()
{
    $prefix = "USR-";
    $date = date("Ymd"); // Format: TahunBulanTanggal (Contoh: 20260516)

    // Membuat 5 karakter string acak alfanumerik yang kuat
    $bytes = random_bytes(3);
    $randomString = substr(bin2hex($bytes), 0, 5);

    // Hasil akhirnya akan seperti: USR-20260516a1b2c
    return $prefix . $date . $randomString;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Validasi: Cek email terlebih dahulu
    $cekEmail = "SELECT email FROM users WHERE email = '$email'";
    $hasilCek = mysqli_query($conn, $cekEmail);

    if (mysqli_num_rows($hasilCek) > 0) {
        echo "<script>
                alert('Email sudah terdaftar!');
                window.location.href = 'loginNregist.php';
            </script>";
    } else {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // 1. GENERATE ID_USER UNIK DI SINI
        $id_user = generate_custom_id();

        // 2. MASUKKAN ID_USER KE DALAM PERINTAH INSERT
        $query = "INSERT INTO users (id_user, username, email, password) VALUES ('$id_user', '$username', '$email', '$passwordHash')";

        if (mysqli_query($conn, $query)) {
            echo "<script>
                    alert('Registrasi Berhasil! Silakan Log In.');
                    window.location.href = 'loginNregist.php'; 
                </script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
