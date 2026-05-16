<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Wishlist - CekC!ng</title>
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
            display: none; /* Tersembunyi di awal sebelum diupload */
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
        @media (max-width: 768px) {
            .split-container {
                flex-direction: column;
            }
            .preview-panel {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                padding: 30px;
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
            
            <form action="proses_add_wishlist.php" method="POST" enctype="multipart/form-data">
                
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
                        <input type="number" name="target_harga" placeholder="Contoh: 12500000" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Target Alokasi Mingguan (Rp)</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-wallet"></i>
                        <input type="number" name="target_mingguan" placeholder="Contoh: 250000" required>
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

                // Saat file selesai dibaca oleh sistem browser
                reader.addEventListener('load', function() {
                    // 1. Ganti atribut src tag img dengan data url gambar baru
                    liveImage.setAttribute('src', this.result);
                    
                    // 2. Tampilkan element gambar
                    liveImage.style.display = 'block';
                    
                    // 3. Sembunyikan icon & teks panduan awal
                    placeholderIcon.style.display = 'none';
                    placeholderText.style.display = 'none';
                    
                    // 4. Ubah border bingkai menjadi garis solid agar rapi
                    previewFrame.style.border = 'none';
                });

                // Membaca file gambar sebagai Data URL
                reader.readAsDataURL(file);
            } else {
                // Jika user membatalkan pilihan file, kembalikan ke kondisi awal
                liveImage.style.display = 'none';
                placeholderIcon.style.display = 'block';
                placeholderText.style.display = 'block';
                previewFrame.style.border = '2px dashed rgba(255, 255, 255, 0.4)';
            }
        });
    </script>
</body>
</html>