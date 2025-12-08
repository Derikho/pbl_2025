<?php
$page_title = "Manajemen Kehadiran - LET Lab Admin";
include_once 'includes/header.php';

// 1. CEK LOGIN
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    header("location: login.php");
    exit;
}

include_once 'config/database.php';
include_once 'models/Attendance.php';

$database = new Database();
$db = $database->getConnection();
$attendance = new Attendance($db);

$show_form = false;
$edit_mode = false;
$edit_data = null;

// --- FUNGSI HELPER UPLOAD GAMBAR ---
function uploadProof($fileInputName, $targetDir = "uploads/attendance/") {
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $fileName = basename($_FILES[$fileInputName]["name"]);
    $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = time() . '_' . rand(100, 999) . '.' . $imageFileType;
    $targetFilePath = $targetDir . $newFileName;
    
    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    if(in_array($imageFileType, $allowedTypes)){
        if(move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)){
            return $targetFilePath;
        }
    }
    return false;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // A. TAMBAH KEHADIRAN
    if(isset($_POST['add_attendance'])){
        if(empty($_POST['user_id']) || empty($_POST['date'])) {
            $error_msg = "User dan Tanggal wajib diisi!";
        } else {
            $attendance->user_id = $_POST['user_id'];
            $attendance->date = $_POST['date'];
            $attendance->check_in_time = !empty($_POST['check_in_time']) ? $_POST['check_in_time'] : null;
            $attendance->check_out_time = !empty($_POST['check_out_time']) ? $_POST['check_out_time'] : null;
            $attendance->location_note = $_POST['location_note'] ?? '';
            
            // Handle Upload
            $photoPath = '';
            if(!empty($_FILES["photo_file"]["name"])){
                $uploadResult = uploadProof("photo_file");
                if($uploadResult) {
                    $photoPath = $uploadResult;
                } else {
                    $error_msg = "Gagal upload bukti foto.";
                }
            }
            $attendance->photo_proof = $photoPath;
            
            if(!isset($error_msg)){
                if($attendance->create()){
                    $_SESSION['message'] = "Data kehadiran berhasil ditambahkan!";
                    echo "<script>window.location.href='admin_absent.php';</script>";
                    exit;
                } else {
                    $error_msg = "Gagal menyimpan ke database.";
                }
            }
        }
    }

    // B. UPDATE KEHADIRAN
    if(isset($_POST['update_attendance'])){
        $attendance->id = $_POST['log_id'];
        $attendance->user_id = $_POST['user_id'];
        $attendance->date = $_POST['date'];
        $attendance->check_in_time = !empty($_POST['check_in_time']) ? $_POST['check_in_time'] : null;
        $attendance->check_out_time = !empty($_POST['check_out_time']) ? $_POST['check_out_time'] : null;
        $attendance->location_note = $_POST['location_note'];
        
        // Handle Upload Update
        if(!empty($_FILES["photo_file"]["name"])){
            $uploadResult = uploadProof("photo_file");
            if($uploadResult){
                $attendance->photo_proof = $uploadResult;
                // Hapus file lama
                if(!empty($_POST['existing_photo']) && file_exists($_POST['existing_photo'])){
                    unlink($_POST['existing_photo']);
                }
            } else {
                $error_msg = "Gagal upload bukti foto baru.";
            }
        } else {
            $attendance->photo_proof = $_POST['existing_photo'];
        }
        
        if(!isset($error_msg)){
            if($attendance->update()){
                $_SESSION['message'] = "Data kehadiran berhasil diperbarui!";
                echo "<script>window.location.href='admin_absent.php';</script>";
                exit;
            } else {
                $error_msg = "Gagal memperbarui data.";
            }
        }
    }
}

// --- HANDLE DELETE (GET) ---
if(isset($_GET['delete_id'])){
    $delete_id = $_GET['delete_id'];
    
    // Ambil path foto untuk dihapus
    $stmt = $db->prepare("SELECT photo_proof FROM attendance_logs WHERE log_id = ?");
    $stmt->execute([$delete_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $attendance->id = $delete_id;
    if($attendance->delete()){
        // Hapus file fisik
        if($row && !empty($row['photo_proof']) && file_exists($row['photo_proof'])){
            unlink($row['photo_proof']);
        }
        
        $_SESSION['message'] = "Data kehadiran berhasil dihapus!";
        echo "<script>window.location.href='admin_absent.php';</script>";
        exit;
    }
}

// --- HANDLE SHOW FORM (GET) ---
if(isset($_GET['action'])){
    if($_GET['action'] == 'add'){
        $show_form = true;
    } elseif($_GET['action'] == 'edit' && isset($_GET['id'])){
        $show_form = true;
        $edit_mode = true;
        $stmt = $attendance->read();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($row['id'] == $_GET['id']){
                $edit_data = $row;
                break;
            }
        }
    }
}

$attendance_logs = $attendance->read();

// PERBAIKAN: ganti nim jadi identification_number
$user_query = "SELECT user_id, full_name, identification_number FROM users WHERE role = 'member' AND is_active = true ORDER BY full_name";
$user_stmt = $db->prepare($user_query);
$user_stmt->execute();
$users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

$stats = $attendance->getStats();

function getAttendanceStatus($check_in_time) {
    if(empty($check_in_time)) return ['label' => 'Belum Check In', 'badge' => 'secondary'];
    $check_in = strtotime($check_in_time);
    $late_threshold = strtotime('08:00:00');
    if($check_in <= $late_threshold) {
        return ['label' => 'Tepat Waktu', 'badge' => 'success'];
    } else {
        return ['label' => 'Terlambat', 'badge' => 'warning'];
    }
}
?>

<style>
    .sidebar-header { text-align: center; padding: 1rem; border-bottom: 1px solid #dee2e6; margin-bottom: 1rem; }
    .admin-container { background-color: #f8f9fa; min-height: 100vh; }
    .stats-card { transition: transform 0.2s; }
    .stats-card:hover { transform: translateY(-2px); }
</style>

<nav class="navbar navbar-expand-lg navbar-admin sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="admin_dashboard.php">
            <div class="admin-logo"><i class="fas fa-crown me-2"></i><span>Panel Admin</span></div>
        </a>
        <div class="navbar-actions ms-auto">
            <div class="admin-info me-3 text-white">
                <i class="fas fa-user-shield me-1"></i>
                <span class="admin-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            <a href="logout.php" class="btn btn-sm btn-outline-light"><i class="fas fa-sign-out-alt me-1"></i>Keluar</a>
        </div>
    </div>
</nav>

<div class="admin-container">
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
            <li class="menu-item"><a href="admin_activity.php"><i class="fas fa-chart-line me-2"></i><span>Aktivitas</span></a></li>
            <li class="menu-item"><a href="admin_booking.php"><i class="fas fa-calendar-check me-2"></i><span>Peminjaman</span></a></li>
            <li class="menu-item active"><a href="admin_absent.php"><i class="fas fa-clipboard-list me-2"></i><span>Kehadiran</span></a></li>
            <li class="menu-item"><a href="admin_guestbook.php"><i class="fas fa-envelope-open-text me-2"></i><span>Buku Tamu</span></a></li>
        </ul>
    </div>

    <div class="admin-content">
        <div class="content-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Manajemen Kehadiran</h1>
                    <p class="text-muted small">Kelola catatan kehadiran mahasiswa</p>
                </div>
                <?php if(!$show_form): ?>
                    <a href="admin_absent.php?action=add" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Kehadiran</a>
                <?php else: ?>
                    <a href="admin_absent.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(!$show_form): ?>
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-primary border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $stats['total']; ?></h3>
                        <p class="text-muted mb-0 small">Total Hari Ini</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-success border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $stats['tepat_waktu']; ?></h3>
                        <p class="text-muted mb-0 small">Tepat Waktu</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-warning border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $stats['terlambat']; ?></h3>
                        <p class="text-muted mb-0 small">Terlambat</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-info border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $stats['sudah_keluar']; ?></h3>
                        <p class="text-muted mb-0 small">Sudah Pulang</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($show_form): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-<?php echo $edit_mode ? 'warning' : 'primary'; ?> text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'clipboard-list'; ?> me-2"></i>
                        <?php echo $edit_mode ? 'Edit Data Kehadiran' : 'Tambah Kehadiran Baru'; ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="admin_absent.php" enctype="multipart/form-data">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="log_id" value="<?php echo $edit_data['id']; ?>">
                            <input type="hidden" name="existing_photo" value="<?php echo htmlspecialchars($edit_data['photo_proof']); ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Mahasiswa *</label>
                                <select class="form-select" name="user_id" required <?php echo $edit_mode ? 'disabled' : ''; ?>>
                                    <option value="">Pilih Mahasiswa</option>
                                    <?php foreach($users as $user): ?>
                                        <option value="<?php echo $user['user_id']; ?>" 
                                                <?php echo ($edit_mode && $edit_data['user_id'] == $user['user_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['full_name']) . ' (' . htmlspecialchars($user['identification_number']) . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if($edit_mode): ?>
                                    <input type="hidden" name="user_id" value="<?php echo $edit_data['user_id']; ?>">
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tanggal *</label>
                                <input type="date" class="form-control" name="date" required
                                       value="<?php echo $edit_mode ? $edit_data['date'] : date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Masuk</label>
                                <input type="time" class="form-control" name="check_in_time"
                                       value="<?php echo $edit_mode ? $edit_data['check_in_time'] : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu Keluar</label>
                                <input type="time" class="form-control" name="check_out_time"
                                       value="<?php echo $edit_mode ? $edit_data['check_out_time'] : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bukti Foto</label>
                            <input type="file" class="form-control" name="photo_file" accept="image/*">
                            <?php if($edit_mode && !empty($edit_data['photo_proof'])): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Foto saat ini:</small><br>
                                    <img src="<?php echo htmlspecialchars($edit_data['photo_proof']); ?>" 
                                         alt="Current Proof" style="height: 60px; border: 1px solid #ddd; padding: 2px;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan Lokasi</label>
                            <textarea class="form-control" name="location_note" rows="3"><?php echo $edit_mode ? htmlspecialchars($edit_data['location_note']) : ''; ?></textarea>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="<?php echo $edit_mode ? 'update_attendance' : 'add_attendance'; ?>" 
                                    class="btn btn-<?php echo $edit_mode ? 'warning' : 'primary'; ?> px-4">
                                <i class="fas fa-save me-1"></i> <?php echo $edit_mode ? 'Perbarui Data' : 'Simpan Data'; ?>
                            </button>
                            <a href="admin_absent.php" class="btn btn-secondary px-4"><i class="fas fa-times me-1"></i> Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Data Kehadiran</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Bukti</th>
                                    <th>#</th>
                                    <th>Mahasiswa</th>
                                    <th>Tanggal</th>
                                    <th>Masuk</th>
                                    <th>Keluar</th>
                                    <th>Status</th>
                                    <th>Lokasi</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($attendance_logs->rowCount() > 0): ?>
                                    <?php while($log = $attendance_logs->fetch(PDO::FETCH_ASSOC)): 
                                        $status = getAttendanceStatus($log['check_in_time']);
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if(!empty($log['photo_proof']) && file_exists($log['photo_proof'])): ?>
                                                <img src="<?php echo htmlspecialchars($log['photo_proof']); ?>" 
                                                     alt="Proof" class="rounded border"
                                                     style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;"
                                                     onclick="window.open('<?php echo htmlspecialchars($log['photo_proof']); ?>', '_blank')">
                                            <?php else: ?>
                                                <div class="bg-light rounded border d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $log['id']; ?></td>
                                        <td>
                                            <strong class="d-block"><?php echo htmlspecialchars($log['name']); ?></strong>
                                            <small class="text-muted"><?php echo htmlspecialchars($log['identification_number']); ?></small>
                                        </td>
                                        <td><small><?php echo date('d M Y', strtotime($log['date'])); ?></small></td>
                                        <td>
                                            <?php if(!empty($log['check_in_time'])): ?>
                                                <span class="badge bg-light text-dark border"><i class="fas fa-sign-in-alt me-1"></i><?php echo date('H:i', strtotime($log['check_in_time'])); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(!empty($log['check_out_time'])): ?>
                                                <span class="badge bg-light text-dark border"><i class="fas fa-sign-out-alt me-1"></i><?php echo date('H:i', strtotime($log['check_out_time'])); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-<?php echo $status['badge']; ?>"><?php echo $status['label']; ?></span></td>
                                        <td><small><?php echo !empty($log['location_note']) ? htmlspecialchars(substr($log['location_note'], 0, 20)) . '...' : '-'; ?></small></td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="admin_absent.php?action=edit&id=<?php echo $log['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                                <a href="admin_absent.php?delete_id=<?php echo $log['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus data ini?')"><i class="fas fa-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="9" class="text-center py-5 text-muted">Belum Ada Data Kehadiran</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>