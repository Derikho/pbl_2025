<?php
$page_title = "Guest Book - LET Lab Admin";
include_once 'includes/header.php';

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    header("location: login.php");
    exit;
}

include_once 'config/database.php';
include_once 'models/GuestBook.php';

$database = new Database();
$db = $database->getConnection();
$guestbook = new GuestBook($db);

// Handle Delete
if(isset($_GET['delete_id'])){
    $guestbook->guest_id = $_GET['delete_id'];
    if($guestbook->delete()){
        $msg = "Pesan berhasil dihapus.";
        $msg_type = "success";
    } else {
        $msg = "Gagal menghapus pesan.";
        $msg_type = "danger";
    }
}

$messages = $guestbook->read();
?>

<nav class="navbar navbar-expand-lg navbar-admin sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="admin_dashboard.php">
            <div class="admin-logo">
                <i class="fas fa-crown me-2"></i>
                <span>Admin Panel</span>
            </div>
        </a>
        <div class="navbar-actions ms-auto">
            <div class="admin-info me-3 text-white">
                <i class="fas fa-user-shield me-1"></i>
                <span class="admin-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            <a href="#" class="btn btn-sm btn-outline-light" class="text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </div>
    </div>
</nav>

<div class="admin-container">
    <div class="admin-sidebar">
        <div class="sidebar-header"><h5 class="mb-0">Navigation</h5></div>
        <ul class="sidebar-menu">
            <li class="menu-item"><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i><span>Dashboard</span></a></li>
            <li class="menu-item"><a href="admin_users.php"><i class="fas fa-users-cog me-2"></i><span>Users</span></a></li>
            <li class="menu-item"><a href="admin_partners.php"><i class="fas fa-handshake me-2"></i><span>Partners</span></a></li>
            <li class="menu-item"><a href="admin_team.php"><i class="fas fa-users me-2"></i><span>Team</span></a></li>
            <li class="menu-item"><a href="admin_products.php"><i class="fas fa-box me-2"></i><span>Products</span></a></li>
            <li class="menu-item"><a href="admin_news.php"><i class="fas fa-newspaper me-2"></i><span>News</span></a></li>
            <li class="menu-item"><a href="admin_gallery.php"><i class="fas fa-images me-2"></i><span>Gallery</span></a></li>
            <li class="menu-item"><a href="admin_activity.php"><i class="fas fa-chart-line me-2"></i><span>Activity</span></a></li>
            <li class="menu-item"><a href="admin_booking.php"><i class="fas fa-calendar-check me-2"></i><span>Booking</span></a></li>
            <li class="menu-item"><a href="admin_absent.php"><i class="fas fa-clipboard-list me-2"></i><span>Absent</span></a></li>
            <li class="menu-item active"><a href="admin_guestbook.php"><i class="fas fa-envelope-open-text me-2"></i><span>Guest Book</span></a></li>
        </ul>
    </div>

    <div class="admin-content">
        <div class="content-wrapper">
            <div class="content-header mb-4">
                <h1 class="h3 mb-0 text-gray-800">Guest Book (Pesan Masuk)</h1>
                <p class="text-muted small">Pesan dan pertanyaan dari pengunjung website.</p>
            </div>

            <?php if(isset($msg)): ?>
                <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Pengirim</th>
                                    <th>Kontak</th>
                                    <th>Pesan</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($messages->rowCount() > 0): ?>
                                    <?php while($row = $messages->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td style="width: 120px;">
                                            <small class="fw-bold"><?php echo date('d M Y', strtotime($row['created_at'])); ?></small><br>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($row['created_at'])); ?></small>
                                        </td>
                                        <td style="width: 200px;">
                                            <strong><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
                                            <small class="text-muted"><i class="fas fa-building me-1"></i> <?php echo htmlspecialchars($row['institution']); ?></small>
                                        </td>
                                        <td style="width: 200px;">
                                            <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="text-decoration-none d-block mb-1">
                                                <i class="fas fa-envelope me-1 text-primary"></i> <?php echo htmlspecialchars($row['email']); ?>
                                            </a>
                                            <a href="https://wa.me/<?php echo htmlspecialchars($row['phone_number']); ?>" target="_blank" class="text-decoration-none text-success">
                                                <i class="fab fa-whatsapp me-1"></i> <?php echo htmlspecialchars($row['phone_number']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="p-2 bg-light rounded border">
                                                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <a href="admin_guestbook.php?delete_id=<?php echo $row['guest_id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Hapus pesan ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">Belum ada pesan masuk.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 1rem;
}
    .admin-container {
        background-color: #f8f9fa;
        min-height: 100vh;
    }
    
</style>

<?php include_once 'includes/footer.php'; ?>