<?php
$page_title = "Manajemen Tim - Admin LET Lab";
include_once 'includes/header.php';

// 1. CEK LOGIN
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    header("location: login.php");
    exit;
}

include_once 'config/database.php';
include_once 'models/Team.php';

$database = new Database();
$db = $database->getConnection();
$team = new Team($db);

// Variable kontrol
$show_form = false;
$edit_mode = false;
$edit_data = null;
$details_data = []; 

// --- FUNGSI HELPER UPLOAD GAMBAR ---
function uploadTeamPhoto($fileInputName, $targetDir = "uploads/team/") {
    // Buat folder jika belum ada
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($_FILES[$fileInputName]["name"]);
    $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Generate nama file unik (Timestamp + Random)
    $newFileName = time() . '_' . rand(100, 999) . '.' . $imageFileType;
    $targetFilePath = $targetDir . $newFileName;
    
    // Validasi ekstensi
    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    
    if(in_array($imageFileType, $allowedTypes)){
        if(move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)){
            return $targetFilePath;
        }
    }
    return false;
}

// --- HANDLE FORM SUBMISSION (POST) ---
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Helper Function untuk memproses data TEKS saja
    function processTeamData($team, $post) {
        $team->name = $post['name'];
        $team->position = $post['position'];
        $team->email = $post['email'] ?? '';
        $team->phone = $post['phone'] ?? '';
        $team->bio = $post['bio'] ?? '';
        // Note: Photo dihandle terpisah di logika utama
        $team->status = $post['status'] ?? 'active';
        
        // 1. Social Links
        $social_links = [];
        $keys = ['linkedin','twitter','facebook','instagram','github','website','sinta','google_scholar','researchgate','orcid','scopus'];
        foreach($keys as $key) { 
            if(!empty($post[$key])) $social_links[$key] = $post[$key]; 
        }
        $team->social_links = !empty($social_links) ? $social_links : null;

        // 2. Profile Details
        $profile_details = [];
        $profile_details['nip'] = $post['nip'] ?? '';
        $profile_details['nidn'] = $post['nidn'] ?? '';
        $profile_details['prodi'] = $post['prodi'] ?? '';
        
        if(!empty($post['education'])) {
            $profile_details['education'] = array_values(array_filter(array_map('trim', explode("\n", $post['education']))));
        } else {
            $profile_details['education'] = [];
        }

        if(!empty($post['certifications'])) {
            $profile_details['certifications'] = array_values(array_filter(array_map('trim', explode("\n", $post['certifications']))));
        } else {
            $profile_details['certifications'] = [];
        }

        $team->profile_details = $profile_details;
    }

    // A. TAMBAH ANGGOTA
    if(isset($_POST['add_member'])){
        if(empty($_POST['name']) || empty($_POST['position'])) {
            $error_msg = "Nama dan Posisi wajib diisi!";
        } else {
            processTeamData($team, $_POST);

            // Handle Upload Foto Baru
            $photoPath = ''; 
            if(!empty($_FILES["photo_file"]["name"])){
                $uploadResult = uploadTeamPhoto("photo_file");
                if($uploadResult){
                    $photoPath = $uploadResult;
                } else {
                    $error_msg = "Gagal upload gambar (Format: jpg, png, webp).";
                }
            }
            $team->photo = $photoPath;

            if(!isset($error_msg)){
                if($team->create()){
                    $_SESSION['message'] = "Anggota tim berhasil ditambahkan!";
                    echo "<script>window.location.href='admin_team.php';</script>";
                    exit;
                } else { $error_msg = "Gagal menyimpan ke database."; }
            }
        }
    }

    // B. UPDATE ANGGOTA
    if(isset($_POST['update_member'])){
        $team->id = $_POST['id'];
        processTeamData($team, $_POST);
        
        // Handle Upload Foto Edit
        if(!empty($_FILES["photo_file"]["name"])){
            // User upload foto baru
            $uploadResult = uploadTeamPhoto("photo_file");
            if($uploadResult){
                $team->photo = $uploadResult;
                
                // Hapus foto lama fisik jika ada
                if(!empty($_POST['existing_photo']) && file_exists($_POST['existing_photo'])){
                    unlink($_POST['existing_photo']);
                }
            } else {
                $error_msg = "Gagal upload gambar baru.";
            }
        } else {
            // User tidak upload, pakai foto lama
            $team->photo = $_POST['existing_photo'];
        }

        if(!isset($error_msg)){
            if($team->update()){
                $_SESSION['message'] = "Anggota tim berhasil diperbarui!";
                echo "<script>window.location.href='admin_team.php';</script>";
                exit;
            } else { $error_msg = "Gagal memperbarui data."; }
        }
    }
}

// C. HAPUS ANGGOTA
if(isset($_GET['delete_id'])){
    $delete_id = $_GET['delete_id'];
    
    // Ambil data dulu untuk hapus gambarnya
    $stmt = $db->prepare("SELECT photo_url FROM team_members WHERE member_id = ?");
    $stmt->execute([$delete_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $team->id = $delete_id;
    if($team->delete()){
        // Hapus file fisik
        if($row && !empty($row['photo_url']) && file_exists($row['photo_url'])){
            unlink($row['photo_url']);
        }

        $_SESSION['message'] = "Anggota berhasil dihapus!";
        echo "<script>window.location.href='admin_team.php';</script>";
        exit;
    }
}

// SHOW FORM (GET)
if(isset($_GET['action'])){
    if($_GET['action'] == 'add'){
        $show_form = true;
    } elseif($_GET['action'] == 'edit' && isset($_GET['id'])){
        $show_form = true;
        $edit_mode = true;
        $stmt = $team->read();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($row['id'] == $_GET['id']){
                $edit_data = $row;
                // Decode JSON Data
                if(!empty($edit_data['social_links'])) {
                    $edit_data['social_links'] = json_decode($edit_data['social_links'], true);
                }
                if(!empty($edit_data['profile_details'])) {
                    $details_data = json_decode($edit_data['profile_details'], true);
                }
                break;
            }
        }
    }
}

$team_members = $team->read();
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
        <div class="sidebar-header"><h5 class="mb-0">Navigasi</h5></div>
        <ul class="sidebar-menu">
            <li class="menu-item"><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i><span>Dashboard</span></a></li>
            <li class="menu-item"><a href="admin_users.php"><i class="fas fa-users-cog me-2"></i><span>Pengguna</span></a></li>
            <li class="menu-item"><a href="admin_partners.php"><i class="fas fa-handshake me-2"></i><span>Mitra</span></a></li>
            <li class="menu-item active"><a href="admin_team.php"><i class="fas fa-users me-2"></i><span>Tim</span></a></li>
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
                    <h1 class="h3 mb-0 text-gray-800">Manajemen Tim</h1>
                    <p class="text-muted small">Kelola anggota tim dan dosen</p>
                </div>
                <?php if(!$show_form): ?>
                    <a href="admin_team.php?action=add" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Anggota</a>
                <?php else: ?>
                    <a href="admin_team.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar</a>
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

        <?php if($show_form): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark py-3"> 
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i> <?php echo $edit_mode ? 'Edit Anggota Tim' : 'Tambah Anggota Baru'; ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="admin_team.php" enctype="multipart/form-data">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                            <input type="hidden" name="existing_photo" value="<?php echo htmlspecialchars($edit_data['photo']); ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nama Lengkap *</label>
                                <input type="text" class="form-control" name="name" required 
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['name']) : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Posisi *</label>
                                <input type="text" class="form-control" name="position" required 
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['position']) : ''; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo $edit_mode ? htmlspecialchars($edit_data['email']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telepon</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control" name="phone" 
                                           value="<?php echo $edit_mode ? htmlspecialchars($edit_data['phone']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Foto</label>
                            <div class="input-group mb-2">
                                <input type="file" class="form-control" name="photo_file" accept="image/*">
                            </div>
                            
                            <?php if($edit_mode && !empty($edit_data['photo'])): ?>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="small text-muted">Foto saat ini:</div>
                                    <img src="<?php echo htmlspecialchars($edit_data['photo']); ?>" 
                                         alt="Current" 
                                         style="height: 60px; width: 60px; object-fit: cover; border-radius: 50%; border: 1px solid #ddd;">
                                </div>
                            <?php endif; ?>
                            <small class="form-text text-muted">Rekomendasi: Gambar persegi (1:1), format JPG/PNG.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bio / Deskripsi</label>
                            <textarea class="form-control" name="bio" rows="3"><?php echo $edit_mode ? htmlspecialchars($edit_data['bio']) : ''; ?></textarea>
                        </div>

                        <div class="card bg-light border mb-4">
                            <div class="card-body">
                                <h6 class="text-primary border-bottom pb-2 mb-3 fw-bold">
                                    <i class="fas fa-university me-2"></i>Detail Akademik
                                </h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">NIP</label>
                                        <input type="text" class="form-control" name="nip" placeholder="Contoh: 1990..."
                                               value="<?php echo $edit_mode ? htmlspecialchars($details_data['nip'] ?? '') : ''; ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">NIDN</label>
                                        <input type="text" class="form-control" name="nidn" placeholder="Contoh: 001..."
                                               value="<?php echo $edit_mode ? htmlspecialchars($details_data['nidn'] ?? '') : ''; ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Program Studi</label>
                                        <input type="text" class="form-control" name="prodi" placeholder="Contoh: Teknik Informatika"
                                               value="<?php echo $edit_mode ? htmlspecialchars($details_data['prodi'] ?? '') : ''; ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Pendidikan</label>
                                        <textarea class="form-control" name="education" rows="3" placeholder="S1 - Nama Kampus&#10;S2 - Nama Kampus"><?php 
                                            if($edit_mode && !empty($details_data['education'])) {
                                                echo implode("\n", $details_data['education']);
                                            }
                                        ?></textarea>
                                        <small class="text-muted">Gunakan Enter untuk baris baru.</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Sertifikasi</label>
                                        <textarea class="form-control" name="certifications" rows="3" placeholder="Sertifikasi A&#10;Sertifikasi B"><?php 
                                            if($edit_mode && !empty($details_data['certifications'])) {
                                                echo implode("\n", $details_data['certifications']);
                                            }
                                        ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h6 class="border-bottom pb-2 mb-3 fw-bold">
                            <i class="fas fa-share-alt me-2"></i>Media Sosial
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">LinkedIn</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-linkedin text-primary"></i></span>
                                    <input type="url" class="form-control" name="linkedin" 
                                           value="<?php echo ($edit_mode && isset($edit_data['social_links']['linkedin'])) ? htmlspecialchars($edit_data['social_links']['linkedin']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Twitter</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-twitter text-info"></i></span>
                                    <input type="url" class="form-control" name="twitter" 
                                           value="<?php echo ($edit_mode && isset($edit_data['social_links']['twitter'])) ? htmlspecialchars($edit_data['social_links']['twitter']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Facebook</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-facebook text-primary"></i></span>
                                    <input type="url" class="form-control" name="facebook" 
                                           value="<?php echo ($edit_mode && isset($edit_data['social_links']['facebook'])) ? htmlspecialchars($edit_data['social_links']['facebook']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Instagram</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-instagram text-danger"></i></span>
                                    <input type="url" class="form-control" name="instagram" 
                                           value="<?php echo ($edit_mode && isset($edit_data['social_links']['instagram'])) ? htmlspecialchars($edit_data['social_links']['instagram']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3 mt-4 fw-bold">
                            <i class="fas fa-graduation-cap me-2"></i>Profil Akademik & Riset
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SINTA</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-graduation-cap text-warning"></i></span>
                                    <input type="url" class="form-control" name="sinta" 
                                           value="<?php echo ($edit_mode && isset($edit_data['social_links']['sinta'])) ? htmlspecialchars($edit_data['social_links']['sinta']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Google Scholar</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-book text-primary"></i></span>
                                    <input type="url" class="form-control" name="google_scholar" 
                                           value="<?php echo ($edit_mode && isset($edit_data['social_links']['google_scholar'])) ? htmlspecialchars($edit_data['social_links']['google_scholar']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ResearchGate</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-researchgate text-success"></i></span>
                                    <input type="url" class="form-control" name="researchgate" 
                                           value="<?php echo ($edit_mode && isset($edit_data['social_links']['researchgate'])) ? htmlspecialchars($edit_data['social_links']['researchgate']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ORCID</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card text-info"></i></span>
                                    <input type="url" class="form-control" name="orcid" 
                                           value="<?php echo ($edit_mode && isset($edit_data['social_links']['orcid'])) ? htmlspecialchars($edit_data['social_links']['orcid']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Scopus ID</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-flask text-danger"></i></span>
                                    <input type="url" class="form-control" name="scopus" 
                                           value="<?php echo ($edit_mode && isset($edit_data['social_links']['scopus'])) ? htmlspecialchars($edit_data['social_links']['scopus']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active" <?php echo ($edit_mode && $edit_data['status'] == 'active') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="inactive" <?php echo ($edit_mode && $edit_data['status'] == 'inactive') ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="<?php echo $edit_mode ? 'update_member' : 'add_member'; ?>" 
                                    class="btn btn-warning px-4 text-dark fw-bold">
                                <i class="fas fa-save me-1"></i> <?php echo $edit_mode ? 'Perbarui Perubahan' : 'Simpan Anggota'; ?>
                            </button>
                            <a href="admin_team.php" class="btn btn-secondary px-4"><i class="fas fa-times me-1"></i> Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <?php if($team_members->rowCount() > 0): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-white border-0 shadow-sm">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-users text-primary me-2"></i>
                                    <span class="fw-bold">Total Anggota Tim: <?php echo $team_members->rowCount(); ?></span>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Klik edit untuk mengubah data anggota
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <?php if($team_members->rowCount() > 0): ?>
                    <?php 
                    $stmt = $team->read(); // Reset pointer
                    while($member = $stmt->fetch(PDO::FETCH_ASSOC)): 
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body text-center">
                                <div class="mb-3 position-relative d-inline-block">
                                    <?php if(!empty($member['photo']) && file_exists($member['photo'])): ?>
                                        <img src="<?php echo htmlspecialchars($member['photo']); ?>" class="rounded-circle border p-1" style="width: 100px; height: 100px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px;"><i class="fas fa-user fa-3x text-secondary"></i></div>
                                    <?php endif; ?>
                                </div>
                                <h5 class="card-title fw-bold mb-1"><?php echo htmlspecialchars($member['name']); ?></h5>
                                <p class="text-primary small mb-2"><?php echo htmlspecialchars($member['position']); ?></p>
                                <div class="mt-3">
                                    <span class="badge bg-<?php echo $member['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo $member['status'] == 'active' ? 'Aktif' : 'Tidak Aktif'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0 pb-3 pt-0 text-center">
                                <a href="admin_team.php?action=edit&id=<?php echo $member['id']; ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit me-1"></i> Edit</a>
                                <a href="admin_team.php?delete_id=<?php echo $member['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus anggota ini? Foto akan ikut terhapus.')"><i class="fas fa-trash me-1"></i> Hapus</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="fas fa-users fa-3x mb-3 d-block"></i>
                        <h5>Belum Ada Anggota Tim</h5>
                        <p>Klik "Tambah Anggota" untuk menambahkan anggota pertama.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .sidebar-header { 
        text-align: center; 
        padding: 1rem; 
        border-bottom: 1px solid #06305aff; 
        margin-bottom: 1rem; 
    }
    .admin-container { 
        background-color: #06305aff; 
        min-height: 100vh; 
    }
    .table th {
        background-color: #06305aff;
        font-weight: 600;
        padding: 12px 16px;
    }
</style>

<?php include_once 'includes/footer.php'; ?>