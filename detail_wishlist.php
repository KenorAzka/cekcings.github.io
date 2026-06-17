<?php
session_start();
include 'proses/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: loginNregist.php");
    exit();
}

$id_wishlist = mysqli_real_escape_string($conn, $_GET['id']);

// 1. Ambil data spesifik wishlist beserta total akumulasi tabungannya
$query = "SELECT w.*, COALESCE(SUM(s.jumlah_tabungan), 0) AS total_terkumpul 
          FROM wishlists w 
          LEFT JOIN saving_progress s ON w.id_wishlist = s.id_wishlist
          WHERE w.id_wishlist = '$id_wishlist' GROUP BY w.id_wishlist";
$eksekusi = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($eksekusi);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='dashboard.php';</script>";
    exit();
}

// Perhitungan Matematis Keuangan
$target_harga    = (float)$data['target_harga'];
$target_mingguan = (float)$data['target_mingguan']; // Nominal periodik alokasi tabungan
$collected       = (float)$data['total_terkumpul'];
$lack            = $target_harga - $collected;
if ($lack < 0) $lack = 0;

$progress_percent = ($target_harga > 0) ? round(($collected / $target_harga) * 100) : 0;
if ($progress_percent > 100) $progress_percent = 100;

// Perhitungan sisa periode waktu berdasarkan target alokasi
$time_left = ($target_mingguan > 0) ? ceil($lack / $target_mingguan) : 0;

// Penerjemah Tipe Alokasi dari Database untuk Label Teks
$tipe = $data['tipe_alokasi'];
if ($tipe == 'harian') {
    $label_period = 'Per Day';
    $label_time   = ' More Days';
} elseif ($tipe == 'bulanan') {
    $label_period = 'Per Month';
    $label_time   = ' More Months';
} elseif ($tipe == 'tahunan') {
    $label_period = 'Per Year';
    $label_time   = ' More Years';
} else {
    $label_period = 'Per Week';
    $label_time   = ' More Weeks';
}

// 2. Ambil riwayat mutasi tabungan dari tabel saving_progress
$query_history = "SELECT * FROM saving_progress WHERE id_wishlist = '$id_wishlist' ORDER BY tanggal_setor DESC";
$history_result = mysqli_query($conn, $query_history);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CekC!ng</title>
    <link rel="stylesheet" href="dashboard_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #124170, #1C5478, #67C090);
            background-repeat: no-repeat;
            color: #333;
            height: 100vh;
            display: flex;
            justify-content: center;
            overflow: hidden;
        }

        .detail-wrapper {
            width: 100%;
            max-width: 1000px;
            background: white;
            padding: 30px;
            height: fit-content;
            margin: 40px 0;
            border-radius: 20px;
            overflow-y: auto;
        }

        .top-action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .top-action-bar h2 {
            color: black;
            text-align: center;
        }

        /* Tombol bulat back & aksi */
        .btn-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            border: none;
            cursor: pointer;
        }

        .btn-back {
            background: linear-gradient(135deg, #124170, #67C090);
        }

        .action-right-box {
            display: flex;
            gap: 10px;
            background: linear-gradient(135deg, #124170, #67C090);
            padding: 5px 15px;
            border-radius: 25px;
            align-items: center;
        }

        .action-right-box a,
        .action-right-box button {
            color: white;
            background: none;
            border: none;
            font-size: 1.1rem;
            cursor: pointer;
            text-decoration: none;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }

        /* Sisi Kiri: Informasi Main Card */
        .left-card {
            background: #174A7C;
            border-radius: 24px;
            padding: 0;
            overflow: hidden;
            ;
            display: flex;
            flex-direction: column;
            color: white;
            height: 100%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .left-card img {
            width: 100%;
            height: 280px;
            object-fit: cover;
            background: white;
        }

        .left-card .info-padding {
            padding: 25px;
        }

        .left-card h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 25px;
        }

        /* Sisi Kanan: Keuangan & Input */
        .right-section {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .info-strip {
            background: #174A7C;
            border-radius: 16px;
            padding: 15px 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .info-strip.split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-radius: 20px 20px 0 0;
            border-bottom: 2px solid #333;
            text-align: center;
            margin-bottom: 0;
        }

        .collected-text {
            color: #2ecc71;
            font-weight: 700;
            font-size: 1.3rem;
        }

        .lack-text {
            color: #e74c3c;
            font-weight: 700;
            font-size: 1.3rem;
        }

        /* Area Catatan Tabungan History */
        .history-card-box {
            background: linear-gradient(to bottom, #174A7C, #67C090);
            border-radius: 0 0 20px 20px;
            padding: 20px;
            color: white;
            min-height: 200px;
        }

        .history-title-line {
            text-align: center;
            font-weight: 600;
            font-size: 0.9rem;
            border-bottom: 1px solid white;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .history-list {
            max-height: 150px;
            overflow-y: auto;
            list-style: none;
            font-size: 0.85rem;
            padding: 0
        }

        .history-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Form Tambah Saldo */
        .transaction-form {
            background: #174A7C;
            padding: 15px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .transaction-form h4 {
            margin-bottom: 10px;
            font-weight: 600;
            color: white;
            margin-top: 0
        }

        .form-row {
            display: flex;
            gap: 10px;
        }

        .form-row input,
        .form-row select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-family: 'Poppins';
        }

        .form-row input[type="number"] {
            flex-grow: 1;
        }

        .btn-submit-trans {
            background: #67C090;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="detail-wrapper">
        <div class="top-action-bar">
            <a href="dashboard.php" class="btn-circle btn-back"><i class="fa-solid fa-chevron-left"></i></a>
            <h2 style="font-weight: 700;">My Wishlist</h2>
            <div class="action-right-box">
                <a href="edit_wishlist.php?id=<?php echo $id_wishlist; ?>"><i class="fa-solid fa-pen"></i></a>
                <span style="color: rgba(255,255,255,0.4); margin: 0 5px;">|</span>
                <a href="proses/proses_delete_wishlist.php?id=<?php echo $id_wishlist; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus wishlist impian ini?')"><i class="fa-solid fa-trash"></i></a>
            </div>
        </div>

        <div class="detail-grid">

            <div class="left-card">
                <img src="uploads/<?php echo htmlspecialchars($data['foto']); ?>" alt="Foto Barang">
                <div class="info-padding">
                    <h2><?php echo htmlspecialchars($data['nama_barang']); ?></h2>
                    <div class="progress-container" style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 12px; position: relative;">
                        <span style="font-size: 0.8rem; display:block; margin-bottom: 5px;">Progress</span>
                        <div class="progress-bar-bg" style="width: 85%; height: 10px; background: rgba(255,255,255,0.2); border-radius: 5px; overflow:hidden;">
                            <div class="progress-bar-fill" style="width: <?php echo $progress_percent; ?>%; height:100%; background: white;"></div>
                        </div>
                        <span style="position: absolute; right: 15px; top: 25px; font-weight: 700; font-size: 1.1rem;"><?php echo $progress_percent; ?>%</span>

                        <span style="font-size: 0.8rem; display:block; margin-top: 8px; color: rgba(255,255,255,0.8);">
                            <?php echo ($progress_percent >= 100) ? 'Goal Terpenuhi!' : $time_left . $label_time; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="right-section">

                <div class="info-strip">
                    <div>
                        <div style="font-size: 1.2rem; font-weight:700;">Rp<?php echo number_format($target_harga, 0, ',', '.'); ?></div>

                        <div style="font-size: 0.75rem; color: rgba(255,255,255,0.6);">
                            Rp<?php echo number_format($target_mingguan, 0, ',', '.'); ?> <?php echo $label_period; ?>
                        </div>
                    </div>
                    <div style="font-size: 0.8rem; text-align: right; color: rgba(255,255,255,0.8);">
                        Date Created: <?php echo date('d/m/Y', strtotime($data['created_at'])); ?><br>
                        Status: <b><?php echo $data['status']; ?></b>
                    </div>
                </div>

                <div class="transaction-form">
                    <h4>Catat Mutasi Tabungan</h4>
                    <form action="proses/proses_transaksi_saving.php" method="POST">
                        <input type="hidden" name="id_wishlist" value="<?php echo $id_wishlist; ?>">
                        <div class="form-row">
                            <select name="jenis_mutasi" required>
                                <option value="Pemasukan">Setor (Masuk)</option>
                                <option value="Pengeluaran">Tarik (Keluar)</option>
                            </select>
                            <input type="text" id="input_jumlah" name="jumlah" placeholder="Masukkan Nominal Uang (Rp)" oninput="formatRupiah(this)" required> <input type="text" name="keterangan" placeholder="Keterangan opsional">
                            <button type="submit" class="btn-submit-trans">Simpan</button>
                        </div>
                    </form>
                </div>

                <div>
                    <div class="info-strip split">
                        <div>
                            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.7);">Collected</div>
                            <div class="collected-text">Rp<?php echo number_format($collected, 0, ',', '.'); ?></div>
                        </div>
                        <div style="border-left: 1px solid rgba(255,255,255,0.2);">
                            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.7);">Lack</div>
                            <div class="lack-text">Rp<?php echo number_format($lack, 0, ',', '.'); ?></div>
                        </div>
                    </div>

                    <div class="history-card-box">
                        <div class="history-title-line">Savings History</div>
                        <ul class="history-list">
                            <?php
                            if (mysqli_num_rows($history_result) > 0) {
                                while ($h = mysqli_fetch_assoc($history_result)) {
                                    $is_masuk = ((float)$h['jumlah_tabungan'] >= 0);
                                    $tanda = $is_masuk ? "(+)" : "(-)";
                                    $warna_tanda = $is_masuk ? "#2ecc71" : "#e74c3c";
                                    $nominal_abs = abs((float)$h['jumlah_tabungan']);
                            ?>
                                    <li class="history-item">
                                        <span><?php echo date('d M Y H:i', strtotime($h['tanggal_setor'])); ?> - <small><?php echo htmlspecialchars($h['keterangan']); ?></small></span>
                                        <span style="font-weight:600; color: <?php echo $warna_tanda; ?>;"><?php echo $tanda; ?> Rp<?php echo number_format($nominal_abs, 0, ',', '.'); ?></span>
                                    </li>
                            <?php
                                }
                            } else {
                                echo "<li style='text-align:center; padding-top:30px; opacity:0.7;'>Belum ada riwayat transaksi tabungan.</li>";
                            }
                            ?>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function formatRupiah(element) {
            // Ambil value input, hapus semua karakter selain angka
            let value = element.value.replace(/[^,\d]/g, '').toString();
            let split = value.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            // Jika ada ribuan, tambahkan titik sebagai pemisah
            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            // Gabungkan kembali jika ada koma desimal
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;

            // Masukkan kembali hasil format ke dalam input text
            element.value = rupiah;
        }
    </script>

</body>

</html>