<?php
session_start();
include __DIR__ . '/koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$id_user = $_SESSION['user_id'];

// Mengatur zona waktu agar presisi dengan waktu lokal Indonesia
date_default_timezone_set('Asia/Jakarta');

// Dapatkan nama hari dalam bahasa Indonesia
$hari_inggris = date('l');
$daftar_hari = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
];
$hari_ini = $daftar_hari[$hari_inggris];

// Dapatkan jam menit saat ini (Format HH:MM)
$jam_sekarang = date('H:i');

// Query mencari apakah ada pengingat yang aktif HARI INI dan JAM INI untuk user tersebut
$query = "SELECT r.id_reminder, w.nama_barang 
          FROM reminders r
          JOIN wishlists w ON r.id_wishlist = w.id_wishlist
          WHERE w.id_user = '$id_user' 
            AND r.is_active = 1 
            AND r.hari = '$hari_ini' 
            AND TIME_FORMAT(r.jam, '%H:%i') = '$jam_sekarang'";

$result = mysqli_query($conn, $query);

$notifikasi_kirim = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $notifikasi_kirim[] = [
            'id' => $row['id_reminder'],
            'pesan' => "Waktunya Menabung! Jangan lupa sisihkan uangmu hari ini untuk target: " . $row['nama_barang']
        ];
    }
}

// Kembalikan data dalam bentuk JSON untuk dibaca oleh JavaScript
echo json_encode(['status' => 'success', 'data' => $notifikasi_kirim]);
