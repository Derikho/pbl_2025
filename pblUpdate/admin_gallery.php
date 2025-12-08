<?php
$page_title = "Manajemen Galeri - Admin LET Lab";
include_once 'includes/header.php';

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    header("location: login.php");
    exit;
}

include_once 'config/database.php';
include_once 'models/Gallery.php';

$database = new Database();
$db = $database->getConnection();
$gallery = new Gallery($db);

// Variabel untuk kontrol tampilan form
$show_form = false;
$edit_mode = false;
$edit_data = null;

// --- HANDLE SUBMISI FORM (POST) ---
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // A. TAMBAH GALLERY (CREATE)
    if(isset($_POST['add_gallery'])){
        if(empty($_POST['title']) || empty($_POST['image_url'])) {
            $error_msg = "Judul dan URL Gambar wajib diisi!";
        } else {
            $gallery->title = $_POST['title'];
            $gallery->description = $_POST['description'] ?? '';
            $gallery->image_url = $_POST['image_url'];
            $gallery->category = $_POST['category'] ?? 'events';
            $gallery->status = $_POST['status'] ?? 'active';
            
            if($gallery->create()){
                $_SESSION['message'] = "Item galeri berhasil ditambahkan!";
                echo "<script>window.location.href='admin_gallery.php';</script>";
                exit;
            } else {
                $error_msg = "Gagal menyimpan ke database.";
            }
        }
    }
    
    // B. UPDATE GALLERY
    if(isset($_POST['update_gallery'])){
        $gallery->id = $_POST['id'];
        $gallery->title = $_POST['title'];
        $gallery->description = $_POST['description'];
        $gallery->image_url = $_POST['image_url'];
        $gallery->category = $_POST['category'];
        $gallery->status = $_POST['status'];
        
        if($gallery->update()){
            $_SESSION['message'] = "Item galeri berhasil diperbarui!";
            echo "<script>window.location.href='admin_gallery.php';</script>";
            exit;
        } else {
            $error_msg = "Gagal memperbarui data.";
        }
    }
}

// --- HANDLE HAPUS (GET) ---
if(isset($_GET['delete_id'])){
    $gallery->id = $_GET['delete_id'];
    if($gallery->delete()){
        $_SESSION['message'] = "Item galeri berhasil dihapus!";
        echo "<script>window.location.href='admin_gallery.php';</script>";
        exit;
    }
}

if(isset($_GET['action'])){
    if($_GET['action'] == 'add'){
        $show_form = true;
    } elseif($_GET['action'] == 'edit' && isset($_GET['id'])){
        $show_form = true;
        $edit_mode = true;
        $stmt = $gallery->read();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($row['id'] == $_GET['id']){
                $edit_data = $row;
                break;
            }
        }
    }
}

$gallery_items = $gallery->read();
?>

<!-- Admin Navbar -->
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
            <li class="menu-item"><a href="admin_partners.php"><i class="fas fa-handshake me-2"></i><span>Mitra</span></a></li>
            <li class="menu-item"><a href="admin_team.php"><i class="fas fa-users me-2"></i><span>Tim</span></a></li>
            <li class="menu-item"><a href="admin_products.php"><i class="fas fa-box me-2"></i><span>Produk</span></a></li>
            <li class="menu-item"><a href="admin_news.php"><i class="fas fa-newspaper me-2"></i><span>Berita</span></a></li>
            <li class="menu-item active"><a href="admin_gallery.php"><i class="fas fa-images me-2"></i><span>Galeri</span></a></li>
            <li class="menu-item"><a href="admin_activity.php"><i class="fas fa-chart-line me-2"></i><span>Aktivitas</span></a></li>
            <li class="menu-item"><a href="admin_booking.php"><i class="fas fa-calendar-check me-2"></i><span>Peminjaman</span></a></li>
            <li class="menu-item"><a href="admin_absent.php"><i class="fas fa-clipboard-list me-2"></i><span>Absensi</span></a></li>
            <li class="menu-item"><a href="admin_guestbook.php"><i class="fas fa-envelope-open-text me-2"></i><span>Buku Tamu</span></a></li>
        </ul>
    </div>

    <div class="admin-content">
        <div class="content-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Manajemen Galeri</h1>
                    <p class="text-muted small">Kelola foto & dokumentasi Lab LET</p>
                </div>
                <?php if(!$show_form): ?>
                    <a href="admin_gallery.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Tambah Item Galeri
                    </a>
                <?php else: ?>
                    <a href="admin_gallery.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Galeri
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?php if($show_form): ?>
            <!-- FORM TAMBAH/EDIT GALERI -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-<?php echo $edit_mode ? 'warning' : 'primary'; ?> text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'plus-circle'; ?> me-2"></i>
                        <?php echo $edit_mode ? 'Edit Item Galeri' : 'Tambah Item Galeri Baru'; ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="admin_gallery.php">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Judul *</label>
                                <input type="text" class="form-control" name="title" required 
                                       placeholder="Contoh: Workshop IoT 2024"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Kategori *</label>
                                <select class="form-select" name="category" required>
                                    <option value="events" <?php echo ($edit_mode && $edit_data['category'] == 'events') ? 'selected' : ''; ?>>Acara</option>
                                    <option value="research" <?php echo ($edit_mode && $edit_data['category'] == 'research') ? 'selected' : ''; ?>>Penelitian</option>
                                    <option value="facilities" <?php echo ($edit_mode && $edit_data['category'] == 'facilities') ? 'selected' : ''; ?>>Fasilitas</option>
                                    <option value="team" <?php echo ($edit_mode && $edit_data['category'] == 'team') ? 'selected' : ''; ?>>Tim</option>
                                    <option value="products" <?php echo ($edit_mode && $edit_data['category'] == 'products') ? 'selected' : ''; ?>>Produk</option>
                                    <option value="documentation" <?php echo ($edit_mode && $edit_data['category'] == 'documentation') ? 'selected' : ''; ?>>Dokumentasi</option>
                                    <option value="workshop" <?php echo ($edit_mode && $edit_data['category'] == 'workshop') ? 'selected' : ''; ?>>Workshop</option>
                                    <option value="seminar" <?php echo ($edit_mode && $edit_data['category'] == 'seminar') ? 'selected' : ''; ?>>Seminar</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Deskripsi singkat item galeri ini..."><?php echo $edit_mode ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">URL Gambar *</label>
                                <input type="url" class="form-control" name="image_url" required 
                                       placeholder="https://contoh.com/gambar.jpg"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['image_url']) : ''; ?>">
                                <small class="form-text text-muted">Masukkan URL lengkap dari gambar</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="active" <?php echo ($edit_mode && ($edit_data['status'] ?? 'active') == 'active') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="inactive" <?php echo ($edit_mode && ($edit_data['status'] ?? 'active') == 'inactive') ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <?php if($edit_mode && !empty($edit_data['image_url'])): ?>
                            <div class="mb-3">
                                <label class="form-label">Pratinjau Gambar Saat Ini</label>
                                <div class="border rounded p-2 bg-light text-center">
                                    <img src="<?php echo htmlspecialchars($edit_data['image_url']); ?>" 
                                         alt="Pratinjau" 
                                         class="img-fluid rounded" 
                                         style="max-height: 200px; object-fit: contain;"
                                         onerror="this.src='https://via.placeholder.com/400x300/e9ecef/6c757d?text=Gambar+Tidak+Ditemukan'">
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="<?php echo $edit_mode ? 'update_gallery' : 'add_gallery'; ?>" 
                                    class="btn btn-<?php echo $edit_mode ? 'warning' : 'primary'; ?> px-4">
                                <i class="fas fa-save me-1"></i>
                                <?php echo $edit_mode ? 'Perbarui Galeri' : 'Tambah ke Galeri'; ?>
                            </button>
                            <a href="admin_gallery.php" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- GRID GALERI -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Item Galeri</h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="categoryFilter" style="width: 200px;">
                                <option value="">Semua Kategori</option>
                                <option value="events">Acara</option>
                                <option value="research">Penelitian</option>
                                <option value="facilities">Fasilitas</option>
                                <option value="team">Tim</option>
                                <option value="products">Produk</option>
                                <option value="documentation">Dokumentasi</option>
                                <option value="workshop">Workshop</option>
                                <option value="seminar">Seminar</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if($gallery_items && $gallery_items->rowCount() > 0): ?>
                        <div class="gallery-grid">
                            <?php 
                            $items_displayed = 0;
                            while($item = $gallery_items->fetch(PDO::FETCH_ASSOC)): 
                                $items_displayed++;
                                // Mapping kategori untuk display
                                $category_display = [
                                    'general' => 'Umum',
                                    'events' => 'Acara',
                                    'research' => 'Penelitian',
                                    'facilities' => 'Fasilitas',
                                    'team' => 'Tim',
                                    'products' => 'Produk',
                                    'documentation' => 'Dokumentasi',
                                    'workshop' => 'Workshop',
                                    'seminar' => 'Seminar'
                                ];
                                $category_text = $item['category'] ?? 'general';
                                $category_name = $category_display[$category_text] ?? ucfirst($category_text);
                                
                                // Status badge
                                $status_badge = ($item['status'] ?? 'active') == 'active' 
                                    ? '<span class="badge bg-success">Aktif</span>' 
                                    : '<span class="badge bg-secondary">Nonaktif</span>';
                            ?>
                            <div class="gallery-item" data-category="<?php echo $category_text; ?>">
                                <div class="gallery-card">
                                    <div class="gallery-image">
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['title']); ?>"
                                             onerror="this.src='https://via.placeholder.com/400x300/e9ecef/6c757d?text=Gambar+Tidak+Ditemukan'">
                                        <div class="gallery-overlay">
                                            <div class="gallery-actions">
                                                <a href="admin_gallery.php?action=edit&id=<?php echo $item['id']; ?>" 
                                                   class="btn btn-sm btn-light me-1" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="admin_gallery.php?delete_id=<?php echo $item['id']; ?>" 
                                                   class="btn btn-sm btn-danger" title="Hapus"
                                                   onclick="return confirm('Apakah Anda yakin ingin menghapus item galeri ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="gallery-badges">
                                            <?php echo $status_badge; ?>
                                            <span class="badge bg-primary"><?php echo $category_name; ?></span>
                                        </div>
                                    </div>
                                    <div class="gallery-info">
                                        <h6 class="gallery-title"><?php echo htmlspecialchars($item['title']); ?></h6>
                                        <?php if(!empty($item['description'])): ?>
                                            <p class="gallery-description">
                                                <?php echo htmlspecialchars(substr($item['description'], 0, 80)); ?>
                                                <?php echo strlen($item['description']) > 80 ? '...' : ''; ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="gallery-meta">
                                            <small class="text-muted">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                <?php echo date('d M Y', strtotime($item['created_at'] ?? 'now')); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <small class="text-muted">
                                <i class="fas fa-images me-1"></i>
                                Menampilkan <?php echo $items_displayed; ?> item galeri
                            </small>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-images fa-4x mb-3 opacity-50"></i>
                            <h5>Belum Ada Item Galeri</h5>
                            <p>Klik "Tambah Item Galeri" untuk mengunggah gambar pertama Anda.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('categoryFilter');
    if(categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            const selectedCategory = this.value;
            const galleryItems = document.querySelectorAll('.gallery-item');
            
            galleryItems.forEach(item => {
                if (selectedCategory === '' || item.getAttribute('data-category') === selectedCategory) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
</script>

<style>
.admin-sidebar {
    min-height: 100vh;
    width: 250px;
    background: #fff;
    border-right: 1px solid #eee;
}

.admin-container {
    display: flex;
    background: #f8f9fa;
}

.admin-content {
    flex: 1;
    padding: 20px;
}

.sidebar-header {
    text-align: center;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 1rem;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.gallery-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #e9ecef;
}

.gallery-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.gallery-image {
    position: relative;
    overflow: hidden;
    height: 200px;
    background: #f8f9fa;
}

.gallery-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.gallery-card:hover .gallery-image img {
    transform: scale(1.05);
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-card:hover .gallery-overlay {
    opacity: 1;
}

.gallery-badges {
    position: absolute;
    top: 10px;
    left: 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    align-items: flex-start;
}

.gallery-badges .badge {
    font-size: 0.7rem;
    padding: 4px 8px;
}

.gallery-actions {
    display: flex;
    gap: 0.5rem;
}

.gallery-info {
    padding: 1rem;
}

.gallery-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2c3e50;
    font-size: 0.95rem;
}

.gallery-description {
    color: #6c757d;
    font-size: 0.85rem;
    line-height: 1.4;
    margin-bottom: 0.75rem;
    min-height: 40px;
}

.gallery-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #e9ecef;
}

@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .gallery-image {
        height: 180px;
    }
    
    .admin-container {
        flex-direction: column;
    }
    
    .admin-sidebar {
        width: 100%;
        min-height: auto;
    }
}

@media (max-width: 576px) {
    .gallery-grid {
        grid-template-columns: 1fr;
    }
    
    .content-header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }
    
    .content-header > div:last-child {
        align-self: stretch;
    }
    
    .content-header .btn {
        width: 100%;
    }
}
</style>

<?php include_once 'includes/footer.php'; ?>