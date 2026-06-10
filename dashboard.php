<?php
session_start();
include 'proses/koneksi.php';

$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['user_id'])) {
    header("Location: loginNregist.php");
    exit();
}

$id_user = $_SESSION['user_id'];
$username = $_SESSION['username'];
$email = $_SESSION['email'];

// Query mengambil seluruh wishlist milik user beserta akumulasi tabungannya
$query_wishlist = "SELECT w.*, COALESCE(SUM(s.jumlah_tabungan), 0) AS total_terkumpul 
    FROM wishlists w 
    LEFT JOIN saving_progress s ON w.id_wishlist = s.id_wishlist
    WHERE w.id_user = '$id_user' 
    GROUP BY w.id_wishlist 
    ORDER BY w.created_at DESC";
$tampil_wishlist = mysqli_query($conn, $query_wishlist);

$wishlists = [];
// Inisialisasi variabel statistik untuk Dashboard Summary Card
$total_wishlist_aktif = 0;
$total_target_keseluruhan = 0;
$total_terkumpul_keseluruhan = 0;

// Variabel baru untuk menampung data card ke-4 (Target Berkala Dinamis)
$target_berkala_nominal = 0;
$tipe_berkala_display = "Target Berkala";

if ($tampil_wishlist && mysqli_num_rows($tampil_wishlist) > 0) {
    while ($row = mysqli_fetch_assoc($tampil_wishlist)) {
        $wishlists[] = $row;

        // Kalkulasi persentase item saat ini
        $th = (float)$row['target_harga'];
        $tt = (float)$row['total_terkumpul'];
        $persen = ($th > 0) ? ($tt / $th) * 100 : 0;

        // Hanya hitung statistik dashboard jika status wishlist 'In Progress' atau belum 100%
        if ($row['status'] == 'In Progress' && $persen < 100) {
            $total_wishlist_aktif++;
            $total_target_keseluruhan += $th;
            $total_terkumpul_keseluruhan += $tt;

            // Logika Card 4: Ambil nominal dari wishlist aktif pertama yang ditemukan
            if ($target_berkala_nominal == 0) {
                $target_berkala_nominal = (float)$row['target_mingguan'];
                $tipe_berkala_display = "Target " . ucfirst($row['tipe_alokasi']); // Contoh: harian -> Target Harian
            }
        }
    }
}

// Query mengambil 3 aktivitas terakhir dengan kolom yang benar (tanggal_setor)
$query_aktivitas = "SELECT s.jumlah_tabungan, s.tanggal_setor, s.keterangan, w.nama_barang 
    FROM saving_progress s
    JOIN wishlists w ON s.id_wishlist = w.id_wishlist
    WHERE w.id_user = '$id_user'
    ORDER BY s.tanggal_setor DESC, s.id_progress DESC
    LIMIT 3";
$tampil_aktivitas = mysqli_query($conn, $query_aktivitas);

$aktivitas_terakhir = [];
if ($tampil_aktivitas && mysqli_num_rows($tampil_aktivitas) > 0) {
    while ($row_act = mysqli_fetch_assoc($tampil_aktivitas)) {
        $aktivitas_terakhir[] = $row_act;
    }
}

// Hitung persentase progress keseluruhan dari target finansial
$persen_keseluruhan = ($total_target_keseluruhan > 0) ? round(($total_terkumpul_keseluruhan / $total_target_keseluruhan) * 100, 2) : 0;
$sisa_target_keseluruhan = max(0, $total_target_keseluruhan - $total_terkumpul_keseluruhan);
$persen_sisa_keseluruhan = ($total_target_keseluruhan > 0) ? round(($sisa_target_keseluruhan / $total_target_keseluruhan) * 100, 2) : 100;

// Query mengambil 1 pengingat aktif terbaru milik user dengan join ke tabel wishlists
$query_reminder = "SELECT r.hari, r.jam, r.is_active, w.nama_barang 
    FROM reminders r
    JOIN wishlists w ON r.id_wishlist = w.id_wishlist
    WHERE w.id_user = '$id_user' AND r.is_active = 1
    ORDER BY r.id_reminder DESC
    LIMIT 1";
$tampil_reminder = mysqli_query($conn, $query_reminder);

$reminder_aktif = null;
if ($tampil_reminder && mysqli_num_rows($tampil_reminder) > 0) {
    $reminder_aktif = mysqli_fetch_assoc($tampil_reminder);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CekC!ng</title>
    <link rel="stylesheet" href="css/dashboard.css?v=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

        .content-section {
            display: none;
        }
        .content-section.section-active {
            display: block;
        }
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
                    <li class="menu-item active" data-section="dashboard-section">
                        <a href="#"><i class="fa-solid fa-house"></i> Dashboard</a>
                    </li>

                    <li class="menu-item" data-section="wishlist-section">
                        <a href="#"><i class="fa-solid fa-bullseye"></i> My Wishlist</a>
                    </li>

                    <li class="menu-item" data-section="completed-section">
                        <a href="#"><i class="fa-solid fa-wallet"></i> Completed</a>
                    </li>

                    <li class="menu-item" data-section="pengingat-section">
                        <a href="#"><i class="fa-solid fa-bell"></i> Pengingat</a>
                    </li>

                    <li class="menu-item" data-section="feedback-section">
                        <a href="#"><i class="fa-solid fa-comments"></i> Feedback</a>
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
                        <li><a href="proses/logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar / Logout</a></li>
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

            <section id="dashboard-section" class="dashboard-content content-section section-active">

                <div class="summary-cards-container">
                    <div class="summary-card card-blue">
                        <div class="summary-icon"><i class="fa-solid fa-wallet"></i></div>
                        <div class="summary-info">
                            <h3>Total Wishlist</h3>
                            <p><?php echo $total_wishlist_aktif; ?></p>
                            <span>Wishlist Aktif</span>
                        </div>
                    </div>

                    <div class="summary-card card-green">
                        <div class="summary-icon"><i class="fa-solid fa-bullseye"></i></div>
                        <div class="summary-info">
                            <h3>Total Target</h3>
                            <p>Rp<?php echo number_format($total_target_keseluruhan, 0, ',', '.'); ?></p>
                            <span>Target Keseluruhan</span>
                        </div>
                    </div>

                    <div class="summary-card card-purple">
                        <div class="summary-icon"><i class="fa-solid fa-chart-simple"></i></div>
                        <div class="summary-info">
                            <h3>Total Terkumpul</h3>
                            <p>Rp<?php echo number_format($total_terkumpul_keseluruhan, 0, ',', '.'); ?></p>
                            <span><?php echo $persen_keseluruhan; ?>% dari total target</span>
                        </div>
                    </div>

                    <div class="summary-card card-orange">
                        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <div class="summary-icon"><i class="fa-regular fa-calendar"></i></div>
                                <div class="summary-info">
                                    <h3><?php echo $tipe_berkala_display; ?></h3>
                                    <p>Rp<?php echo number_format($target_berkala_nominal, 0, ',', '.'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 15px;">
                            <div style="background: rgba(255,255,255,0.2); height: 6px; border-radius: 10px; position: relative;">
                                <div style="background: #fff; width: <?php echo min(100, $persen_keseluruhan); ?>%; height: 100%; border-radius: 10px;"></div>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.7rem; margin-top: 5px; opacity: 0.8;">
                                <span>Dari Rp<?php echo number_format($total_target_keseluruhan, 0, ',', '.'); ?></span>
                                <span><?php echo $persen_keseluruhan; ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-grid-layout">

                    <div class="left-column">
                        <div class="section-title-container">
                            <h2>Wishlist Kamu</h2>
                            <a href="#" class="view-all-link" onclick="document.querySelector('[data-section=\'wishlist-section\']').click();">Lihat Semua</a>
                        </div>

                        <div class="horizontal-wishlist-flex">
                            <?php
                            $limit_preview = 0;
                            foreach ($wishlists as $row) {
                                $target_harga = (float)$row['target_harga'];
                                $total_terkumpul = (float)$row['total_terkumpul'];
                                $progress_percent = ($target_harga > 0) ? round(($total_terkumpul / $target_harga) * 100) : 0;

                                if ($row['status'] != 'In Progress' || $progress_percent >= 100) continue; // Lewati jika selesai / diklaim
                                if ($limit_preview >= 3) break; // Batasi maksimal 3 preview item teratas
                                $limit_preview++;
                            ?>
                                <div class="wishlist-card" style="position: relative;">
                                    <span style="position: absolute; left: 15px; top: 15px; font-size: 0.65rem; background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 12px; font-weight: 600; text-transform: capitalize; z-index: 2;">
                                        <?php echo htmlspecialchars($row['tipe_alokasi']); ?>
                                    </span>

                                    <div style="position: absolute; right: 15px; top: 15px; color: #94a3b8; cursor: pointer; z-index: 2;"><i class="fa-solid fa-ellipsis-vertical"></i></div>
                                    <img src="uploads/<?php echo htmlspecialchars($row['foto']); ?>" alt="<?php echo htmlspecialchars($row['nama_barang']); ?>">
                                    <h3 style="font-size: 0.95rem; margin: 10px 0 5px 0; font-weight: 600;"><?php echo htmlspecialchars($row['nama_barang']); ?></h3>
                                    <p style="font-size: 0.9rem; font-weight: 700; margin: 0 0 15px 0; color: #1e293b;">Rp<?php echo number_format($target_harga, 0, ',', '.'); ?></p>

                                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #64748b; margin-bottom: 5px;">
                                        <span>Terkumpul: Rp<?php echo number_format($total_terkumpul, 0, ',', '.'); ?></span>
                                        <span style="font-weight: 600; color: #2563eb;"><?php echo $progress_percent; ?>%</span>
                                    </div>
                                    <div style="background: #e2e8f0; height: 6px; border-radius: 10px; overflow: hidden; margin-bottom: 12px;">
                                        <div style="background: #2563eb; width: <?php echo min(100, $progress_percent); ?>%; height: 100%;"></div>
                                    </div>

                                    <div style="font-size: 0.7rem; color: #64748b; display: flex; align-items: center; gap: 4px;">
                                        <i class="fa-regular fa-money-bill-1" style="color: #10b981;"></i>
                                        <span>Wajib: <strong>Rp<?php echo number_format($row['target_mingguan'], 0, ',', '.'); ?></strong> / <?php echo strtolower($row['tipe_alokasi']); ?></span>
                                    </div>
                                </div>
                            <?php
                            }
                            if ($limit_preview == 0) {
                                echo "<p style='color:#94a3b8; font-size:0.85rem; padding: 15px 0;'>Belum ada wishlist aktif.</p>";
                            }
                            ?>
                        </div>

                        <div class="tip-box">
                            <i class="fa-solid fa-seedling" style="font-size: 1.5rem;"></i>
                            <div class="tip-text">
                                <h4>Tip CekC!ng</h4>
                                <p>Menabung sedikit setiap hari lebih baik daripada menunggu punya banyak. Konsistensi adalah kunci utama mencapai impianmu!</p>
                            </div>
                        </div>
                    </div>

                    <div class="right-column">
                        <div class="right-widget-card">
                            <h3>Progress Keseluruhan</h3>
                            <div class="chart-container">
                                <svg class="circle-chart" viewBox="0 0 36 36">
                                    <path class="circle-bg" stroke="#e2e8f0" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    <path class="circle-fill" stroke="#2563eb" stroke-width="3" stroke-dasharray="<?php echo $persen_keseluruhan; ?>, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                </svg>
                                <div class="circle-text">
                                    <h2><?php echo $persen_keseluruhan; ?>%</h2>
                                    <p>Terkumpul</p>
                                </div>
                            </div>

                            <div class="chart-legend">
                                <div class="legend-item">
                                    <div class="legend-label"><span class="dot" style="background:#2563eb;"></span> Terkumpul</div>
                                    <div style="text-align: right;"><strong>Rp<?php echo number_format($total_terkumpul_keseluruhan, 0, ',', '.'); ?></strong> <span style="opacity:0.6; font-size:0.7rem;"><?php echo $persen_keseluruhan; ?>%</span></div>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-label"><span class="dot" style="background:#cbd5e1;"></span> Sisa Target</div>
                                    <div style="text-align: right;"><strong>Rp<?php echo number_format($sisa_target_keseluruhan, 0, ',', '.'); ?></strong> <span style="opacity:0.6; font-size:0.7rem;"><?php echo $persen_sisa_keseluruhan; ?>%</span></div>
                                </div>
                            </div>
                            <div style="border-top: 1px solid #f1f5f9; margin-top: 15px; padding-top: 10px; text-align: center; font-size: 0.75rem; font-weight: 600; color: #64748b;">
                                Total Target: Rp<?php echo number_format($total_target_keseluruhan, 0, ',', '.'); ?>
                            </div>
                        </div>

                        <div class="right-widget-card">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h3 style="margin:0;">Pengingat Aktif</h3>
                                <a href="#" class="view-all-link" style="color:#2563eb;" onclick="document.querySelector('[data-section=\'pengingat-section\']').click();">Lihat Semua</a>
                            </div>

                            <?php if ($reminder_aktif):
                                $clean_jam = date('H:i', strtotime($reminder_aktif['jam']));
                            ?>
                                <div class="reminder-item">
                                    <div style="display: flex; gap: 12px; align-items: center;">
                                        <div style="background: #e2f1e7; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa-solid fa-bell" style="color: #2e6f40; font-size: 0.9rem;"></i>
                                        </div>
                                        <div>
                                            <div style="font-size: 0.85rem; font-weight: 600; color: #1e293b;">
                                                Menabung untuk <?php echo htmlspecialchars($reminder_aktif['nama_barang']); ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                                Setiap <?php echo htmlspecialchars($reminder_aktif['hari']); ?>, <?php echo $clean_jam; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="badge-aktif">Aktif</span>
                                </div>
                            <?php else: ?>
                                <div style="text-align: center; padding: 15px 0; color: #94a3b8; font-size: 0.8rem;">
                                    <i class="fa-regular fa-bell-slash" style="font-size: 1.3rem; margin-bottom: 5px; display:block;"></i>
                                    Tidak ada pengingat aktif.
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="right-widget-card">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h3 style="margin:0;">Aktivitas Terakhir</h3>
                                <a href="#" class="view-all-link" style="color:#2563eb;">Lihat Semua</a>
                            </div>
                            <div class="activity-list">
                                <?php
                                if (!empty($aktivitas_terakhir)) {
                                    foreach ($aktivitas_terakhir as $act) {
                                        $timestamp = strtotime($act['tanggal_setor']);
                                        $bulan_id = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                        $format_tgl = date('d', $timestamp) . ' ' . $bulan_id[(int)date('m', $timestamp)] . ' ' . date('Y, H:i', $timestamp);

                                        $jumlah = (float)$act['jumlah_tabungan'];

                                        if ($jumlah >= 0) {
                                            $icon_class = "fa-solid fa-plus";
                                            $bg_icon = "#e2f1e7";
                                            $color_text = "#2e6f40";
                                            $label_text = "Menabung untuk " . htmlspecialchars($act['nama_barang']);
                                            $display_jumlah = "+Rp" . number_format($jumlah, 0, ',', '.');
                                        } else {
                                            $icon_class = "fa-solid fa-minus";
                                            $bg_icon = "#fde8e8";
                                            $color_text = "#9b1c1c";
                                            $label_text = (!empty($act['keterangan']) && $act['keterangan'] != 'Aw') ? htmlspecialchars($act['keterangan']) : "Penarikan untuk " . htmlspecialchars($act['nama_barang']);
                                            $display_jumlah = "-Rp" . number_format(abs($jumlah), 0, ',', '.');
                                        }
                                ?>
                                        <div class="activity-item">
                                            <div class="activity-left">
                                                <div class="activity-icon" style="background: <?php echo $bg_icon; ?>; color: <?php echo $color_text; ?>;">
                                                    <i class="<?php echo $icon_class; ?>"></i>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 600; font-size: 0.85rem; color: #1e293b;">
                                                        <?php echo $label_text; ?>
                                                    </div>
                                                    <div style="opacity: 0.6; font-size: 0.7rem; color: #64748b;">
                                                        <?php echo $format_tgl; ?> WIB
                                                    </div>
                                                </div>
                                            </div>
                                            <div style="color: <?php echo $color_text; ?>; font-weight: 700; font-size: 0.85rem;">
                                                <?php echo $display_jumlah; ?>
                                            </div>
                                        </div>
                                <?php
                                    }
                                } else {
                                    echo "
                                    <div style='text-align: center; padding: 10px 0; color: #94a3b8; font-size: 0.8rem;'>
                                        <i class='fa-solid fa-receipt' style='font-size: 1.5rem; margin-bottom: 5px; display:block;'></i>
                                        Belum ada riwayat aktivitas keuangan.
                                    </div>";
                                }
                                ?>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <section id="wishlist-section" class="dashboard-content content-section">
                <div class="wishlist-header">
                    <h2>My Wishlist</h2>
                    <a href="add_wishlist.php" class="add-savings-btn"><i class="fa-solid fa-plus"></i> Add Savings</a>
                </div>

                <div class="wishlist-grid">
                    <?php
                    $ada_wishlist_aktif = false;
                    foreach ($wishlists as $row) {
                        $target_harga = (float)$row['target_harga'];
                        $total_terkumpul = (float)$row['total_terkumpul'];
                        $progress_percent = ($target_harga > 0) ? round(($total_terkumpul / $target_harga) * 100) : 0;

                        // Lewati jika target sudah tercapai (100% atau lebih)
                        if ($progress_percent >= 100) {
                            continue;
                        }

                        $ada_wishlist_aktif = true;
                        $target_mingguan = (float)$row['target_mingguan'];
                        $kekurangan = max(0, $target_harga - $total_terkumpul);
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
                                            <span class="progress-caption"><?php echo $weeks_left . ' More Weeks'; ?></span>
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
                    if (!$ada_wishlist_aktif) {
                        echo "<p class='empty-text'>Belum ada impian aktif yang sedang berjalan.</p>";
                    }
                    ?>
                </div>
            </section>

            <section id="completed-section" class="dashboard-content content-section">
                <div class="wishlist-header">
                    <h2>Congrats, <?php echo htmlspecialchars($username); ?> inilah daftar wishlistmu yang sudah terpenuhi 🎉</h2>
                </div>
                <p style="color: white; margin-top: 10px; margin-bottom: 20px;">Riwayat impian yang berhasil kamu tabung dengan konsisten.</p>

                <div class="wishlist-grid">
                    <?php
                    $ada_completed = false;
                    foreach ($wishlists as $row) {
                        $target_harga = (float)$row['target_harga'];
                        $total_terkumpul = (float)$row['total_terkumpul'];
                        $progress_percent = ($target_harga > 0) ? round(($total_terkumpul / $target_harga) * 100) : 0;

                        // Hanya proses jika target sudah mencapai atau melebihi 100%
                        if ($progress_percent < 100) {
                            continue;
                        }

                        $ada_completed = true;
                        $target_mingguan = (float)$row['target_mingguan'];
                    ?>
                        <a href="detail_wishlist.php?id=<?php echo $row['id_wishlist']; ?>" style="text-decoration: none; color: inherit;">
                            <div class="wishlist-card" style="border: 1px solid rgba(0, 255, 120, 0.4);">
                                <div class="card-top">
                                    <div class="image-container">
                                        <img src="uploads/<?php echo htmlspecialchars($row['foto']); ?>" alt="<?php echo htmlspecialchars($row['nama_barang']); ?>">
                                    </div>
                                    <div class="item-detail">
                                        <h3><?php echo htmlspecialchars($row['nama_barang']); ?></h3>
                                        <div class="progress-container">
                                            <span class="progress-title" style="color: #00ff78;">Completed</span>
                                            <div class="progress-bar-bg">
                                                <div class="progress-bar-fill" style="width: 100%; background: #00ff78;"></div>
                                            </div>
                                            <span class="progress-caption" style="color: #00ff78; font-weight: 600;">Goal Achieved!</span>
                                        </div>
                                        <span class="percent-badge" style="background: #00ff78; color: #111;">100%</span>
                                    </div>
                                </div>
                                <div class="card-bottom">
                                    <div class="price-info">
                                        <span class="total-price">Rp<?php echo number_format($target_harga, 0, ',', '.'); ?></span>
                                        <span class="weekly-target" style="color: #00ff78;">Target Terpenuhi!</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php
                    }
                    if (!$ada_completed) {
                        echo "<p class='empty-text'>Belum ada wishlist yang mencapai target 100%.</p>";
                    }
                    ?>
                </div>
            </section>

            <section id="pengingat-section" class="dashboard-content content-section">
                <div class="wishlist-header">
                    <h2>Pengingat Menabung</h2>
                    <a href="add_reminder.php" class="add-savings-btn" style="display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-plus"></i> Atur Pengingat Baru
                    </a>
                </div>
                <p style="color: rgba(255, 255, 255, 0.7); margin-top: 5px; margin-bottom: 25px; font-size: 0.9rem;">
                    Kelola jadwal notifikasi rutin agar target wishlist impianmu selesai tepat waktu.
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
                    <?php
                    // Ambil semua daftar pengingat milik user saat ini
                    $query_all_reminders = "SELECT r.*, w.nama_barang FROM reminders r 
                                JOIN wishlists w ON r.id_wishlist = w.id_wishlist 
                                WHERE w.id_user = '$id_user' 
                                ORDER BY r.id_reminder DESC";
                    $tampil_all = mysqli_query($conn, $query_all_reminders);

                    if ($tampil_all && mysqli_num_rows($tampil_all) > 0) {
                        while ($rem = mysqli_fetch_assoc($tampil_all)) {
                            $status_aktif = ((int)$rem['is_active'] === 1);
                            $formatted_jam = date('H:i', strtotime($rem['jam']));
                    ?>
                            <div style="background: white; border-radius: 15px; padding: 20px; color: #333; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; height: 140px; opacity: <?php echo $status_aktif ? '1' : '0.7'; ?>;">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="display: flex; gap: 15px; align-items: center;">
                                        <div style="background: <?php echo $status_aktif ? '#e2f1e7' : '#f1f5f9'; ?>; width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa-solid <?php echo $status_aktif ? 'fa-bell' : 'fa-bell-slash'; ?>" style="color: <?php echo $status_aktif ? '#2e6f40' : '#94a3b8'; ?>; font-size: 1.2rem;"></i>
                                        </div>
                                        <div>
                                            <h4 style="margin: 0; font-size: 0.95rem; font-weight: 600; color: #1e293b;">Menabung: <?php echo htmlspecialchars($rem['nama_barang']); ?></h4>
                                            <p style="margin: 4px 0 0 0; font-size: 0.8rem; color: #64748b;"><i class="fa-regular fa-clock"></i> Setiap <?php echo htmlspecialchars($rem['hari']); ?>, <?php echo $formatted_jam; ?> WIB</p>
                                        </div>
                                    </div>
                                    <span class="badge-aktif" style="background: <?php echo $status_aktif ? '#e2f1e7' : '#f1f5f9'; ?>; color: <?php echo $status_aktif ? '#2e6f40' : '#94a3b8'; ?>; font-size: 0.7rem;">
                                        <?php echo $status_aktif ? 'Aktif' : 'Nonaktif'; ?>
                                    </span>
                                </div>

                                <div style="border-top: 1px solid #f1f5f9; padding-top: 12px; display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 0.75rem; color: #94a3b8;">Sistem Notifikasi</span>
                                    <div style="display: flex; gap: 15px;">
                                        <a href="proses/toggle_reminder.php?id=<?php echo $rem['id_reminder']; ?>" style="color: <?php echo $status_aktif ? '#9b1c1c' : '#2e6f40'; ?>; text-decoration: none; font-size: 0.85rem;" title="<?php echo $status_aktif ? 'Matikan' : 'Aktifkan'; ?>">
                                            <i class="fa-solid fa-power-off"></i>
                                        </a>
                                        <a href="proses/hapus_reminder.php?id=<?php echo $rem['id_reminder']; ?>" style="color: #94a3b8; text-decoration: none; font-size: 0.85rem;" onclick="return confirm('Hapus pengingat ini?')" title="Hapus">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<p style='color: white; font-size: 0.9rem; grid-column: 1/-1;'>Kamu belum membuat pengingat menabung apa pun.</p>";
                    }
                    ?>
                </div>
            </section>

            <section id="feedback-section" class="dashboard-content content-section">
                <div class="wishlist-header">
                    <h2>Feedback & Saran</h2>
                </div>
                <p style="color: white; margin-top: 10px;">Bantu kami mengembangkan CekC!ng menjadi lebih baik!</p>

                <div class="feedback-form-container">
                    <form id="feedbackForm">
                        <div class="input-group">
                            <label style="color: white; font-size: 0.85rem;">Isi Masukan / Kritik & Saran</label>
                            <textarea name="pesan" id="pesanFeedback" placeholder="Tulis masukan Anda di sini untuk pengembangan CekC!ng..." required style="width: 100%; min-height: 120px; padding: 12px; border-radius: 12px; border: none; outline: none; font-family: 'Poppins'; color: #333; margin-top: 8px;"></textarea>
                        </div>
                        <button type="submit" id="btnKirimFeedback" class="btn btn-submit" style="margin-top: 15px; width: 100%; background: #67C090; color: white; padding: 12px; border: none; border-radius: 12px; font-weight: 600; cursor: pointer;">
                            <i class="fa-solid fa-paper-plane"></i> Kirim Masukan
                        </button>
                    </form>
                </div>
            </section>

        </main>

    </div>

    <script>
        // Logika Pengendali Navigasi SPA
        const menuItems = document.querySelectorAll('.sidebar-menu .menu-item');
        const contentSections = document.querySelectorAll('.content-section');

        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                // 1. Atur Class Active Pada Menu Sidebar
                menuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');

                // 2. Sembunyikan Semua Section Konten
                contentSections.forEach(section => {
                    section.classList.remove('section-active');
                });

                // 3. Tampilkan Hanya Section yang Sesuai
                const targetSectionId = this.getAttribute('data-section');
                const targetSection = document.getElementById(targetSectionId);
                if (targetSection) {
                    targetSection.classList.add('section-active');
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // 1. Meminta izin (Permission) memunculkan notifikasi ke sistem operasi/browser
            if (Notification.permission !== "granted" && Notification.permission !== "denied") {
                Notification.requestPermission();
            }

            // Variabel penampung agar notifikasi tidak muncul berulang-ulang di menit yang sama
            let sudahMuncul = [];

            // 2. Fungsi Scanner untuk mengecek jadwal ke database via AJAX
            function jalankanScannerReminder() {
                fetch('proses/cek_notifikasi.php')
                    .then(response => response.json())
                    .then(res => {
                        if (res.status === 'success' && res.data.length > 0) {
                            res.data.forEach(reminder => {
                                // Jika ID pengingat belum pernah dimunculkan pada sesi menit ini
                                if (!sudahMuncul.includes(reminder.id)) {

                                    // Eksekusi trigger notifikasi desktop/HP
                                    tampilkanNotifikasiSistem("CekC!ng Reminder 🔔", reminder.pesan);

                                    // Catat ID agar tidak spamming
                                    sudahMuncul.push(reminder.id);
                                }
                            });
                        } else {
                            // Reset antrean jika menit sudah berganti dan tidak ada transaksi aktif
                            sudahMuncul = [];
                        }
                    })
                    .catch(err => console.error("Gagal melakukan scan pengingat:", err));
            }

            // 3. Fungsi untuk memunculkan alert notifikasi fisik
            function tampilkanNotifikasiSistem(title, bodyText) {
                if (Notification.permission === "granted") {
                    const opsi = {
                        body: bodyText,
                        icon: 'assets/img/logo_cekcing.png', // Sesuaikan dengan path logo CekC!ng kamu
                        vibrate: [200, 100, 200],
                        requireInteraction: true // Notifikasi tetap menetap sampai di-close/klik oleh user
                    };

                    const notif = new Notification(title, opsi);

                    // Jika notifikasi diklik, arahkan user langsung ke tab pengingat
                    notif.onclick = function() {
                        window.focus();
                        document.querySelector('[data-section="pengingat-section"]').click();
                    };
                } else {
                    // Fallback alternatif menggunakan alert bawaan browser jika izin notifikasi diblokir
                    alert(bodyText);
                }
            }

            // Run otomatis pertama kali saat halaman dibuka
            jalankanScannerReminder();

            // Jalankan mesin pencari otomatis secara berkala setiap 60 detik (60000 ms)
            setInterval(jalankanScannerReminder, 60000);
        });


        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah reload halaman ke dashboard

            const btnSubmit = document.getElementById('btnKirimFeedback');
            const pesanInput = document.getElementById('pesanFeedback');

            // Ubah status tombol saat loading
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
            btnSubmit.disabled = true;

            const formData = new FormData(this);

            // Kirim data secara asinkron ke file PHP pemroses
            fetch('proses/proses_feedback_email.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Terima kasih! Feedback Anda berhasil dikirim ke tim pengembang.');
                        pesanInput.value = ''; // Kosongkan form kembali
                    } else {
                        alert('Gagal mengirim feedback: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan jaringan.');
                })
                .finally(() => {
                    // Kembalikan status tombol seperti semula
                    btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Masukan';
                    btnSubmit.disabled = false;
                });
        });

        // Logika Dropdown Profil
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