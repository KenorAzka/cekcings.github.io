<?php
session_start();
include 'koneksi.php';

$current_page = basename($_SERVER['PHP_SELF']);
// Proteksi Halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: loginNregist.php");
    exit();
}

$id_user = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];

$query_wishlist = "SELECT w.*, COALESCE(SUM(s.jumlah_tabungan), 0) AS total_terkumpul 
    FROM wishlists w 
    LEFT JOIN saving_progress s ON w.id_wishlist = s.id_wishlist
    WHERE w.id_user = '$id_user' 
    GROUP BY w.id_wishlist 
    ORDER BY w.created_at DESC";
$tampil_wishlist = mysqli_query($conn, $query_wishlist);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CekC!ng</title>
    <link rel="stylesheet" href="css/dashboard_style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="dashboard-container">

        <aside class="sidebar">
            <div class="sidebar-top">
                <img src="img/logo.png" alt="Logo CekC!ng" class="sidebar-logo">
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li class="<?php echo ($current_page == 'dashboard.php' || $current_page == 'detail_wishlist.php') ? 'active' : ''; ?>">
                        <a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
                    </li>

                    <li class="<?php echo ($current_page == 'wishlist.php') ? 'active' : ''; ?>">
                        <a href="#wishlist-section"><i class="fa-solid fa-bullseye"></i> My Wishlist</a>
                    </li>

                    <li class="<?php echo ($current_page == 'penarikan.php') ? 'active' : ''; ?>">
                        <a href="#"><i class="fa-solid fa-wallet"></i> Catatan Penarikan</a>
                    </li>

                    <li class="<?php echo ($current_page == 'pengingat.php') ? 'active' : ''; ?>">
                        <a href="#"><i class="fa-solid fa-bell"></i> Pengingat</a>
                    </li>

                    <li class="<?php echo ($current_page == 'laporan.php') ? 'active' : ''; ?>">
                        <a href="#"><i class="fa-solid fa-chart-simple"></i> Laporan</a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-profile">
                <div class="profile-box" id="profileToggle">
                    <div class="avatar"><i class="fa-solid fa-user"></i></div>
                    <div class="profile-info">
                        <span class="profile-name"><?php echo htmlspecialchars($username); ?></span>
                        <span class="profile-email"><?php echo htmlspecialchars($email); ?></span>
                    </div>
                    <i class="fa-solid fa-chevron-down arrow-down"></i>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <ul>
                        <li><a href="#"><i class="fa-regular fa-user"></i> Lihat Profil</a></li>
                        <li><a href="logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar / Logout</a></li>
                    </ul>
                </div>
            </div>
        </aside>

        <main class="main-dashboard">
            <header class="dashboard-header">
                <div class="welcome-text">
                    <h1>Halo, <?php echo htmlspecialchars($username); ?>! 👋</h1>
                    <p>Kelola Tabunganmu & Wujudkan impianmu bersama CekC!ng</p>
                </div>

                <div class="header-right">
                    <div class="date-widget">
                        <i class="fa-regular fa-calendar-days"></i>
                        <span>
                            <?php
                            date_default_timezone_set('Asia/Jakarta');
                            $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            echo date('d') . ' ' . $bulan[(int)date('m')] . ' ' . date('Y');
                            ?>
                        </span>
                    </div>
                    <button class="notif-btn"><i class="fa-regular fa-bell"></i></button>
                </div>
            </header>

            <section id="wishlist-section" class="dashboard-content">
                <div class="wishlist-header">
                    <h2>My Wishlist</h2>
                    <a href="add_wishlist.php" class="add-savings-btn"><i class="fa-solid fa-plus"></i> Add Savings</a>
                </div>

                <div class="wishlist-grid">
                    <?php
                    if (mysqli_num_rows($tampil_wishlist) > 0) {
                        while ($row = mysqli_fetch_assoc($tampil_wishlist)) {

                            // LOGIKA MATEMATIS PROGRESS & SISA MINGGU
                            $target_harga = (float)$row['target_harga'];
                            $target_mingguan = (float)$row['target_mingguan'];
                            $total_terkumpul = (float)$row['total_terkumpul'];

                            // Hitung persentase progress (Maksimal 100%)
                            $progress_percent = ($target_harga > 0) ? round(($total_terkumpul / $target_harga) * 100) : 0;
                            if ($progress_percent > 100) $progress_percent = 100;

                            // Hitung sisa kekurangan uang
                            $kekurangan = $target_harga - $total_terkumpul;
                            if ($kekurangan < 0) $kekurangan = 0;

                            // Hitung sisa minggu yang dibutuhkan
                            $weeks_left = ($target_mingguan > 0) ? ceil($kekurangan / $target_mingguan) : 0;
                    ?>
                            <a href="detail_wishlist.php?id=<?php echo $row['id_wishlist']; ?>" style="text-decoration: none; color: inherit;">
                                <div class="wishlist-card">
                                    <div class="card-top">
                                        <div class="image-container">
                                            <img src="uploads/<?php echo htmlspecialchars($row['foto']); ?>" alt="<?php echo htmlspecialchars($row['nama_barang']); ?>">
                                        </div>
                                        <div class="item-detail">
                                            <h3><?php echo htmlspecialchars($row['nama_barang']); ?></h3>
                                            <div class="progress-container">
                                                <span class="progress-title">Progress</span>
                                                <div class="progress-bar-bg">
                                                    <div class="progress-bar-fill" style="width: <?php echo $progress_percent; ?>%;"></div>
                                                </div>
                                                <span class="progress-caption"><?php echo ($progress_percent >= 100) ? 'Goal Achieved!' : $weeks_left . ' More Weeks'; ?></span>
                                            </div>
                                            <span class="percent-badge"><?php echo $progress_percent; ?>%</span>
                                        </div>
                                    </div>
                                    <div class="card-bottom">
                                        <div class="price-info">
                                            <span class="total-price">Rp<?php echo number_format($target_harga, 0, ',', '.'); ?></span>
                                            <span class="weekly-target">Rp<?php echo number_format($target_mingguan, 0, ',', '.'); ?> Per Week</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                    <?php
                        }
                    } else {
                        echo "<p class='empty-text'>Belum ada wishlist tercatat.</p>";
                    }
                    ?>
                </div>
            </section>
        </main>

    </div>

    <script>
        const menuItems = document.querySelectorAll('.sidebar-menu li');

        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                // Hapus class active dari semua menu terlebih dahulu
                menuItems.forEach(i => i.classList.remove('active'));

                // Tambahkan class active ke menu yang baru saja diklik
                this.classList.add('active');
            });
        });
        const profileToggle = document.getElementById('profileToggle');
        const profileDropdown = document.getElementById('profileDropdown');

        profileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });

        window.addEventListener('click', function() {
            if (profileDropdown.classList.contains('active')) {
                profileDropdown.classList.remove('active');
            }
        });
    </script>
</body>

</html>