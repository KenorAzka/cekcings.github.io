<?php
session_start();
include __DIR__ . '/koneksi.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if (isset($_POST['submit_forgot'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Cek apakah email terdaftar di tabel users
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Generate Token unik dan aman
        $token = bin2hex(random_bytes(32));
        // Set masa berlaku token (30 menit)
        $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        // Simpan token ke database agar bisa diverifikasi nanti
        $update_query = "UPDATE users SET reset_token = '$token', token_expires_at = '$expires' WHERE email = '$email'";
        mysqli_query($conn, $update_query);

        $mail = new PHPMailer(true);

        try {
            // --- KONFIGURASI SMTP MAILTRAP MU ---
            $mail->isSMTP();
            $mail->Host       = 'sandbox.smtp.mailtrap.io'; 
            $mail->SMTPAuth   = true;
            $mail->Port       = 2525;                       
            $mail->Username   = '9904d7e40cb804'; // Username Mailtrap kamu          
            $mail->Password   = 'e95d0c3ec524d4'; // Masukkan password asli Mailtrap kamu (bukan yang disensor bintang)
            // ----------------------------------

            // Pengirim & Penerima (Bebas diisi karena simulasi)
            $mail->setFrom('support@cekcing.my.id', 'CekC!ng Support');
            $mail->addAddress($email);

            // Tautan Reset Password
            $reset_link = "http://localhost/cekcing/reset_password.php?token=" . $token;

            $mail->isHTML(true);
            $mail->Subject = 'Reset Password Akun CekC!ng';
            $mail->Body    = "<h3>Halo,</h3>
                              <p>Kami menerima permintaan untuk meriset password akun CekC!ng Anda.</p>
                              <p>Silakan klik tombol di bawah ini untuk membuat password baru:</p>
                              <br>
                              <a href='$reset_link' style='padding: 12px 20px; background: #a3e635; color: #124170; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Reset Password</a>
                              <br><br>
                              <p>Tautan ini hanya berlaku selama 30 menit.</p>";

            $mail->send();
            echo "<script>alert('Link reset password telah dikirim ke email simulasi Anda. Silakan cek dashboard Mailtrap!'); window.location.href='../loginNregist.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Gagal mengirim email. Error: {$mail->ErrorInfo}'); window.history.back();</script>";
        }
    }
}
?>