<?php
session_start();
include 'proses/koneksi.php';

// Proteksi halaman: Jika belum login, tendang ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: loginNregist.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// Ambil data user paling update dari database
$query = mysqli_query($conn, "SELECT username, email, foto_profil FROM users WHERE id_user = '$id_user'");
$user = mysqli_fetch_assoc($query);

// Tentukan avatar yang tampil (pakai placeholder fontawesome jika data di db masih kosong)
$foto_sekarang = $user['foto_profil'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CekC!ng</title>
    <link rel="shortcut icon" href="img/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        .profile-main {
            min-height: 100vh;
            width: 100%;
            background: linear-gradient(135deg, #124170, #1C5478);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 100px 20px 40px 20px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        .profile-card {
            background-color: #ffffff;
            width: 100%;
            max-width: 500px;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            color: #333333;
            box-sizing: border-box;
        }

        .profile-card h2 {
            color: #124170;
            margin: 0 0 5px 0;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .profile-card .subtitle {
            color: #777;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .avatar-upload-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
            gap: 8px;
        }

        .avatar-preview {
            width: 120px;
            height: 120px;
            position: relative;
            border-radius: 100%;
            border: 4px solid #e1e5eb;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-edit-input input {
            display: none;
        }

        .avatar-edit-input label {
            display: inline-block;
            background-color: #f4f6f9;
            color: #124170;
            padding: 6px 16px;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 20px;
            cursor: pointer;
            border: 1px solid #dcdcdc;
            transition: all 0.3s ease;
        }

        .avatar-edit-input label:hover {
            background-color: #124170;
            color: #ffffff;
        }

        .file-info {
            font-size: 0.75rem;
            color: #999;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .form-group label {
            color: #124170;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5eb;
            border-radius: 10px;
            font-size: 0.95rem;
            outline: none;
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            border-color: #124170;
        }

        .form-group .input-disabled {
            background-color: #f5f5f5;
            color: #999;
            cursor: not-allowed;
            border-color: #e1e5eb;
        }

        .profile-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            gap: 15px;
        }

        .btn-cancel {
            text-decoration: none;
            color: #666;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 12px 20px;
        }

        .btn-save {
            background-color: #67C090;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            background-color: #55b07f;
            transform: translateY(-2px);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .alert-danger {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        @media screen and (max-width: 768px) {
            .profile-main {
                padding: 80px 16px 30px 16px !important;
                align-items: flex-start !important;
            }

            .profile-card {
                padding: 24px 20px !important;
                border-radius: 16px !important;
            }

            .profile-card h2 {
                font-size: 1.5rem !important;
            }

            .profile-card .subtitle {
                font-size: 0.85rem !important;
                margin-bottom: 20px !important;
            }

            .avatar-preview {
                width: 100px !important;
                height: 100px !important;
            }

            .form-group {
                margin-bottom: 15px !important;
            }

            .form-group input {
                padding: 10px 12px !important;
                font-size: 0.9rem !important;
            }

            .profile-actions {
                flex-direction: column-reverse !important;
                gap: 10px !important;
                margin-top: 20px !important;
            }

            .btn-cancel,
            .btn-save {
                width: 100% !important;
                text-align: center !important;
                box-sizing: border-box !important;
                padding: 12px !important;
            }

            .navbar {
                padding: 10px 16px !important;
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
            }

            .nav-links {
                gap: 15px !important;
            }

            .nav-links a {
                font-size: 0.9rem !important;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar shrunk">
        <img src="img/logo.png" alt="Logo CekC!ng" class="logo">
        <ul class="nav-links">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="proses/logout.php" style="color: #ff6b6b;">Logout</a></li>
        </ul>
    </nav>

    <main class="profile-main">
        <div class="profile-card">
            <h2>Edit Profile</h2>
            <p class="subtitle">Kelola informasi akun dan foto profil CekC!ng Anda</p>

            <?php if (isset($_GET['status'])): ?>
                <div class="alert <?= ($_GET['status'] == 'success') ? 'alert-success' : 'alert-danger'; ?>">
                    <?= isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : ''; ?>
                </div>
            <?php endif; ?>

            <form action="proses/proses_update_profile.php" method="POST" enctype="multipart/form-data">

                <div class="avatar-upload-wrapper">
                    <div class="avatar-preview">
                        <?php if (!empty($foto_sekarang) && file_exists($foto_sekarang)): ?>
                            <img src="<?= htmlspecialchars($foto_sekarang); ?>" id="imagePreview" alt="Foto Profil">
                        <?php else: ?>
                            <img src="img/default-avatar.png" id="imagePreview" alt="Foto Profil Default" style="opacity: 0.6;">
                        <?php endif; ?>
                    </div>
                    <div class="avatar-edit-input">
                        <input type='file' id="imageUpload" name="foto_profil" accept=".png, .jpg, .jpeg" />
                        <label for="imageUpload">Ganti Foto 📷</label>
                    </div>
                    <small class="file-info">Format: JPG, JPEG, PNG (Maks 2MB)</small>
                </div>

                <div class="form-group">
                    <label for="username">Username Baru</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Akun (Permanen)</label>
                    <input type="email" id="email" value="<?= htmlspecialchars($user['email']); ?>" disabled class="input-disabled">
                </div>

                <div class="profile-actions">
                    <a href="dashboard.php" class="btn-cancel">Kembali</a>
                    <button type="submit" class="btn-save">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Script instant preview gambar saat user memilih file di local computer
        document.getElementById('imageUpload').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').setAttribute('src', e.target.result);
                    document.getElementById('imagePreview').style.opacity = "1";
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>