<?php
include __DIR__ . '/koneksi.php';
$id_wishlist = mysqli_real_escape_string($conn, $_GET['id']);

// Bersihkan riwayat tabungan (foreign key) terlebih dahulu agar tidak terjadi error relasi constraint
mysqli_query($conn, "DELETE FROM saving_progress WHERE id_wishlist = '$id_wishlist'");
mysqli_query($conn, "DELETE FROM history WHERE id_wishlist = '$id_wishlist'");

// Hapus entitas utama
$query_del = "DELETE FROM wishlists WHERE id_wishlist = '$id_wishlist'";

if (mysqli_query($conn, $query_del)) {
    echo "<script>alert('Wishlist berhasil dihapus.'); window.location.href='../dashboard.php';</script>";
}
