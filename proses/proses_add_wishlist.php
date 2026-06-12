<?php
session_start();
include __DIR__ . '/koneksi.php';

// Fungsi UUID v4 Kustom
function generate_uuid()
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_user = $_SESSION['user_id'];
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);

    // --- PROSES PEMBERSIHAN FORMAT RUPIAH ---
    // Ambil string nominal mentah dari input (Contoh: "12.500.000" atau "250.000")
    $harga_raw = $_POST['target_harga'];
    $mingguan_raw = $_POST['target_mingguan'];

    // Bersihkan karakter titik (.) pemisah ribuan agar menjadi angka murni kembali ("12500000")
    $harga_clean = str_replace('.', '', $harga_raw);
    $mingguan_clean = str_replace('.', '', $mingguan_raw);

    // Ubah koma (,) menjadi titik (.) jika user tidak sengaja menginput desimal sen
    $harga_clean = str_replace(',', '.', $harga_clean);
    $mingguan_clean = str_replace(',', '.', $mingguan_clean);

    // Escape string angka yang sudah murni untuk keamanan query SQL
    $target_harga = mysqli_real_escape_string($conn, $harga_clean);
    $target_mingguan = mysqli_real_escape_string($conn, $mingguan_clean);
    // ----------------------------------------

    // Menangkap input tipe_alokasi dari form HTML (harian, mingguan, bulanan, tahunan)
    $tipe_alokasi = mysqli_real_escape_string($conn, $_POST['tipe_alokasi']);

    // Proses Upload File Gambar
    $filename = $_FILES['foto']['name'];
    $target_dir = "../uploads/";

    // Mengubah nama file agar unik agar tidak menimpa file lain di server
    $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
    $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    // Pastikan direktori folder penampung foto sudah dibuat
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
        // Generate kode unik ID Wishlist
        $id_wishlist = generate_uuid();
        $status = "In Progress"; // Default status Enum sesuai rancangan ERD database

        // Menambahkan kolom tipe_alokasi dan nilainya ke dalam query SQL
        $query = "INSERT INTO wishlists (id_wishlist, id_user, nama_barang, target_harga, target_mingguan, foto, status, tipe_alokasi) 
                  VALUES ('$id_wishlist', '$id_user', '$nama_barang', '$target_harga', '$target_mingguan', '$new_filename', '$status', '$tipe_alokasi')";

        if (mysqli_query($conn, $query)) {
            echo "<script>
                    alert('Wishlist Impian Berhasil Ditambahkan!');
                    window.location.href = '../dashboard.php';
                </script>";
        } else {
            echo "Error Database: " . mysqli_error($conn);
        }
    } else {
        echo "<script>alert('Gagal mengunggah foto barang.'); window.history.back();</script>";
    }
}
