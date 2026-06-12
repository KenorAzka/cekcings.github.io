<?php
session_start();
include __DIR__ . '/koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$id_user = (int)$_SESSION['user_id']; // Konversi ke integer untuk keamanan

// 1. Atur zona waktu agar sinkron dengan jam di laptop/HP
date_default_timezone_set('Asia/Jakarta');

// 2. Dapatkan nama hari dalam bahasa Indonesia
$hari_inggris = date('l');
$daftar_hari = [
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu',
    'Sunday'    => 'Minggu'
];
$hari_ini = $daftar_hari[$hari_inggris];

// 3. Dapatkan jam menit saat ini (Format HH:MM seperti "22:09")
$jam_sekarang = date('H:i');

// 4. Query dengan error handling yang lebih baik
$query = "SELECT r.id_reminder, w.nama_barang 
          FROM reminders r
          JOIN wishlists w ON r.id_wishlist = w.id_wishlist
          WHERE w.id_user = $id_user 
            AND r.is_active = 1 
            AND r.hari = '$hari_ini' 
            AND TIME_FORMAT(r.jam, '%H:%i') = '$jam_sekarang'";

$result = mysqli_query($conn, $query);

if ($result === false) {
    // Jika query gagal, return error dengan pesan MySQL
    echo json_encode([
        'status' => 'error', 
        'message' => 'Query Error: ' . mysqli_error($conn),
        'debug' => [
            'query' => $query,
            'hari' => $hari_ini,
            'jam' => $jam_sekarang,
            'id_user' => $id_user
        ]
    ]);
    exit();
}

$notifikasi_kirim = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $notifikasi_kirim[] = [
            'id' => $row['id_reminder'],
            'pesan' => "Waktunya Menabung! Jangan lupa sisihkan uangmu hari ini untuk target: " . htmlspecialchars($row['nama_barang'])
        ];
    }
}

// Kembalikan data dalam bentuk JSON
echo json_encode([
    'status' => 'success', 
    'data' => $notifikasi_kirim,
    'debug' => [
        'hari_ini' => $hari_ini,
        'jam_sekarang' => $jam_sekarang,
        'jumlah_reminder' => count($notifikasi_kirim)
    ]
]);
