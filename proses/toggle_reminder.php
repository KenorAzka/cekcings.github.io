<?php
session_start();
include __DIR__ . '/koneksi.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit();
}

$id_reminder = mysqli_real_escape_string($conn, $_GET['id']);

// Cek status aktif saat ini
$query_status = "SELECT is_active FROM reminders WHERE id_reminder = '$id_reminder'";
$res_status = mysqli_query($conn, $query_status);

if ($res_status && mysqli_num_rows($res_status) > 0) {
    $row = mysqli_fetch_assoc($res_status);
    // Balikkan statusnya (jika 1 jadi 0, jika 0 jadi 1)
    $status_baru = ($row['is_active'] == 1) ? 0 : 1;

    $query_update = "UPDATE reminders SET is_active = $status_baru WHERE id_reminder = '$id_reminder'";
    mysqli_query($conn, $query_update);
}

// Kembalikan ke dashboard
header("Location: ../dashboard.php");
exit();
