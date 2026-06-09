<?php
require_once __DIR__ . '/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_wishlist = mysqli_real_escape_string($conn, $_POST['id_wishlist']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $harga = mysqli_real_escape_string($conn, $_POST['target_harga']);
    $mingguan = mysqli_real_escape_string($conn, $_POST['target_mingguan']);

    if ($_FILES['foto']['name'] != "") {
        // Jika user mengunggah foto baru pengganti
        $filename = $_FILES['foto']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $new_name = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . "/../uploads/" . $new_name);

        $query = "UPDATE wishlists SET nama_barang='$nama', target_harga='$harga', target_mingguan='$mingguan', foto='$new_name' WHERE id_wishlist='$id_wishlist'";
    } else {
        // Jika memakai foto lama
        $query = "UPDATE wishlists SET nama_barang='$nama', target_harga='$harga', target_mingguan='$mingguan' WHERE id_wishlist='$id_wishlist'";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: ../detail_wishlist.php?id=" . $id_wishlist);
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
