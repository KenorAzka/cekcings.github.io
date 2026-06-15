<?php
session_start();
include 'proses/koneksi.php';

$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['user_id'])) {
    header("Location: loginNregist.php");
    exit();
}

$id_user = $_SESSION['user_id'];

$query_user = mysqli_query($conn, "SELECT username, email, foto_profil FROM users WHERE id_user = '$id_user'");
$data_user = mysqli_fetch_assoc($query_user);

// Definisikan variabel dari database, bukan dari $_SESSION lagi
$username = $data_user['username'];
$email = $data_user['email'];
$foto_profil = $data_user['foto_profil'];

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

// Ambil 5 aktivitas terbaru dari tabel history, diurutkan dari yang paling baru (DESC)
$query_recent =     "SELECT h.*, w.nama_barang 
                FROM history h 
                LEFT JOIN wishlists w ON h.id_wishlist = w.id_wishlist 
                WHERE h.id_user = '$id_user' 
                ORDER BY h.tanggal DESC";
$recent_result = mysqli_query($conn, $query_recent);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CekC!ng</title>
    <link rel="stylesheet" href="css/dashboard.css?v=1.4">
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
                        <a href="#"><i class="fa-solid fa-bell"></i> Reminder</a>
                    </li>

                    <li class="menu-item" data-section="recent-section">
                        <a href="#"><i class="fa-solid fa-history"></i> Recent</a>
                    </li>

                    <li class="menu-item" data-section="feedback-section">
                        <a href="#"><i class="fa-solid fa-comments"></i> Feedback</a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-profile">
                <div class="profile-box" id="profileToggle">

                    <div class="avatar">
                        <?php if (!empty($foto_profil) && file_exists($foto_profil)): ?>
                            <img src="<?php echo htmlspecialchars($foto_profil); ?>?t=<?php echo time(); ?>" alt="User Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block;">
                        <?php else: ?>
                            <i class="fa-solid fa-user"></i>
                        <?php endif; ?>
                    </div>

                    <div class="profile-info">
                        <span class="profile-name"><?php echo htmlspecialchars($username); ?></span>
                        <span class="profile-email"><?php echo htmlspecialchars($email); ?></span>
                    </div>
                    <i class="fa-solid fa-chevron-down arrow-down"></i>
                </div>

                <div class="profile-dropdown" id="profileDropdown">
                    <ul>
                        <li><a href="profile.php"><i class="fa-regular fa-user"></i> Lihat Profil</a></li>
                        <li><a href="proses/logout.php" class="logout-link"><i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar / Logout</a></li>
                    </ul>
                </div>
            </div>
        </aside>

        <main class="main-dashboard">
            <header class="dashboard-header">
                <div class="welcome-text">
                    <h4>Halo, <?php echo htmlspecialchars($username); ?>! 👋</h4>
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
                                <a href="#" class="view-all-link" style="color:#2563eb;" onclick="document.querySelector('[data-section=\'recent-section\']').click();">Lihat Semua</a>
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
                                <div class="price-info">
                                    <span class="total-price">Rp<?php echo number_format($target_harga, 0, ',', '.'); ?></span>

                                    <span class="weekly-target">
                                        Rp<?php echo number_format($target_mingguan, 0, ',', '.'); ?>
                                        <?php
                                        $tipe = $row['tipe_alokasi'];
                                        if ($tipe == 'harian') echo 'Per Day';
                                        elseif ($tipe == 'mingguan') echo 'Per Week';
                                        elseif ($tipe == 'bulanan') echo 'Per Month';
                                        elseif ($tipe == 'tahunan') echo 'Per Year';
                                        ?>
                                    </span>
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
                                            <span class="progress-title" style="color: #67C090;">Completed</span>
                                            <div class="progress-bar-bg">
                                                <div class="progress-bar-fill" style="width: 100%; background: #67C090;"></div>
                                            </div>
                                            <span class="progress-caption" style="color: #67C090; font-weight: 600;">Goal Achieved!</span>
                                        </div>
                                        <span class="percent-badge" style="background: #67C090; color: #111;">100%</span>
                                    </div>
                                </div>
                                <div class="card-bottom">
                                    <div class="price-info">
                                        <span class="total-price">Rp<?php echo number_format($target_harga, 0, ',', '.'); ?></span>
                                        <span class="weekly-target" style="color: #67C090;">Target Terpenuhi!</span>
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
                <div class="reminder-prof-header">
                    <div class="header-left">
                        <h2>Pengingat Menabung</h2>
                        <p class="header-subtitle">
                            Kelola jadwal notifikasi rutin agar target wishlist impianmu selesai tepat waktu.
                        </p>
                    </div>
                    <a href="add_reminder.php" class="add-savings-btn" style="display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-plus"></i> Add Reminder
                    </a>
                </div>

                <div class="reminder-prof-grid">
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
                            <div class="prof-reminder-card <?php echo $status_aktif ? '' : 'is-disabled'; ?>">

                                <div class="prof-card-top">
                                    <div class="prof-icon-wrapper">
                                        <i class="fa-solid fa-bell"></i>
                                    </div>
                                    <div class="prof-badge-status <?php echo $status_aktif ? 'status-active' : 'status-inactive'; ?>">
                                        <span class="prof-badge-dot"></span>
                                        <?php echo $status_aktif ? 'Aktif' : 'Nonaktif'; ?>
                                    </div>
                                </div>

                                <div class="prof-card-body">
                                    <h4 class="prof-reminder-title"><?php echo htmlspecialchars($rem['nama_barang']); ?></h4>
                                    <div class="prof-meta-row">
                                        <div class="prof-meta-item">
                                            <i class="fa-solid fa-calendar-days"></i>
                                            <span>Setiap <?php echo htmlspecialchars($rem['hari']); ?></span>
                                        </div>
                                        <div class="prof-meta-item">
                                            <i class="fa-solid fa-clock"></i>
                                            <span>Jam <?php echo $formatted_jam; ?> WIB</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="prof-card-actions">
                                    <a href="proses/toggle_reminder.php?id=<?php echo $rem['id_reminder']; ?>" class="prof-btn-action prof-btn-toggle">
                                        <i class="fa-solid fa-power-off"></i>
                                        <span><?php echo $status_aktif ? 'Matikan' : 'Aktifkan'; ?></span>
                                    </a>
                                    <a href="proses/hapus_reminder.php?id=<?php echo $rem['id_reminder']; ?>" onclick="return confirm('Hapus pengingat ini secara permanen?')" class="prof-btn-action prof-btn-delete">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </a>
                                </div>

                            </div>
                    <?php
                        }
                    } else {
                        echo "<div class='prof-empty-state'>
                <div class='prof-empty-icon'><i class='fa-regular fa-bell'></i></div>
                <h5>Belum ada pengingat</h5>
                <p>Jadwalkan notifikasi rutin untuk membantumu disiplin menabung.</p>
            </div>";
                    }
                    ?>
                </div>
            </section>

            <section class="recent-activities-box dashboard-content content-section" id="recent-section">
                <h3>
                    <i class="fa-solid fa-clock-rotate-left"></i> Recent Activities
                </h3>

                <ul class="activity-list">
                    <?php
                    if (mysqli_num_rows($recent_result) > 0) {
                        while ($act = mysqli_fetch_assoc($recent_result)) {
                            $is_pemasukan = ($act['jenis'] == 'Pemasukan');
                            $icon = $is_pemasukan ? 'fa-arrow-down-long' : 'fa-arrow-up-long';
                            $bg_icon = $is_pemasukan ? '#2ecc71' : '#e74c3c';
                            $tanda = $is_pemasukan ? '+' : '-';

                            // Menentukan teks aksi (Menabung / Menarik)
                            $aksi_teks = $is_pemasukan ? 'Menabung untuk' : 'Penarikan dari';
                    ?>
                            <li class="activity-item">
                                <div class="activity-left-content">
                                    <div class="activity-icon-badge" style="background: <?php echo $bg_icon; ?>;">
                                        <i class="fa-solid <?php echo $icon; ?>"></i>
                                    </div>
                                    <div>
                                        <div class="activity-title"><?php echo htmlspecialchars($act['label_kategori']); ?></div>

                                        <?php if (!empty($act['nama_barang'])): ?>
                                            <div class="activity-target-desc">
                                                <?php echo $aksi_teks; ?> <strong><?php echo htmlspecialchars($act['nama_barang']); ?></strong>
                                            </div>
                                        <?php endif; ?>

                                        <div class="activity-date"><?php echo date('d M Y, H:i', strtotime($act['tanggal'])); ?></div>
                                    </div>
                                </div>
                                <div class="activity-amount" style="color: <?php echo $bg_icon; ?>;">
                                    <?php echo $tanda; ?> Rp<?php echo number_format((float)$act['jumlah'], 0, ',', '.'); ?>
                                </div>
                            </li>
                    <?php
                        }
                    } else {
                        echo "<li style='text-align: center; padding: 30px 0; color: rgba(255,255,255,0.5); font-size: 0.85rem;'>Belum ada aktivitas finansial terakhir.</li>";
                    }
                    ?>
                </ul>
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
        // ==========================================================================
        // 1. LOGIKA PENGENDALI NAVIGASI SPA (SINGLE PAGE APPLICATION)
        // ==========================================================================
        const menuItems = document.querySelectorAll('.sidebar-menu .menu-item');
        const contentSections = document.querySelectorAll('.content-section');

        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                // Atur Class Active Pada Menu Sidebar
                menuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');

                // Sembunyikan Semua Section Konten
                contentSections.forEach(section => {
                    section.classList.remove('section-active');
                });

                // Tampilkan Hanya Section yang Sesuai
                const targetSectionId = this.getAttribute('data-section');
                const targetSection = document.getElementById(targetSectionId);
                if (targetSection) {
                    targetSection.classList.add('section-active');
                }
            });
        });

        // ==========================================================================
        // 2. ENGINE AUTOMATION REMINDER & NOTIFIKASI SISTEM (CSP COMPLIANT)
        // ==========================================================================
        document.addEventListener('DOMContentLoaded', function() {
            // Meminta izin (Permission) memunculkan notifikasi ke OS / Browser
            if (Notification.permission !== "granted" && Notification.permission !== "denied") {
                Notification.requestPermission();
            }

            // Variabel penampung ID agar tidak terjadi spamming notifikasi
            // MENGGUNAKAN LOCALSTORAGE: Data tetap tersimpan meski halaman di-refresh

            // Fungsi untuk mendapatkan reminder yang sudah ditampilkan menit ini
            function getSudahMunculReminders() {
                const key = 'cekcing_reminders_shown_' + Math.floor(new Date().getTime() / 60000); // Key berdasarkan menit
                const data = localStorage.getItem(key);
                return data ? JSON.parse(data) : [];
            }

            // Fungsi untuk menyimpan reminder yang sudah ditampilkan
            function saveSudahMunculReminder(reminderId) {
                const key = 'cekcing_reminders_shown_' + Math.floor(new Date().getTime() / 60000);
                const sudahMuncul = getSudahMunculReminders();

                if (!sudahMuncul.includes(reminderId)) {
                    sudahMuncul.push(reminderId);
                    localStorage.setItem(key, JSON.stringify(sudahMuncul));
                }

                // Cleanup: Hapus key lama (lebih dari 2 menit yang lalu)
                const currentMinute = Math.floor(new Date().getTime() / 60000);
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (key.startsWith('cekcing_reminders_shown_')) {
                        const minute = parseInt(key.split('_').pop());
                        if (currentMinute - minute > 2) {
                            localStorage.removeItem(key);
                        }
                    }
                }
            }

            // Fungsi Scanner untuk mengecek jadwal ke database via AJAX Fetch
            function jalankanScannerReminder() {
                const now = new Date();
                const hari_inggris = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][now.getDay()];
                const daftar_hari = {
                    'Sunday': 'Minggu',
                    'Monday': 'Senin',
                    'Tuesday': 'Selasa',
                    'Wednesday': 'Rabu',
                    'Thursday': 'Kamis',
                    'Friday': 'Jumat',
                    'Saturday': 'Sabtu'
                };
                const hari_ini = daftar_hari[hari_inggris];
                const jam_sekarang = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

                console.log(`🔍 [${now.toLocaleTimeString()}] SCANNER RUNNING - Hari: ${hari_ini}, Jam: ${jam_sekarang}`);

                fetch('proses/cek_notifikasi.php')
                    .then(response => response.json())
                    .then(res => {
                        console.log("📡 Response dari cek_notifikasi.php:", res);

                        if (res.status === 'error') {
                            console.error("❌ Error dari server:", res.message);
                            if (res.debug) console.log("🐛 Debug info:", res.debug);
                            return;
                        }

                        if (res.status === 'success' && res.data && res.data.length > 0) {
                            console.log(`✅ Ditemukan ${res.data.length} reminder(s) yang cocok!`); // DEBUG
                            const sudahMuncul = getSudahMunculReminders();
                            console.log("📋 Reminder yang sudah ditampilkan di menit ini:", sudahMuncul);

                            res.data.forEach(reminder => {
                                console.log(`🔔 Checking reminder ID: ${reminder.id}`);
                                // Jika ID pengingat belum pernah dimunculkan pada MENIT INI
                                if (!sudahMuncul.includes(reminder.id)) {
                                    console.log(`✔️ Reminder ID ${reminder.id} BARU - TAMPILKAN NOTIFIKASI!`);

                                    // Eksekusi trigger notifikasi desktop/HP
                                    tampilkanNotifikasiSistem("CekC!ng Reminder 🔔", reminder.pesan);

                                    // Catat ID ke localStorage agar tidak muncul lagi sebelum menit berganti
                                    saveSudahMunculReminder(reminder.id);

                                    console.log("💾 Reminder ID disimpan ke localStorage");
                                } else {
                                    console.log(`⏭️ Reminder ID ${reminder.id} sudah ditampilkan di menit ini, SKIP`);
                                }
                            });
                        } else {
                            console.log(`⏸️ Tidak ada reminder yang sesuai pada waktu ini (${hari_ini}, ${jam_sekarang})`);
                            if (res.debug) console.log("🐛 Debug info:", res.debug);
                        }
                    })
                    .catch(err => console.error("❌ Gagal melakukan scan pengingat:", err));
            }

            // Fungsi Notifikasi dengan Sistem Fallback Multi-Platform (Aman dari Eval)
            function tampilkanNotifikasiSistem(title, bodyText) {
                console.log("🔔 NOTIFIKASI TRIGGERED: " + title);
                console.log("   Pesan: " + bodyText);

                // 🎵 LANGSUNG MAINKAN AUDIO ALERT
                playNotificationSound();

                // 🟢 LANGSUNG TAMPILKAN VISUAL CARD (JANGAN TUNGGU NOTIFICATION API)
                tampilkanFallback(title, bodyText);

                // 🔔 TRY NOTIFICATION API SEBAGAI BONUS (di latar belakang)
                if (window.Notification && Notification.permission === "granted") {
                    try {
                        const opsi = {
                            body: bodyText,
                            icon: 'img/logo.png',
                            vibrate: [200, 100, 200],
                            requireInteraction: false,
                            tag: 'reminder-' + new Date().getTime()
                        };
                        new Notification(title, opsi);
                        console.log("✅ Notifikasi sistem OS juga ditrigger");
                    } catch (e) {
                        console.log("⚠️ Notification API tidak bisa dipakai:", e.message);
                    }
                }
            }

            // 🎵 Fungsi untuk memainkan audio notification
            function playNotificationSound() {
                try {
                    // Buat audio context dengan beep sound
                    const audioContext = new(window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);

                    // Set frekuensi dan durasi
                    oscillator.frequency.value = 800; // Hz
                    oscillator.type = 'sine';

                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.5);

                    console.log("🔊 Audio beep dimainkan");
                } catch (e) {
                    console.log("⚠️ Audio notification tidak bisa dijalankan:", e.message);
                }
            }

            // Fallback: Tampilkan notifikasi via visual alert di halaman
            function tampilkanFallback(title, bodyText) {
                console.log("📢 MENAMPILKAN VISUAL NOTIFICATION CARD");

                // Buat elemen notifikasi visual di halaman
                const notifEl = document.createElement('div');
                notifEl.style.cssText = `
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: linear-gradient(180deg, #124170, #1C5478);
                    color: white;
                    padding: 30px;
                    border-radius: 15px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
                    z-index: 99999;
                    max-width: 450px;
                    font-family: Poppins, sans-serif;
                    animation: popIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                    text-align: center;
                    border-left: 6px solid #ffffff;
                    border-right: 6px solid #ffffff;
                `;

                notifEl.innerHTML = `
                    <div style="
                        font-size: 3rem;
                        margin-bottom: 15px;
                        animation: bounce 0.6s ease-in-out;
                    ">🔔</div>
                    <div style="
                        font-weight: bold;
                        font-size: 1.4rem;
                        margin-bottom: 12px;
                        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
                    ">${title}</div>
                    <div style="
                        font-size: 1rem;
                        line-height: 1.6;
                        margin-bottom: 20px;
                        opacity: 0.95;
                    ">${bodyText}</div>
                    <div style="
                        display: flex;
                        gap: 10px;
                        justify-content: center;
                    ">
                        <button onclick="this.parentElement.parentElement.remove()" style="
                            background: rgba(255,255,255,0.3);
                            color: white;
                            border: 2px solid white;
                            padding: 10px 20px;
                            border-radius: 8px;
                            cursor: pointer;
                            font-size: 0.95rem;
                            font-weight: bold;
                            transition: all 0.3s;
                            font-family: Poppins, sans-serif;
                        " onmouseover="this.style.background='rgba(255,255,255,0.5); this.style.transform='scale(1.05)'" onmouseout="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='scale(1)'">
                            ✓ Tutup
                        </button>
                    </div>
                `;

                document.body.appendChild(notifEl);
                console.log("✅ Visual card ditampilkan di CENTER layar");

                // Hapus otomatis setelah 8 detik
                const autoCloseTimer = setTimeout(() => {
                    if (notifEl.parentElement) {
                        notifEl.style.animation = 'fadeOut 0.3s ease-out';
                        setTimeout(() => notifEl.remove(), 300);
                        console.log("📴 Card auto-close (timeout)");
                    }
                }, 8000);

                // Batalkan auto-close jika user klik tombol
                const closeBtn = notifEl.querySelector('button');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        clearTimeout(autoCloseTimer);
                    });
                }
            }

            // Tambah CSS animations untuk notifikasi
            const style = document.createElement('style');
            style.textContent = `
                @keyframes popIn {
                    0% {
                        transform: translate(-50%, -50%) scale(0.7);
                        opacity: 0;
                    }
                    50% {
                        transform: translate(-50%, -50%) scale(1.05);
                    }
                    100% {
                        transform: translate(-50%, -50%) scale(1);
                        opacity: 1;
                    }
                }

                @keyframes bounce {
                    0%, 100% { transform: translateY(0); }
                    50% { transform: translateY(-15px); }
                }

                @keyframes fadeOut {
                    from {
                        opacity: 1;
                    }
                    to {
                        opacity: 0;
                    }
                }

                @keyframes slideIn {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
            `;
            document.head.appendChild(style);

            // TEST MANUAL: Tambah button untuk test notifikasi
            console.log("%c🧪 DEBUG MODE: Ketik di console: testNotifikasi() untuk test notifikasi", "color: blue; font-weight: bold; font-size: 12px;");
            window.testNotifikasi = function() {
                tampilkanNotifikasiSistem("Test Notifikasi 🧪", "Ini adalah notifikasi test untuk debugging - seharusnya ada di TENGAH layar dengan BEEP sound");
            };

            // 1. Jalankan scanner pertama kali saat halaman siap dimuat
            jalankanScannerReminder();

            // 2. Loop Otomatis Pengganti setInterval (Lolos Validasi Aman Kebijakan CSP)
            function loopScannerAman() {
                setTimeout(() => {
                    jalankanScannerReminder();
                    loopScannerAman(); // Panggil fungsi kembali (rekursif)
                }, 10000); // ⚡ UNTUK TESTING: Eksekusi setiap 10 detik (ganti ke 60000 saat production)
            }

            // Mulai jalankan loop engine pengingat
            loopScannerAman();

            console.log("%c✅ REMINDER SYSTEM STARTED - Scanner akan berjalan setiap 10 detik", "color: green; font-weight: bold; font-size: 12px;");
        });

        // ==========================================================================
        // 3. HANDLER AJAX FORM SUBMIT FEEDBACK (VIA FETCH API)
        // ==========================================================================
        const feedbackForm = document.getElementById('feedbackForm');
        if (feedbackForm) {
            feedbackForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const btnSubmit = document.getElementById('btnKirimFeedback');
                const pesanInput = document.getElementById('pesanFeedback');

                // Ubah status komponen tombol menjadi mode Loading Animasi
                btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
                btnSubmit.disabled = true;

                const formData = new FormData(this);

                fetch('proses/proses_feedback_email.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Terima kasih! Feedback Anda berhasil dikirim ke tim pengembang.');
                            pesanInput.value = ''; // Reset isi teks area form
                        } else {
                            alert('Gagal mengirim feedback: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error Jaringan:', error);
                        alert('Terjadi kesalahan jaringan. Cek koneksi Anda.');
                    })
                    .finally(() => {
                        // Kembalikan visual tombol ke kondisi awal semula
                        btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Masukan';
                        btnSubmit.disabled = false;
                    });
            });
        }

        // ==========================================================================
        // 4. INTERAKSI TOGGLE DROPDOWN PROFIL SIDEBAR
        // ==========================================================================
        const profileToggle = document.getElementById('profileToggle');
        const profileDropdown = document.getElementById('profileDropdown');

        if (profileToggle && profileDropdown) {
            profileToggle.addEventListener('click', function(e) {
                e.stopPropagation(); // Mencegah event bubbling ke objek window
                profileDropdown.classList.toggle('active');
            });

            // Tutup dropdown secara otomatis jika pengguna mengklik area luar menu
            window.addEventListener('click', function() {
                if (profileDropdown.classList.contains('active')) {
                    profileDropdown.classList.remove('active');
                }
            });
        }
    </script>
</body>

</html>