<?php
$page_title = "Manajemen Produk - Admin LET Lab";
include_once 'includes/header.php';

// 1. Cek Login Admin
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    header("location: login.php");
    exit;
}

include_once 'config/database.php';
include_once 'models/Products.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

// Variable Kontrol Tampilan
$show_form = false;
$edit_mode = false;
$edit_data = null;

// --- FUNGSI HELPER UPLOAD GAMBAR ---
function uploadProductImage($fileInputName, $targetDir = "uploads/products/") {
    // Buat folder jika belum ada
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($_FILES[$fileInputName]["name"]);
    $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Generate nama file unik (Timestamp + Random) agar tidak bentrok
    $newFileName = time() . '_' . rand(100, 999) . '.' . $imageFileType;
    $targetFilePath = $targetDir . $newFileName;
    
    // Validasi ekstensi
    $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    
    if(in_array($imageFileType, $allowedTypes)){
        // Cek ukuran file (opsional, misal max 2MB)
        if ($_FILES[$fileInputName]["size"] > 2000000) {
            return false; // File terlalu besar
        }

        if(move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFilePath)){
            return $targetFilePath;
        }
    }
    return false;
}

// --- FUNGSI UNTUK MEMBERSIHKAN INPUT HARGA ---
function cleanPriceInput($price) {
    if (empty($price) || trim($price) === '') {
        return 0;
    }
    
    // Hapus karakter non-numeric kecuali titik dan koma
    $cleaned = preg_replace('/[^0-9.,]/', '', $price);
    
    // Jika ada koma sebagai pemisah ribuan, hapus
    $cleaned = str_replace(',', '', $cleaned);
    
    // Konversi ke float
    $result = (float)$cleaned;
    
    // Pastikan tidak negatif
    return max(0, $result);
}

// --- HANDLE FORM SUBMISSION (POST) ---
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // A. TAMBAH PRODUK (CREATE)
    if(isset($_POST['add_product'])){
        if(empty($_POST['name'])) {
            $error_msg = "Nama produk wajib diisi!";
        } else {
            $product->name = trim($_POST['name']);
            $product->description = trim($_POST['description'] ?? '');
            
            // FIX: Bersihkan input harga dan pastikan numeric
            $rawPrice = $_POST['price'] ?? '0';
            $product->price = cleanPriceInput($rawPrice);
            
            $product->link_demo = trim($_POST['link_demo'] ?? ''); 
            
            // Handle Upload Image
            $imagePath = '';
            if(!empty($_FILES["image_file"]["name"])){
                $uploadResult = uploadProductImage("image_file");
                if($uploadResult){
                    $imagePath = $uploadResult;
                } else {
                    $error_msg = "Gagal upload gambar. Pastikan format jpg/png/webp dan ukuran < 2MB.";
                }
            }
            $product->image_url = $imagePath;
            
            if(!isset($error_msg)){
                if($product->create()){
                    $_SESSION['message'] = "Produk berhasil ditambahkan!";
                    echo "<script>window.location.href='admin_products.php';</script>";
                    exit;
                } else {
                    $error_msg = "Gagal menyimpan ke database.";
                }
            }
        }
    }

    // B. UPDATE PRODUK
    if(isset($_POST['update_product'])){
        $product->id = (int)$_POST['id'];
        $product->name = trim($_POST['name']);
        $product->description = trim($_POST['description']);
        
        // FIX: Bersihkan input harga dan pastikan numeric untuk update
        $rawPrice = $_POST['price'] ?? '0';
        $product->price = cleanPriceInput($rawPrice);
        
        $product->link_demo = trim($_POST['link_demo']);
        
        // Handle Upload Image Update
        if(!empty($_FILES["image_file"]["name"])){
            // User upload gambar baru
            $uploadResult = uploadProductImage("image_file");
            if($uploadResult){
                $product->image_url = $uploadResult;
                
                // Hapus file lama jika ada
                if(!empty($_POST['existing_image']) && file_exists($_POST['existing_image'])){
                    unlink($_POST['existing_image']);
                }
            } else {
                $error_msg = "Gagal upload gambar baru.";
            }
        } else {
            // User tidak upload, pakai gambar lama
            $product->image_url = $_POST['existing_image'];
        }
        
        if(!isset($error_msg)){
            if($product->update()){
                $_SESSION['message'] = "Produk berhasil diperbarui!";
                echo "<script>window.location.href='admin_products.php';</script>";
                exit;
            } else {
                $error_msg = "Gagal memperbarui data.";
            }
        }
    }
}

// --- HANDLE DELETE (GET) ---
if(isset($_GET['delete_id'])){
    $delete_id = (int)$_GET['delete_id'];
    
    // Ambil path gambar dulu untuk dihapus
    $stmt = $db->prepare("SELECT image_url FROM products WHERE product_id = ?");
    $stmt->execute([$delete_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $product->id = $delete_id;
    if($product->delete()){
        // Hapus file fisik
        if($row && !empty($row['image_url']) && file_exists($row['image_url'])){
            unlink($row['image_url']);
        }

        $_SESSION['message'] = "Produk berhasil dihapus!";
        echo "<script>window.location.href='admin_products.php';</script>";
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
        $stmt = $product->read();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($row['id'] == $_GET['id']){
                $edit_data = $row;
                break;
            }
        }
    }
}

// AMBIL DATA PRODUCTS
$products = $product->read();
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
            <li class="menu-item"><a href="admin_partners.php"><i class="fas fa-handshake me-2"></i><span>Mitra</span></a></li>
            <li class="menu-item"><a href="admin_team.php"><i class="fas fa-users me-2"></i><span>Tim</span></a></li>
            <li class="menu-item active"><a href="admin_products.php"><i class="fas fa-box me-2"></i><span>Produk</span></a></li>
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
                    <h1 class="h3 mb-0 text-gray-800">Manajemen Produk</h1>
                    <p class="text-muted small">Kelola produk, software, dan layanan Anda</p>
                </div>
                <?php if(!$show_form): ?>
                    <a href="admin_products.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Tambah Produk
                    </a>
                <?php else: ?>
                    <a href="admin_products.php" class="btn btn-secondary">
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
                        <?php echo $edit_mode ? 'Edit Produk' : 'Tambah Produk Baru'; ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="admin_products.php" enctype="multipart/form-data" id="productForm">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                            <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($edit_data['image_url']); ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Produk *</label>
                            <input type="text" class="form-control" name="name" required 
                                   placeholder="Contoh: Aplikasi Viat Map"
                                   value="<?php echo $edit_mode ? htmlspecialchars($edit_data['name']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="4" 
                                      placeholder="Deskripsi detail produk..."><?php echo $edit_mode ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Harga</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control price-input" name="price" 
                                           placeholder="0"
                                           value="<?php echo $edit_mode ? number_format($edit_data['price'], 0, ',', '.') : '0'; ?>">
                                </div>
                                <small class="form-text text-muted">Isi 0 jika gratis</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Gambar Produk</label>
                                <input type="file" class="form-control" name="image_file" accept="image/*" id="imageUpload">
                                
                                <?php if($edit_mode && !empty($edit_data['image_url'])): ?>
                                    <div class="mt-2 small text-muted">
                                        Gambar saat ini:<br>
                                        <img src="<?php echo htmlspecialchars($edit_data['image_url']); ?>" 
                                             alt="Preview" 
                                             style="height: 60px; object-fit: contain; border: 1px solid #ddd; padding: 2px;">
                                    </div>
                                <?php endif; ?>
                                <small class="form-text text-muted">Format: JPG, PNG, WEBP. Maks: 2MB</small>
                            </div>

                             <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Link Download (Opsional)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-download"></i></span>
                                    <input type="url" class="form-control" name="link_demo" 
                                           placeholder="https://drive.google.com/..."
                                           value="<?php echo $edit_mode ? htmlspecialchars($edit_data['link_demo']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="<?php echo $edit_mode ? 'update_product' : 'add_product'; ?>" 
                                    class="btn btn-<?php echo $edit_mode ? 'warning' : 'primary'; ?> px-4">
                                <i class="fas fa-save me-1"></i>
                                <?php echo $edit_mode ? 'Perbarui Produk' : 'Simpan Produk'; ?>
                            </button>
                            <a href="admin_products.php" class="btn btn-secondary px-4">
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
                        <h5 class="card-title mb-0">Daftar Produk</h5>
                        <div class="badge bg-primary px-3 py-2">
                            <i class="fas fa-box me-1"></i>
                            <?php echo $products ? $products->rowCount() : 0; ?> Produk
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Gambar</th>
                                    <th>Nama Produk</th>
                                    <th>Harga</th>
                                    <th>Download</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($products && $products->rowCount() > 0): ?>
                                    <?php while($row = $products->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td>
                                            <?php if(!empty($row['image_url']) && file_exists($row['image_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
                                                     alt="Product" class="rounded border" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded border d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="fas fa-box text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong class="text-dark"><?php echo htmlspecialchars($row['name']); ?></strong>
                                            <?php if(!empty($row['description'])): ?>
                                                <div class="text-muted small text-truncate" style="max-width: 300px;">
                                                    <?php echo htmlspecialchars($row['description']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($row['price'] > 0): ?>
                                                <strong class="text-success">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></strong>
                                            <?php else: ?>
                                                <span class="badge bg-success">Gratis</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td>
                                            <?php if(!empty($row['link_demo'])): ?>
                                                <a href="<?php echo htmlspecialchars($row['link_demo']); ?>" target="_blank" class="btn btn-sm btn-outline-primary" title="Download">
                                                    <i class="fas fa-download"></i> Link
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="admin_products.php?action=edit&id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                
                                                <a href="admin_products.php?delete_id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Yakin ingin menghapus produk ini? Gambar akan ikut terhapus.')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3 d-block"></i>
                                            <h5>Belum Ada Produk</h5>
                                            <p>Klik "Tambah Produk" untuk menambahkan produk pertama Anda.</p>
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
        border-bottom: 1px solid #06305aff;
        margin-bottom: 1rem;
    }
    .admin-container {
        background-color: #06305aff;
        min-height: 100vh;
    }
    .price-input {
        font-family: monospace;
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
// Format input harga saat user mengetik
document.addEventListener('DOMContentLoaded', function() {
    const priceInputs = document.querySelectorAll('.price-input');
    
    priceInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // Hapus semua karakter non-digit
            let value = e.target.value.replace(/[^\d]/g, '');
            
            // Format dengan titik sebagai pemisah ribuan
            if (value.length > 0) {
                value = parseInt(value).toLocaleString('id-ID');
            }
            
            e.target.value = value;
        });
        
        // Validasi sebelum submit
        const form = document.getElementById('productForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const priceInput = document.querySelector('.price-input');
                if (priceInput) {
                    // Hapus format titik sebelum submit
                    priceInput.value = priceInput.value.replace(/\./g, '');
                }
            });
        }
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>