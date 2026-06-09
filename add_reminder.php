<?php
session_start();
include 'proses/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: loginNregist.php");
    exit();
}

$id_user = $_SESSION['user_id'];

// Mengambil daftar wishlist aktif milik user untuk dijadikan pilihan opsi select form
$query_opt = "SELECT id_wishlist, nama_barang FROM wishlists WHERE id_user = '$id_user'";
$tampil_opt = mysqli_query($conn, $query_opt);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Pengingat Baru - CekC!ng</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, #124170, #1C5478, #67C090);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
        }

        .form-container h2 {
            margin-top: 0;
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .form-group select,
        .form-group input {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: white;
            color: #333;
            font-size: 0.9rem;
            font-family: inherit;
        }

        .form-group select:focus,
        .form-group input:focus {
            box-shadow: 0 0 0 3px rgba(103, 192, 144, 0.4);

        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-submit {
            background: #67C090;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            flex: 2;
            font-family: inherit;
        }

        .btn-submit:hover {
            background: #56b081;
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-decoration: none;
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 0.9rem;
            flex: 1;
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h2><i class="fa-solid fa-bell" style="color: #a3e635;"></i> Atur Pengingat Baru</h2>
        <p style="font-size: 0.8rem; opacity: 0.6; margin-bottom: 25px; margin-top: -5px;">Pilih target tabungan dan tentukan jadwal alarm notifikasimu.</p>

        <form action="proses/tambah_reminder.php" method="POST">

            <div class="form-group">
                <label for="id_wishlist">Pilih Impian / Wishlist</label>
                <select name="id_wishlist" id="id_wishlist" required>
                    <option value="" disabled selected>-- Pilih Wishlist --</option>
                    <?php while ($row = mysqli_fetch_assoc($tampil_opt)): ?>
                        <option value="<?php echo $row['id_wishlist']; ?>">
                            <?php echo htmlspecialchars($row['nama_barang']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="hari">Hari Mengingatkan</label>
                <select name="hari" id="hari" required>
                    <option value="" disabled selected>-- Pilih Hari --</option>
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jumat</option>
                    <option value="Sabtu">Sabtu</option>
                    <option value="Minggu">Minggu</option>
                </select>
            </div>

            <div class="form-group">
                <label for="jam">Waktu / Jam</label>
                <input type="time" name="jam" id="jam" required>
            </div>

            <div class="btn-group">
                <a href="dashboard.php" class="btn-cancel">Batal</a>
                <button type="submit" name="submit_reminder" class="btn-submit">Simpan Pengingat</button>
            </div>

        </form>
    </div>

</body>

</html>