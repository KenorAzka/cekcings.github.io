<?php
session_start();
// Menghapus semua session yang terdaftar
session_unset();
// Menghancurkan session aktif
session_destroy();

// Melempar user kembali ke halaman utama/login setelah sukses logout
header("Location: ../loginNregist.php");
exit();
