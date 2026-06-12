<?php
session_start();
include __DIR__ . '/koneksi.php';

function generate_uuid()
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_user = $_SESSION['user_id'];
    $id_wishlist = mysqli_real_escape_string($conn, $_POST['id_wishlist']);
    $jenis = $_POST['jenis_mutasi']; // Pemasukan atau Pengeluaran
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    // --- PROSES PEMBERSIHAN FORMAT RUPIAH ---
    // Ambil string nominal mentah dari input (Contoh: "5.000.000" atau "50.000")
    $jumlah_raw = $_POST['jumlah'];

    // Hapus karakter titik (.) pemisah ribuan agar menjadi angka murni ("5000000")
    $jumlah_clean = str_replace('.', '', $jumlah_raw);

    // Ubah koma (,) menjadi titik (.) jika user tidak sengaja memasukkan desimal sen
    $jumlah_clean = str_replace(',', '.', $jumlah_clean);

    // Konversi hasil pembersihan menjadi tipe data float untuk kalkulasi database
    $nominal = (float)$jumlah_clean;
    // ----------------------------------------

    // Jika pengeluaran/tarik, ubah nilai nominal menjadi minus (-) untuk hitungan SUM database
    if ($jenis == "Pengeluaran") {
        $nominal = -1 * abs($nominal);
        if (empty($keterangan)) $keterangan = "Penarikan Dana";
    } else {
        if (empty($keterangan)) $keterangan = "Setoran Tabungan";
    }

    $id_progress = generate_uuid();
    $id_history = generate_uuid();
    $waktu_sekarang = date('Y-m-d H:i:s');

    // 1. Simpan ke tabel saving_progress
    $query_save = "INSERT INTO saving_progress (id_progress, id_wishlist, jumlah_tabungan, tanggal_setor, keterangan) 
                   VALUES ('$id_progress', '$id_wishlist', '$nominal', '$waktu_sekarang', '$keterangan')";

    // 2. Simpan paralel ke tabel history untuk audit global user log
    $query_hist = "INSERT INTO history (id_history, id_user, jumlah, jenis, label_kategori, tanggal, id_wishlist) 
                   VALUES ('$id_history', '$id_user', '" . abs($nominal) . "', '$jenis', 'Wishlist Tabungan', '$waktu_sekarang', '$id_wishlist')";

    if (mysqli_query($conn, $query_save) && mysqli_query($conn, $query_hist)) {

        // Pengecekan otomatis: Jika total tabungan terkumpul sudah menyamai/melebihi target_harga, update status jadi 'Complete'
        $cek = mysqli_query($conn, "SELECT w.target_harga, SUM(s.jumlah_tabungan) as total FROM wishlists w LEFT JOIN saving_progress s ON w.id_wishlist = s.id_wishlist WHERE w.id_wishlist = '$id_wishlist' GROUP BY w.id_wishlist");
        $r_cek = mysqli_fetch_assoc($cek);
        if ((float)$r_cek['total'] >= (float)$r_cek['target_harga']) {
            mysqli_query($conn, "UPDATE wishlists SET status = 'Complete' WHERE id_wishlist = '$id_wishlist'");
        } else {
            mysqli_query($conn, "UPDATE wishlists SET status = 'In Progress' WHERE id_wishlist = '$id_wishlist'");
        }

        echo "<script>alert('Catatan keuangan berhasil disimpan!'); window.location.href='../detail_wishlist.php?id=$id_wishlist';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
