<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CekC!ng</title>
    <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

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

        /* Container Utama dengan Layout Split 2 Kolom */
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
            position: relative;
        }

        /* Bingkai Foto Mengikuti Desain Grid Dashboard Anda */
        .image-preview-frame {
            width: 250px;
            height: 250px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px dashed rgba(255, 255, 255, 0.4);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: all 0.3s ease;
            color: rgba(255, 255, 255, 0.7);
        }

        .image-preview-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .image-preview-frame i {
            font-size: 3.5rem;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.5);
        }

        .preview-text {
            font-size: 0.9rem;
            font-weight: 500;
            text-align: center;
            padding: 0 10px;
        }

        /* SISI KANAN: PANEL FORM INPUT */
        .form-panel {
            flex: 1.2;
            padding: 40px;
            color: white;
        }

        .form-panel h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .form-panel p {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 25px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.8);
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            color: #1C5478;
            font-size: 1rem;
        }

        .input-wrapper input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: white;
            font-size: 0.95rem;
            color: #333;
            outline: none;
            transition: all 0.2s;
        }

        .input-wrapper input:focus {
            box-shadow: 0 0 0 3px rgba(103, 192, 144, 0.4);
        }

        /* Style Khusus Input File Gambar Kustom */
        .file-input-custom {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            cursor: pointer;
            font-size: 0.9rem;
        }

        /* Tombol Aksi */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            border: none;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-submit {
            background: #67C090;
            color: white;
        }

        .btn-submit:hover {
            background: #56b081;
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Responsive Mobile Layout */
        @media screen and (max-width: 768px) {
            .split-container {
                display: flex !important;
                flex-direction: column !important;
                gap: 20px !important;
                padding: 10px 16px 30px 16px !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .preview-panel,
            .form-panel {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }

            .preview-panel {
                display: flex !important;
                justify-content: center !important;
            }

            .image-preview-frame {
                width: 100% !important;
                max-width: 350px !important;
                height: 220px !important;
            }

            .form-panel h2 {
                font-size: 1.5rem !important;
            }

            .form-panel p {
                font-size: 0.85rem !important;
                margin-bottom: 20px !important;
            }

            .input-group style {
                flex-direction: column !important;
                gap: 12px !important;
            }

            .target {
                flex-direction: column !important;
                gap: 12px !important;
            }

            #target_mingguan,
            #tipe_alokasi {
                width: 100% !important;
                flex: none !important;
                box-sizing: border-box !important;
            }

            .action-buttons {
                flex-direction: column-reverse !important;
                gap: 10px !important;
                margin-top: 25px !important;
            }

            .btn {
                width: 100% !important;
                text-align: center !important;
                padding: 12px !important;
                box-sizing: border-box !important;
                margin: 0 !important;
            }
        }
    </style>
</head>

<body>

    <div class="split-container">

        <div class="preview-panel">
            <div class="image-preview-frame" id="previewFrame">
                <i class="fa-regular fa-image" id="placeholderIcon"></i>
                <span class="preview-text" id="placeholderText">Belum ada foto dipilih</span>
                <img src="" id="liveImage" alt="Live Preview">
            </div>
        </div>

        <div class="form-panel">
            <h2>Add New Wishlist</h2>
            <p>Masukkan target impian Anda dan mulai menabung!</p>

            <form action="proses/proses_add_wishlist.php" method="POST" enctype="multipart/form-data">

                <div class="input-group">
                    <label>Nama Barang / Impian</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <input type="text" name="nama_barang" placeholder="Contoh: PlayStation 5 Pro" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Target Harga Total (Rp)</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <input type="text" id="target_harga" name="target_harga" placeholder="Contoh: 12.500.000" oninput="formatRupiah(this)" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="target_mingguan">Target Alokasi Tabungan</label>
                    <div class="target" style="display: flex; gap: 10px;">
                        <input type="text" name="target_mingguan" id="target_mingguan" placeholder="Contoh: 250.000" oninput="formatRupiah(this)" required style="flex: 2; padding: 12px 15px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.2); outline: none; font-size: 0.95rem; color: #333; background: white;">

                        <select name="tipe_alokasi" id="tipe_alokasi" required style="flex: 1; padding: 10px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.2); font-family: 'Poppins'; background: white; color: #333; font-weight: 500; outline: none;">
                            <option value="harian">Per Hari</option>
                            <option value="mingguan" selected>Per Minggu</option>
                            <option value="bulanan">Per Bulan</option>
                            <option value="tahunan">Per Tahun</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <label>Unggah Gambar Barang</label>
                    <input type="file" name="foto" id="fileInput" class="file-input-custom" accept="image/*" required>
                </div>

                <div class="action-buttons">
                    <a href="dashboard.php" class="btn btn-cancel">Batal</a>
                    <button type="submit" class="btn btn-submit">Simpan Impian</button>
                </div>

            </form>
        </div>

    </div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const liveImage = document.getElementById('liveImage');
        const placeholderIcon = document.getElementById('placeholderIcon');
        const placeholderText = document.getElementById('placeholderText');
        const previewFrame = document.getElementById('previewFrame');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];

            if (file) {
                const reader = new FileReader();

                reader.addEventListener('load', function() {
                    liveImage.setAttribute('src', this.result);
                    liveImage.style.display = 'block';
                    placeholderIcon.style.display = 'none';
                    placeholderText.style.display = 'none';
                    previewFrame.style.border = 'none';
                });

                reader.readAsDataURL(file);
            } else {
                liveImage.style.display = 'none';
                placeholderIcon.style.display = 'block';
                placeholderText.style.display = 'block';
                previewFrame.style.border = '2px dashed rgba(255, 255, 255, 0.4)';
            }
        });

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

            // Kembalikan hasil format ke dalam input text
            element.value = rupiah;
        }
    </script>
</body>

</html>