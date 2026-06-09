<?php
session_start();
include __DIR__ . '/koneksi.php';

// Import library PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Keamanan dasar: pastikan user sudah login
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Sesi Anda telah berakhir. Silakan login kembali.']);
        exit;
    }

    $id_user = $_SESSION['user_id'];
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);

    // Tarik data profil user pengirim dari database
    $user_query = mysqli_query($conn, "SELECT username, email FROM users WHERE id_user = '$id_user'");
    $user_data = mysqli_fetch_assoc($user_query);

    $nama_pengirim = $user_data['username'] ?? 'Pengguna CekC!ng';
    $email_pengirim = $user_data['email'] ?? 'Tidak Diketahui';

    // Persiapan variabel mandiri untuk template HTML
    $pesan_formatted = nl2br(htmlspecialchars($pesan));
    $tahun_sekarang = date('Y');
    $subject = "📌 [Feedback CekC!ng] Masukan Baru dari " . $nama_pengirim;

    // ------------------------------------------------------------------
    // FORMAT TEMPLATE EMAIL OTOMATIS (HEREDOC)
    // ------------------------------------------------------------------
    $mail_body = <<<EOD
    <div style="background-color: #f4f6f9; padding: 30px; font-family: Arial, sans-serif;">
        <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid #e1e5eb;">
            <div style="background: linear-gradient(to right, #124170, #1C5478); padding: 20px; text-align: center; color: #ffffff;">
                <h2 style="margin: 0; font-size: 22px; letter-spacing: 1px;">CekC!ng Feedback System</h2>
                <p style="margin: 5px 0 0 0; font-size: 13px; opacity: 0.8;">Laporan Masukan Masuk Sistem</p>
            </div>
            <div style="padding: 25px; color: #333333; line-height: 1.6;">
                <p style="margin-top: 0;">Halo Admin,</p>
                <p>Aplikasi <strong>CekC!ng</strong> baru saja menerima entri feedback baru dari pengguna dengan rincian profil sebagai berikut:</p>
                
                <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                    <tr>
                        <td style="padding: 6px 0; font-weight: bold; width: 120px;">Nama Pengirim</td>
                        <td style="padding: 6px 0;">: {$nama_pengirim}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-weight: bold;">Email Akun</td>
                        <td style="padding: 6px 0;">: <a href="mailto:{$email_pengirim}" style="color: #1C5478;">{$email_pengirim}</a></td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-weight: bold;">ID Pengguna</td>
                        <td style="padding: 6px 0;">: {$id_user}</td>
                    </tr>
                </table>
                
                <div style="background-color: #f9fbfd; border-left: 4px solid #67C090; padding: 15px; margin-top: 20px; border-radius: 4px;">
                    <h4 style="margin: 0 0 10px 0; color: #124170;">Isi Pesan / Saran:</h4>
                    <p style="margin: 0; font-style: italic; color: #555555;">" {$pesan_formatted} "</p>
                </div>
            </div>
            <div style="background-color: #f4f6f9; padding: 15px; text-align: center; font-size: 12px; color: #888888; border-top: 1px solid #e1e5eb;">
                <p style="margin: 0;">Email ini dikirim secara otomatis oleh Engine Sistem CekC!ng.</p>
                <p style="margin: 5px 0 0 0;">&copy; {$tahun_sekarang} CekC!ng App &mdash; All Rights Reserved.</p>
            </div>
        </div>
    </div>
EOD;

    // ------------------------------------------------------------------
    // IMPLEMENTASI KREDENSIAL MAILTRAP KAMU KE PHPMAILER
    // ------------------------------------------------------------------
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth   = true;

        // Memasukkan Username & Password milikmu
        $mail->Username   = '9904d7e40cb804';
        $mail->Password   = 'e95d0c3ec524d4';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 2525;

        // Pengaturan Penerima & Pengirim (Simulasi)
        $mail->setFrom('system@cekcing.local', 'CekC!ng System');
        $mail->addAddress('kendhani0@gmail.com');
        $mail->addReplyTo($email_pengirim, $nama_pengirim);

        // Konten Email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $mail_body;

        // Eksekusi kirim ke Mailtrap Sandbox
        $mail->send();

        echo json_encode(['status' => 'success', 'message' => 'Feedback berhasil dikirim ke Mailtrap!']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengirim email: ' . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode akses tidak sah.']);
}
