<?php
$page_title = "Manajemen Aktivitas - LET Lab Admin";
include_once 'includes/header.php';

// 1. Cek Login
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    header("location: login.php");
    exit;
}

include_once 'config/database.php';
include_once 'models/Activity.php';

$database = new Database();
$db = $database->getConnection();
$activity = new Activity($db);

// --- HANDLE POST REQUEST ---
$show_form = false;
$edit_mode = false;
$edit_data = null;

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    $current_user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 1;

    if(isset($_POST['add_activity'])){
        $activity->title = $_POST['title'];
        $activity->activity_type = $_POST['activity_type'];
        $activity->description = $_POST['description'];
        $activity->activity_date = $_POST['activity_date'];
        $activity->location = $_POST['location'];
        $activity->status = $_POST['status'];
        $activity->link = $_POST['link'];
        $activity->user_id = $current_user_id;
        
        if($activity->create()){
            $_SESSION['message'] = "Aktivitas berhasil ditambahkan!";
            echo "<script>window.location.href='admin_activity.php';</script>";
            exit;
        }
    }
    
    if(isset($_POST['update_activity'])){
        $activity->activity_id = $_POST['id'];
        $activity->title = $_POST['title'];
        $activity->activity_type = $_POST['activity_type'];
        $activity->description = $_POST['description'];
        $activity->activity_date = $_POST['activity_date'];
        $activity->location = $_POST['location'];
        $activity->status = $_POST['status'];
        $activity->link = $_POST['link'];
        
        if($activity->update()){
            $_SESSION['message'] = "Aktivitas berhasil diperbarui!";
            echo "<script>window.location.href='admin_activity.php';</script>";
            exit;
        }
    }
}

if(isset($_GET['delete_id'])){
    $activity->activity_id = $_GET['delete_id'];
    if($activity->delete()){
        $_SESSION['message'] = "Aktivitas berhasil dihapus!";
        echo "<script>window.location.href='admin_activity.php';</script>";
        exit;
    }
}

if(isset($_GET['action'])){
    if($_GET['action'] == 'add'){
        $show_form = true;
    } elseif($_GET['action'] == 'edit' && isset($_GET['id'])){
        $show_form = true;
        $edit_mode = true;
        $stmt = $activity->read();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($row['activity_id'] == $_GET['id']){
                $edit_data = $row;
                break;
            }
        }
    }
}

$activities = $activity->read();
?>

<!-- NAVBAR -->
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
        <div class="sidebar-header"><h5 class="mb-0">Navigasi</h5></div>
        <ul class="sidebar-menu">
            <li class="menu-item"><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i><span>Dashboard</span></a></li>
            <li class="menu-item"><a href="admin_users.php"><i class="fas fa-users-cog me-2"></i><span>Pengguna</span></a></li>
            <li class="menu-item"><a href="admin_partners.php"><i class="fas fa-handshake me-2"></i><span>Mitra</span></a></li>
            <li class="menu-item"><a href="admin_team.php"><i class="fas fa-users me-2"></i><span>Tim</span></a></li>
            <li class="menu-item"><a href="admin_products.php"><i class="fas fa-box me-2"></i><span>Produk</span></a></li>
            <li class="menu-item"><a href="admin_news.php"><i class="fas fa-newspaper me-2"></i><span>Berita</span></a></li>
            <li class="menu-item"><a href="admin_gallery.php"><i class="fas fa-images me-2"></i><span>Galeri</span></a></li>
            <li class="menu-item active"><a href="admin_activity.php"><i class="fas fa-chart-line me-2"></i><span>Aktivitas</span></a></li>
            <li class="menu-item"><a href="admin_booking.php"><i class="fas fa-calendar-check me-2"></i><span>Peminjaman</span></a></li>
            <li class="menu-item"><a href="admin_absent.php"><i class="fas fa-clipboard-list me-2"></i><span>Absensi</span></a></li>
            <li class="menu-item"><a href="admin_guestbook.php"><i class="fas fa-envelope-open-text me-2"></i><span>Buku Tamu</span></a></li>
        </ul>
    </div>

    <!-- CONTENT -->
    <div class="admin-content">
        <div class="content-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">Manajemen Aktivitas</h1>

                <?php if(!$show_form): ?>
                    <a href="admin_activity.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Tambah Aktivitas
                    </a>
                <?php else: ?>
                    <a href="admin_activity.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if($show_form): ?>
            <!-- FORM TAMBAH / EDIT -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-<?php echo $edit_mode ? 'warning' : 'primary'; ?> text-white py-3">
                    <h5 class="card-title mb-0">
                        <?php echo $edit_mode ? 'Edit Aktivitas' : 'Tambah Aktivitas Baru'; ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="admin_activity.php">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['activity_id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Judul Aktivitas *</label>
                                <input type="text" class="form-control" name="title" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="activity_date" required
                                       value="<?php echo $edit_mode ? $edit_data['activity_date'] : date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Aktivitas</label>
                                <select name="activity_type" class="form-select">
                                    <option value="Research" <?php echo ($edit_mode && $edit_data['activity_type']=='Research')?'selected':''; ?>>Penelitian</option>
                                    <option value="Conference" <?php echo ($edit_mode && $edit_data['activity_type']=='Conference')?'selected':''; ?>>Konferensi</option>
                                    <option value="Other" <?php echo ($edit_mode && $edit_data['activity_type']=='Other')?'selected':''; ?>>Lainnya</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="completed" <?php echo ($edit_mode && $edit_data['status']=='completed')?'selected':''; ?>>Selesai</option>
                                    <option value="ongoing" <?php echo ($edit_mode && $edit_data['status']=='ongoing')?'selected':''; ?>>Berlangsung</option>
                                    <option value="planned" <?php echo ($edit_mode && $edit_data['status']=='planned')?'selected':''; ?>>Direncanakan</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Link Video YouTube *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-danger text-white">
                                    <i class="fab fa-youtube"></i>
                                </span>
                                <input type="url" class="form-control" name="link" required
                                       placeholder="https://www.youtube.com/watch?v=..."
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['link']) : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="4"><?php 
                                echo $edit_mode ? htmlspecialchars($edit_data['description']) : ''; 
                            ?></textarea>
                        </div>

                        <button type="submit" name="<?php echo $edit_mode ? 'update_activity' : 'add_activity'; ?>" class="btn btn-primary px-4">
                            Simpan
                        </button>
                    </form>
                </div>
            </div>

        <?php else: ?>

            <!-- TABEL DATA AKTIVITAS -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Judul</th>
                                    <th>Tanggal</th>
                                    <th>Link Video</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($activities->rowCount() > 0): ?>
                                    <?php while($row = $activities->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                                        <td><?php echo date('d M Y', strtotime($row['activity_date'])); ?></td>
                                        <td>
                                            <?php if(!empty($row['link'])): ?>
                                                <a href="<?php echo htmlspecialchars($row['link']); ?>" target="_blank"
                                                   class="btn btn-sm btn-outline-danger">
                                                   <i class="fab fa-youtube"></i> Buka Link
                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="admin_activity.php?action=edit&id=<?php echo $row['activity_id']; ?>"
                                                   class="btn btn-sm btn-outline-primary">
                                                   <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="admin_activity.php?delete_id=<?php echo $row['activity_id']; ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Hapus aktivitas ini?')">
                                                   <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<style>
    .sidebar-header { text-align: center; padding: 1rem; border-bottom: 1px solid #dee2e6; margin-bottom: 1rem; }
    .admin-container { background-color: #f8f9fa; min-height: 100vh; }
</style>

<?php include_once 'includes/footer.php'; ?>
