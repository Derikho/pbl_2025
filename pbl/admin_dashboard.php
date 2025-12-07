<?php
$page_title = "Dasbor Admin - LET Lab";
include_once 'includes/header.php';

// Cek sesi admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    exit;
}

include_once 'config/database.php';
include_once 'models/User.php';
include_once 'models/Activity.php';
include_once 'models/Booking.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$activity = new Activity($db);
$booking = new Booking($db);

$total_users = $user->getTotalUsers();
$total_activities = $activity->getTotalActivities();
$active_bookings = $booking->getActiveBookings();
?>

<!-- NAVBAR ADMIN -->
<nav class="navbar navbar-expand-lg navbar-admin sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="admin_dashboard.php">
            <div class="admin-logo">
                <i class="fas fa-crown me-2"></i>
                <span>Panel Admin</span>
            </div>
        </a>
        <div class="navbar-actions ms-auto">
            <div class="admin-info me-3 text-white">
                <i class="fas fa-user-shield me-1"></i>
                <span class="admin-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            <a href="#" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </div>
</nav>

<div class="admin-container">
    
    <!-- SIDEBAR -->
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <h5>Navigasi</h5>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item active">
                <a href="admin_dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    <span>Dasbor</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="admin_users.php"><i class="fas fa-users-cog me-2"></i><span>Pengguna</span></a>
            </li>

            <li class="menu-item">
                <a href="admin_partners.php">
                    <i class="fas fa-handshake me-2"></i>
                    <span>Mitra</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="admin_team.php">
                    <i class="fas fa-users me-2"></i>
                    <span>Tim</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="admin_products.php">
                    <i class="fas fa-box me-2"></i>
                    <span>Produk</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="admin_news.php">
                    <i class="fas fa-newspaper me-2"></i>
                    <span>Berita</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="admin_gallery.php">
                    <i class="fas fa-images me-2"></i>
                    <span>Galeri</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="admin_activity.php">
                    <i class="fas fa-chart-line me-2"></i>
                    <span>Aktivitas</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="admin_booking.php">
                    <i class="fas fa-calendar-check me-2"></i>
                    <span>Peminjaman</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="admin_absent.php">
                    <i class="fas fa-clipboard-list me-2"></i>
                    <span>Absensi</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="admin_guestbook.php">
                    <i class="fas fa-envelope-open-text me-2"></i>
                    <span>Buku Tamu</span>
                </a>
            </li>

        </ul>
    </div>

    <!-- CONTENT AREA -->
    <div class="admin-content">
        <div class="content-header">
            <h1>Dasbor Admin</h1>
            <p>Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        </div>

        <!-- STATISTICS -->
        <div class="row mb-4">

            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Pengguna</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo $active_bookings; ?></h3>
                        <p>Peminjaman Aktif</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo $total_activities; ?></h3>
                        <p>Total Aktivitas</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stats-info">
                        <h3>45</h3>
                        <p>Produk Terjual</p>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">

            <!-- RECENT ACTIVITIES -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Aktivitas Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pengguna</th>
                                        <th>Aktivitas</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Admin</td>
                                        <td>Memperbarui pengaturan sistem</td>
                                        <td>2 menit lalu</td>
                                    </tr>
                                    <tr>
                                        <td>User1</td>
                                        <td>Meminjam peralatan laboratorium</td>
                                        <td>1 jam lalu</td>
                                    </tr>
                                    <tr>
                                        <td>Admin</td>
                                        <td>Menambahkan produk baru</td>
                                        <td>3 jam lalu</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QUICK ACTIONS -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Aksi Cepat</h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">

                            <a href="admin_products.php" class="action-btn">
                                <i class="fas fa-plus"></i>
                                <span>Tambah Produk</span>
                            </a>

                            <a href="admin_news.php" class="action-btn">
                                <i class="fas fa-newspaper"></i>
                                <span>Tambah Berita</span>
                            </a>

                            <a href="admin_booking.php" class="action-btn">
                                <i class="fas fa-calendar"></i>
                                <span>Kelola Peminjaman</span>
                            </a>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<style>
.sidebar-header {
    text-align: center;
    padding: 1rem;
    border-bottom: 1px solid #06305aff;
    margin-bottom: 1rem;
}
</style>

<?php include_once 'includes/footer.php'; ?>
