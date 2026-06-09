<?php
session_start();
include 'proses/koneksi.php';

// Proteksi Halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: loginNregist.php");
    exit();
}

// Ambil ID dari URL dan validasi
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id_wishlist = mysqli_real_escape_string($conn, $_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM wishlists WHERE id_wishlist = '$id_wishlist'");
$d = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan
if (!$d) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='dashboard.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Wishlist - <?php echo htmlspecialchars($d['nama_barang']); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            background: linear-gradient(to bottom, #124170, #1C5478, #67C090);
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .split-container {
            display: flex;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px;
            width: 100%;
            max-width: 900px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        /* SISI KIRI: PANEL PREVIEW GAMBAR */
        .preview-panel {
            flex: 1;
            background: rgba(23, 74, 124, 0.6);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .image-preview-frame {
            width: 260px;
            height: 260px;
            background-color: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .image-preview-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-label {
            margin-top: 15px;
            color: white;
            font-size: 0.8rem;
            opacity: 0.7;
            font-style: italic;
        }

        /* SISI KANAN: PANEL FORM INPUT */
        .form-panel {
            flex: 1.2;
            padding: 40px;
            color: white;
        }

        .form-panel h2 { font-size: 1.8rem; font-weight: 700; margin-bottom: 5px; }
        .form-panel p { font-size: 0.85rem; color: rgba(255, 255, 255, 0.6); margin-bottom: 25px; }

        .input-group { margin-bottom: 18px; }
        .input-group label { display: block; font-size: 0.85rem; margin-bottom: 8px; color: rgba(255, 255, 255, 0.8); }

        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-wrapper i { position: absolute; left: 15px; color: #1C5478; font-size: 1rem; z-index: 2; }
        .input-wrapper input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border-radius: 12px;
            border: none;
            background: white;
            font-size: 0.95rem;
            color: #333;
            outline: none;
        }

        .file-input-custom {
            width: 100%;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .action-buttons { display: flex; gap: 15px; margin-top: 25px; }
        .btn { flex: 1; padding: 12px; border-radius: 12px; font-weight: 600; font-size: 0.95rem; cursor: pointer; border: none; text-align: center; text-decoration: none; transition: all 0.2s; }
        .btn-submit { background: #67C090; color: white; }
        .btn-cancel { background: rgba(255, 255, 255, 0.1); color: white; border: 1px solid rgba(255, 255, 255, 0.2); }
        .btn-submit:hover { background: #56b081; transform: translateY(-2px); }

        @media (max-width: 768px) {
            .split-container { flex-direction: column; }
            .preview-panel { border-right: none; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        }
    </style>
</head>
<body>

    <div class="split-container">
        <div class="preview-panel">
            <div class="image-preview-frame">
                <img src="uploads/<?php echo htmlspecialchars($d['foto']); ?>" id="liveImage" alt="Preview Image">
            </div>
            <span class="preview-label" id="previewStatus">Foto saat ini</span>
        </div>

        <div class="form-panel">
            <h2>Ubah Impian</h2>
            <p>Perbarui detail target tabungan wishlist Anda.</p>
            
            <form action="proses/proses_edit_wishlist.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_wishlist" value="<?php echo $d['id_wishlist']; ?>">
                
                <div class="input-group">
                    <label>Nama Barang / Impian</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <input type="text" name="nama_barang" value="<?php echo htmlspecialchars($d['nama_barang']); ?>" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Target Harga Total (Rp)</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <input type="number" name="target_harga" value="<?php echo (int)$d['target_harga']; ?>" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="target_mingguan">Target Alokasi Tabungan</label>
                    <div style="display: flex; gap: 10px;">
                        <div class="input-wrapper" style="flex: 2;">
                            <i class="fa-solid fa-wallet"></i>
                            <input type="number" name="target_mingguan" id="target_mingguan" value="<?php echo (int)$d['target_mingguan']; ?>" required>
                        </div>

                        <select name="tipe_alokasi" id="tipe_alokasi" required style="flex: 1; padding: 12px; border-radius: 12px; border: none; font-family: 'Poppins'; background: white; color: #333; font-weight: 500; outline: none;">
                            <option value="harian" <?php echo ($d['tipe_alokasi'] == 'harian') ? 'selected' : ''; ?>>Per Hari</option>
                            <option value="mingguan" <?php echo ($d['tipe_alokasi'] == 'mingguan') ? 'selected' : ''; ?>>Per Minggu</option>
                            <option value="bulanan" <?php echo ($d['tipe_alokasi'] == 'bulanan') ? 'selected' : ''; ?>>Per Bulan</option>
                            <option value="tahunan" <?php echo ($d['tipe_alokasi'] == 'tahunan') ? 'selected' : ''; ?>>Per Tahun</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <label>Ganti Gambar (Opsional)</label>
                    <input type="file" name="foto" id="fileInput" class="file-input-custom" accept="image/*">
                </div>

                <div class="action-buttons">
                    <a href="detail_wishlist.php?id=<?php echo $d['id_wishlist']; ?>" class="btn btn-cancel">Batal</a>
                    <button type="submit" class="btn btn-submit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const liveImage = document.getElementById('liveImage');
        const previewStatus = document.getElementById('previewStatus');
        const originalImage = liveImage.src; 

        fileInput.addEventListener('change', function() {
            const file = this.files[0];

            if (file) {
                const reader = new FileReader();
                reader.addEventListener('load', function() {
                    liveImage.setAttribute('src', this.result);
                    previewStatus.innerHTML = "Pratinjau foto baru ✨";
                    previewStatus.style.color = "#67C090"; 
                    previewStatus.style.opacity = "1";
                });
                reader.readAsDataURL(file);
            } else {
                liveImage.setAttribute('src', originalImage);
                previewStatus.innerHTML = "Foto saat ini";
                previewStatus.style.color = "white";
                previewStatus.style.opacity = "0.7";
            }
        });
    </script>
</body>
</html>