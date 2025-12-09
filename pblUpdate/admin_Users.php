<?php
$page_title = "Manajemen Pengguna - Admin LET Lab";
include_once 'includes/header.php';

// 1. CEK LOGIN
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    header("location: login.php");
    exit;
}

include_once 'config/database.php';
include_once 'models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$show_form = false;
$edit_mode = false;
$edit_data = null;
$error_msg = "";

// --- HANDLE FORM SUBMISSION (POST) ---
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Mapping Input
    $user->username = $_POST['username'];
    $user->full_name = $_POST['full_name'];
    $user->email = $_POST['email'] ?? '';
    // UBAH: Mengambil dari input identification_number
    $user->identification_number = $_POST['identification_number'] ?? '';
    $user->institution = $_POST['institution'] ?? '';
    $user->role = $_POST['role'];
    
    // Logic Student Type
    if($user->role == 'member'){
        $user->student_type = $_POST['student_type'] ?? 'regular';
    } else {
        $user->student_type = null;
    }
    
    $user->is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if(!empty($_POST['password'])){
        $user->password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    // A. TAMBAH PENGGUNA
    if(isset($_POST['add_user'])){
        if(empty($_POST['username']) || empty($_POST['password']) || empty($_POST['full_name'])){
            $error_msg = "Username, Password, dan Nama Lengkap wajib diisi!";
        } else {
            if($user->create()){
                $_SESSION['message'] = "Pengguna berhasil dibuat!";
                echo "<script>window.location.href='admin_users.php';</script>";
                exit;
            } else {
                $error_msg = !empty($user->error_message) ? $user->error_message : "Gagal membuat pengguna.";
            }
        }
    }

    // B. UPDATE PENGGUNA
    if(isset($_POST['update_user'])){
        $user->id = $_POST['user_id'];
        
        if($user->update()){
            $_SESSION['message'] = "Pengguna berhasil diperbarui!";
            echo "<script>window.location.href='admin_users.php';</script>";
            exit;
        } else {
            $error_msg = !empty($user->error_message) ? $user->error_message : "Gagal memperbarui pengguna.";
            // Keep form open
            $show_form = true;
            $edit_mode = true;
            $edit_data = $_POST; // Preserve input
            $edit_data['user_id'] = $_POST['user_id'];
        }
    }
}

// --- HANDLE DELETE (GET) ---
if(isset($_GET['delete_id'])){
    $user->id = $_GET['delete_id'];
    if($user->delete()){
        $_SESSION['message'] = "Pengguna berhasil dihapus!";
        echo "<script>window.location.href='admin_users.php';</script>";
        exit;
    }
}

// --- HANDLE SHOW FORM (GET) ---
if(isset($_GET['action']) && !$error_msg){
    if($_GET['action'] == 'add'){
        $show_form = true;
    } elseif($_GET['action'] == 'edit' && isset($_GET['id'])){
        $show_form = true;
        $edit_mode = true;
        $stmt = $user->read();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($row['user_id'] == $_GET['id']){
                $edit_data = $row;
                break;
            }
        }
    }
}

// AMBIL DATA STATISTIK (Penting agar kartu statistik tidak error)
$stats = $user->getUserStats();

// AMBIL LIST USER
$users = $user->read();
?>

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
            <a href="logout.php" class="btn btn-sm btn-outline-light">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </div>
</nav>

<div class="admin-container">
    
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <h5 class="mb-0">Navigasi</h5>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i><span>Dasbor</span></a>
            </li>
            <li class="menu-item active"><a href="admin_users.php"><i class="fas fa-users-cog me-2"></i><span>Pengguna</span></a></li>

            <li class="menu-item">
                <a href="admin_partners.php"><i class="fas fa-handshake me-2"></i><span>Mitra</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_team.php"><i class="fas fa-users me-2"></i><span>Tim</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_products.php"><i class="fas fa-box me-2"></i><span>Produk</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_news.php"><i class="fas fa-newspaper me-2"></i><span>Berita</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_gallery.php"><i class="fas fa-images me-2"></i><span>Galeri</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_activity.php"><i class="fas fa-chart-line me-2"></i><span>Aktivitas</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_booking.php"><i class="fas fa-calendar-check me-2"></i><span>Peminjaman</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_absent.php"><i class="fas fa-clipboard-list me-2"></i><span>Kehadiran</span></a>
            </li>
            <li class="menu-item">
            <a href="admin_guestbook.php"><i class="fas fa-envelope-open-text me-2"></i><span>Buku Tamu</span></a>
            </li>
        </ul>
    </div>

    <div class="admin-content">
        <div class="content-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Manajemen Pengguna</h1>
                    <p class="text-muted small">Kelola akun pengguna dan izin akses</p>
                </div>
                <?php if(!$show_form): ?>
                    <a href="admin_users.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Tambah Pengguna
                    </a>
                <?php else: ?>
                    <a href="admin_users.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(!$show_form): ?>
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-primary border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $stats['total_users']; ?></h3>
                        <p class="text-muted mb-0 small">Total Pengguna</p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-danger border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $stats['total_admins']; ?></h3>
                        <p class="text-muted mb-0 small">Admin</p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-warning border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $stats['total_dosen']; ?></h3>
                        <p class="text-muted mb-0 small">Dosen</p>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-info border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $stats['total_members']; ?></h3>
                        <p class="text-muted mb-0 small">Mahasiswa</p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-success border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $stats['active_users']; ?></h3>
                        <p class="text-muted mb-0 small">Aktif</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($show_form): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-<?php echo $edit_mode ? 'warning' : 'primary'; ?> text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'user-plus'; ?> me-2"></i>
                        <?php echo $edit_mode ? 'Edit Akun Pengguna' : 'Buat Pengguna Baru'; ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="admin_users.php">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="user_id" value="<?php echo $edit_data['user_id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Username *</label>
                                <input type="text" class="form-control" name="username" required 
                                       placeholder="Masukkan username"
                                       <?php echo $edit_mode ? 'readonly' : ''; ?>
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['username']) : ''; ?>">
                                <?php if($edit_mode): ?>
                                    <small class="form-text text-muted">Username tidak dapat diubah</small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nama Lengkap *</label>
                                <input type="text" class="form-control" name="full_name" required 
                                       placeholder="Masukkan nama lengkap"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['full_name']) : ''; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Password <?php echo $edit_mode ? '(Kosongkan jika tidak ingin diubah)' : '*'; ?></label>
                                <input type="password" class="form-control" name="password" 
                                       placeholder="Masukkan password"
                                       <?php echo !$edit_mode ? 'required' : ''; ?>>
                                <small class="form-text text-muted">Minimal 6 karakter</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nomor Induk</label>
                                <input type="text" class="form-control" name="identification_number" 
                                       placeholder="Nomor Induk untuk dosen & mahasiswa"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['identification_number'] ?? '') : ''; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" 
                                       placeholder="email@contoh.com"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['email']) : ''; ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Institusi</label>
                                <input type="text" class="form-control" name="institution" 
                                       placeholder="Contoh: Politeknik Negeri Malang"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['institution']) : ''; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Peran *</label>
                                <select class="form-select" name="role" id="role" required onchange="toggleStudentType()">
                                    <option value="">Pilih Peran</option>
                                    <option value="admin" <?php echo ($edit_mode && $edit_data['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="dosen" <?php echo ($edit_mode && $edit_data['role'] == 'dosen') ? 'selected' : ''; ?>>Dosen</option>
                                    <option value="member" <?php echo ($edit_mode && $edit_data['role'] == 'member') ? 'selected' : ''; ?>>Mahasiswa</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3" id="student_type_wrapper">
                                <label class="form-label">Jenis Mahasiswa</label>
                                <select class="form-select" name="student_type" id="student_type">
                                    <option value="regular" <?php echo ($edit_mode && ($edit_data['student_type'] ?? '') == 'regular') ? 'selected' : ''; ?>>Reguler</option>
                                    <option value="magang" <?php echo ($edit_mode && ($edit_data['student_type'] ?? '') == 'magang') ? 'selected' : ''; ?>>Magang</option>
                                    <option value="skripsi" <?php echo ($edit_mode && ($edit_data['student_type'] ?? '') == 'skripsi') ? 'selected' : ''; ?>>Skripsi</option>
                                </select>
                                <small class="form-text text-muted">Hanya untuk Mahasiswa</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                           <?php echo ($edit_mode && $edit_data['is_active']) || !$edit_mode ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Akun Aktif
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="<?php echo $edit_mode ? 'update_user' : 'add_user'; ?>" 
                                    class="btn btn-<?php echo $edit_mode ? 'warning' : 'primary'; ?> px-4">
                                <i class="fas fa-save me-1"></i>
                                <?php echo $edit_mode ? 'Perbarui Pengguna' : 'Buat Pengguna'; ?>
                            </button>
                            <a href="admin_users.php" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Daftar Akun Pengguna</h5>
                        <div class="badge bg-primary px-3 py-2">
                            <i class="fas fa-users me-1"></i>
                            <?php echo $users ? $users->rowCount() : 0; ?> Pengguna
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Username</th>
                                    <th width="20%">Nama Lengkap</th>
                                    <th width="12%">Nomor Induk</th>
                                    <th width="15%">Email</th>
                                    <th width="10%">Peran</th>
                                    <th width="10%">Jenis</th>
                                    <th width="8%">Status</th>
                                    <th width="15%" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($users->rowCount() > 0): ?>
                                    <?php while($user_data = $users->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $user_data['user_id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user_data['username']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($user_data['full_name']); ?></td>
                                        <td>
                                            <?php if(!empty($user_data['identification_number'])): ?>
                                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($user_data['identification_number']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($user_data['email']): ?>
                                                <small><?php echo htmlspecialchars($user_data['email']); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $role_badge = 'info';
                                            $role_icon = 'user';
                                            $role_label = 'Mahasiswa';
                                            
                                            if($user_data['role'] == 'admin'){
                                                $role_badge = 'danger';
                                                $role_icon = 'user-shield';
                                                $role_label = 'Admin';
                                            } elseif($user_data['role'] == 'dosen'){
                                                $role_badge = 'warning';
                                                $role_icon = 'chalkboard-teacher';
                                                $role_label = 'Dosen';
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $role_badge; ?>">
                                                <i class="fas fa-<?php echo $role_icon; ?> me-1"></i>
                                                <?php echo $role_label; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($user_data['role'] == 'member'): ?>
                                                <?php 
                                                $student_types = [
                                                    'regular' => 'Reguler',
                                                    'magang' => 'Magang',
                                                    'skripsi' => 'Skripsi'
                                                ];
                                                ?>
                                                <small class="text-muted"><?php echo $student_types[$user_data['student_type']] ?? ''; ?></small>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($user_data['is_active']): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="admin_users.php?action=edit&id=<?php echo $user_data['user_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php 
                                                $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                                                if($user_data['user_id'] != $current_user_id): 
                                                ?>
                                                <a href="admin_users.php?delete_id=<?php echo $user_data['user_id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger" title="Hapus"
                                                   onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                            <h5>Belum Ada Pengguna</h5>
                                            <p>Klik "Tambah Pengguna" untuk membuat akun pengguna pertama.</p>
                                        </td>
                                    </tr>
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
    .sidebar-header {
        text-align: center;
        padding: 1rem 1rem;
        border-bottom: 1px solid #06305aff;
        margin-bottom: 1rem;
    }
    .admin-container {
        background-color: #06305aff;
        min-height: 100vh;
    }
    .stats-card {
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-2px);
    }
    .table th {
        background-color: #06305aff;
        font-weight: 600;
        padding: 12px 16px;
    }
    .table td {
        padding: 12px 16px;
        vertical-align: middle;
    }
</style>

<script>
function toggleStudentType() {
    const role = document.getElementById('role').value;
    const studentTypeWrapper = document.getElementById('student_type_wrapper');
    const studentTypeSelect = document.getElementById('student_type');
    
    if(role === 'member') {
        studentTypeWrapper.style.display = 'block';
        studentTypeSelect.disabled = false;
        // studentTypeSelect.required = true; // Opsional tergantung kebutuhan
    } else {
        studentTypeWrapper.style.display = 'none';
        studentTypeSelect.disabled = true;
        studentTypeSelect.required = false;
        studentTypeSelect.value = 'regular'; // set default
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    if(roleSelect) {
        toggleStudentType();
    }
});
</script>

<?php include_once 'includes/footer.php'; ?>    