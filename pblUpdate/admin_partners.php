<?php
$page_title = "Manajemen Mitra - Admin LET Lab";
include_once 'includes/header.php';

// Pastikan session aktif
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

include_once 'config/database.php';
include_once 'models/Partner.php';

$database = new Database();
$db = $database->getConnection();
$partner = new Partner($db);

$show_form = false;
$edit_mode = false;
$edit_data = null;

// --- FUNGSI HELPER UPLOAD GAMBAR ---
function uploadImage($fileInputName, $targetDir = "uploads/partners/") {
    // Cek apakah folder tujuan ada, jika tidak buat foldernya
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($_FILES[$fileInputName]["name"]);
    $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Generate nama file unik agar tidak bentrok (timestamp + random)
    $newFileName = time() . '_' . rand(100, 999) . '.' . $imageFileType;
    $targetFilePath = $targetDir . $newFileName;
    
    // Validasi ekstensi file
    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg');
    
    if(in_array($imageFileType, $allowedTypes)){
        // Upload file ke server
        if(move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)){
            return $targetFilePath; // Mengembalikan path file yang berhasil diupload
        }
    }
    return false; // Gagal upload
}

// --- LOGIKA POST (ADD & UPDATE) ---
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // A. LOGIKA TAMBAH MITRA
    if(isset($_POST['add_partner'])){
        if(empty($_POST['name'])) {
            $error_msg = "Nama mitra wajib diisi!";
        } else {
            $partner->name = $_POST['name'];
            $partner->description = $_POST['description'] ?? '';
            $partner->website = $_POST['website'] ?? '';
            $partner->status = $_POST['status'] ?? 'active';
            
            // Handle File Upload
            $logoPath = ''; // Default kosong
            if(!empty($_FILES["logo_image"]["name"])){
                $uploadResult = uploadImage("logo_image");
                if($uploadResult){
                    $logoPath = $uploadResult;
                } else {
                    $error_msg = "Gagal upload gambar. Pastikan format jpg/png/svg.";
                }
            }
            $partner->logo = $logoPath;

            if(!isset($error_msg)) {
                if($partner->create()){
                    $_SESSION['message'] = "Berhasil menambahkan mitra baru!";
                    echo "<script>window.location.href='admin_partners.php';</script>";
                    exit;
                } else {
                    $error_msg = "Gagal menyimpan ke database.";
                }
            }
        }
    }
    
    // B. LOGIKA UPDATE MITRA (EDIT)
    if(isset($_POST['update_partner'])){
        $partner->id = $_POST['id'];
        $partner->name = $_POST['name'];
        $partner->description = $_POST['description'];
        $partner->website = $_POST['website'];
        $partner->status = $_POST['status'];
        
        // Handle File Upload untuk Edit
        // Cek apakah user mengupload gambar baru?
        if(!empty($_FILES["logo_image"]["name"])){
            $uploadResult = uploadImage("logo_image");
            if($uploadResult){
                $partner->logo = $uploadResult; // Pakai gambar baru
                
                // (Opsional) Hapus gambar lama dari server jika perlu
                if(!empty($_POST['existing_logo']) && file_exists($_POST['existing_logo'])){
                    unlink($_POST['existing_logo']);
                }
            } else {
                $error_msg = "Gagal upload gambar baru.";
            }
        } else {
            // Jika tidak upload baru, pakai path gambar lama (dari hidden input)
            $partner->logo = $_POST['existing_logo'];
        }
        
        if(!isset($error_msg)){
            if($partner->update()){
                $_SESSION['message'] = "Data mitra berhasil diperbarui!";
                echo "<script>window.location.href='admin_partners.php';</script>";
                exit;
            } else {
                $error_msg = "Gagal memperbarui data.";
            }
        }
    }
}

// --- HANDLE DELETE (GET) ---
if(isset($_GET['delete_id'])){
    // Ambil info partner dulu untuk hapus filenya
    $delete_id = $_GET['delete_id'];
    $stmt = $db->prepare("SELECT logo_url FROM partners WHERE partner_id = ?");
    $stmt->execute([$delete_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $partner->id = $delete_id;
    if($partner->delete()){
        // Hapus file fisik jika ada
        if($row && !empty($row['logo_url']) && file_exists($row['logo_url'])){
            unlink($row['logo_url']);
        }

        $_SESSION['message'] = "Mitra berhasil dihapus!";
        echo "<script>window.location.href='admin_partners.php';</script>";
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
        $stmt = $partner->read();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($row['id'] == $_GET['id']){
                $edit_data = $row;
                break;
            }
        }
    }
}

// 3. AMBIL DATA TERAKHIR (READ)
$partners = $partner->read();
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
            <a href="#" class="btn btn-sm btn-outline-light" class="text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
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
            <li class="menu-item"><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i><span>Dashboard</span></a></li>
            <li class="menu-item"><a href="admin_users.php"><i class="fas fa-users-cog me-2"></i><span>Pengguna</span></a></li>
            <li class="menu-item active"><a href="admin_partners.php"><i class="fas fa-handshake me-2"></i><span>Mitra</span></a></li>
            <li class="menu-item"><a href="admin_team.php"><i class="fas fa-users me-2"></i><span>Tim</span></a></li>
            <li class="menu-item"><a href="admin_products.php"><i class="fas fa-box me-2"></i><span>Produk</span></a></li>
            <li class="menu-item"><a href="admin_news.php"><i class="fas fa-newspaper me-2"></i><span>Berita</span></a></li>
            <li class="menu-item"><a href="admin_gallery.php"><i class="fas fa-images me-2"></i><span>Galeri</span></a></li>
            <li class="menu-item"><a href="admin_activity.php"><i class="fas fa-chart-line me-2"></i><span>Aktivitas</span></a></li>
            <li class="menu-item"><a href="admin_booking.php"><i class="fas fa-calendar-check me-2"></i><span>Peminjaman</span></a></li>
            <li class="menu-item"><a href="admin_absent.php"><i class="fas fa-clipboard-list me-2"></i><span>Kehadiran</span></a></li>
            <li class="menu-item">
            <a href="admin_guestbook.php"><i class="fas fa-envelope-open-text me-2"></i><span>Buku Tamu</span></a>
            </li>
        </ul>
    </div>

    <div class="admin-content">
        <div class="content-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Manajemen Mitra</h1>
                    <p class="text-muted small">Kelola mitra organisasi Anda</p>
                </div>
                <?php if(!$show_form): ?>
                    <a href="admin_partners.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Tambah Mitra
                    </a>
                <?php else: ?>
                    <a href="admin_partners.php" class="btn btn-secondary">
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

        <?php if(isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if($show_form): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-<?php echo $edit_mode ? 'warning' : 'primary'; ?> text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'plus-circle'; ?> me-2"></i>
                        <?php echo $edit_mode ? 'Edit Mitra' : 'Tambah Mitra Baru'; ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="admin_partners.php" enctype="multipart/form-data">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                            <input type="hidden" name="existing_logo" value="<?php echo htmlspecialchars($edit_data['logo']); ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nama Mitra *</label>
                                <input type="text" class="form-control" name="name" required 
                                       placeholder="Contoh: Google, Microsoft"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['name']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo ($edit_mode && $edit_data['status'] == 'active') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="inactive" <?php echo ($edit_mode && $edit_data['status'] == 'inactive') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Deskripsi singkat tentang kemitraan..."><?php echo $edit_mode ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">URL Website</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                    <input type="url" class="form-control" name="website" 
                                           placeholder="https://contoh.com"
                                           value="<?php echo $edit_mode ? htmlspecialchars($edit_data['website']) : ''; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Logo Mitra</label>
                                <div class="input-group mb-2">
                                    <input type="file" class="form-control" name="logo_image" accept="image/*">
                                </div>
                                
                                <?php if($edit_mode && !empty($edit_data['logo'])): ?>
                                    <div class="small text-muted mb-2">
                                        Logo saat ini: <br>
                                        <img src="<?php echo htmlspecialchars($edit_data['logo']); ?>" alt="Current Logo" 
                                             style="height: 60px; margin-top: 5px; border: 1px solid #ddd; padding: 2px;">
                                    </div>
                                <?php endif; ?>
                                <small class="text-muted d-block">Format yang diperbolehkan: JPG, PNG, SVG, WEBP.</small>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="<?php echo $edit_mode ? 'update_partner' : 'add_partner'; ?>" 
                                    class="btn btn-<?php echo $edit_mode ? 'warning' : 'primary'; ?> px-4">
                                <i class="fas fa-save me-1"></i>
                                <?php echo $edit_mode ? 'Perbarui Data' : 'Simpan Mitra'; ?>
                            </button>
                            <a href="admin_partners.php" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <?php if($partners && $partners->rowCount() > 0): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-white border-0 shadow-sm">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-handshake text-primary me-2"></i>
                                    <span class="fw-bold">Total Mitra: <?php echo $partners->rowCount(); ?></span>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Klik edit untuk mengubah data mitra
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Daftar Mitra</h5>
                        <div class="badge bg-primary px-3 py-2">
                            <i class="fas fa-handshake me-1"></i>
                            <?php echo $partners ? $partners->rowCount() : 0; ?> Mitra
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Logo</th>
                                    <th>Nama</th>
                                    <th>Website</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($partners && $partners->rowCount() > 0): ?>
                                    <?php 
                                    $stmt = $partner->read(); // Reset pointer
                                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                                    ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td>
                                            <?php if(!empty($row['logo']) && file_exists($row['logo'])): ?>
                                                <img src="<?php echo htmlspecialchars($row['logo']); ?>" 
                                                     alt="Logo" class="rounded border p-1" 
                                                     style="width: 50px; height: 50px; object-fit: contain;">
                                            <?php elseif(!empty($row['logo'])): ?>
                                                <img src="<?php echo htmlspecialchars($row['logo']); ?>" 
                                                     alt="Missing" class="rounded border p-1" 
                                                     style="width: 50px; height: 50px; object-fit: contain; opacity: 0.5;">
                                            <?php else: ?>
                                                <div class="bg-light rounded border d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong class="text-dark"><?php echo htmlspecialchars($row['name']); ?></strong>
                                            <?php if(!empty($row['description'])): ?>
                                                <div class="text-muted small text-truncate" style="max-width: 250px;">
                                                    <?php echo htmlspecialchars($row['description']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(!empty($row['website'])): ?>
                                                <a href="<?php echo htmlspecialchars($row['website']); ?>" target="_blank" class="btn btn-sm btn-light border">
                                                    <i class="fas fa-external-link-alt small me-1"></i> Kunjungi
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo $row['status'] == 'active' ? 'Aktif' : 'Tidak Aktif'; ?>
                                            </span>
                                        </td>
                                        
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="admin_partners.php?action=edit&id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </a>
                                                
                                                <a href="admin_partners.php?delete_id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Yakin ingin menghapus mitra ini? File gambar juga akan dihapus.')">
                                                    <i class="fas fa-trash me-1"></i> Hapus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-handshake fa-3x mb-3 d-block"></i>
                                            <h5>Belum Ada Data Mitra</h5>
                                            <p>Klik "Tambah Mitra" untuk menambahkan mitra pertama.</p>
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
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
    .admin-container {
        background-color: #f8f9fa;
        min-height: 100vh;
    }
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        padding: 12px 16px;
    }
    .table td {
        padding: 12px 16px;
        vertical-align: middle;
    }
</style>

<?php include_once 'includes/footer.php'; ?>