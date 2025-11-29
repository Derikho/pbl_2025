<?php
$page_title = "Gallery Management - LET Lab Admin";
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

// Variable untuk kontrol tampilan form
$show_form = false;
$edit_mode = false;
$edit_data = null;

// --- HANDLE FORM SUBMISSION (POST) ---
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // A. TAMBAH GALLERY (CREATE)
    if(isset($_POST['add_gallery'])){
        if(empty($_POST['title']) || empty($_POST['image_url'])) {
            $error_msg = "Title and Image URL are required!";
        } else {
            $gallery->title = $_POST['title'];
            $gallery->description = $_POST['description'] ?? '';
            $gallery->image_url = $_POST['image_url'];
            $gallery->category = $_POST['category'] ?? 'events';
            $gallery->status = $_POST['status'] ?? 'active';
            
            if($gallery->create()){
                $_SESSION['message'] = "Gallery item added successfully!";
                echo "<script>window.location.href='admin_gallery.php';</script>";
                exit;
            } else {
                $error_msg = "Failed to save to database.";
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
            $_SESSION['message'] = "Gallery item updated successfully!";
            echo "<script>window.location.href='admin_gallery.php';</script>";
            exit;
        } else {
            $error_msg = "Failed to update data.";
        }
    }
}

// --- HANDLE DELETE (GET) ---
if(isset($_GET['delete_id'])){
    $gallery->id = $_GET['delete_id'];
    if($gallery->delete()){
        $_SESSION['message'] = "Gallery item deleted successfully!";
        echo "<script>window.location.href='admin_gallery.php';</script>";
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
        // Ambil data gallery untuk di-edit
        $stmt = $gallery->read();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($row['id'] == $_GET['id']){
                $edit_data = $row;
                break;
            }
        }
    }
}

// AMBIL DATA GALLERY
$gallery_items = $gallery->read();
?>

<!-- Admin Navbar -->
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
            <a href="logout.php" class="btn btn-sm btn-outline-light">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>
    </div>
</nav>

<div class="admin-container">
    
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <h5 class="mb-0">Navigation</h5>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item"><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i><span>Dashboard</span></a></li>
            <li class="menu-item"><a href="admin_users.php"><i class="fas fa-users-cog me-2"></i><span>Users</span></a></li>
            <li class="menu-item"><a href="admin_partners.php"><i class="fas fa-handshake me-2"></i><span>Partners</span></a></li>
            <li class="menu-item"><a href="admin_team.php"><i class="fas fa-users me-2"></i><span>Team</span></a></li>
            <li class="menu-item"><a href="admin_products.php"><i class="fas fa-box me-2"></i><span>Products</span></a></li>
            <li class="menu-item"><a href="admin_news.php"><i class="fas fa-newspaper me-2"></i><span>News</span></a></li>
            <li class="menu-item active"><a href="admin_gallery.php"><i class="fas fa-images me-2"></i><span>Gallery</span></a></li>
            <li class="menu-item"><a href="admin_activity.php"><i class="fas fa-chart-line me-2"></i><span>Activity</span></a></li>
            <li class="menu-item"><a href="admin_booking.php"><i class="fas fa-calendar-check me-2"></i><span>Booking</span></a></li>
            <li class="menu-item"><a href="admin_absent.php"><i class="fas fa-clipboard-list me-2"></i><span>Absent</span></a></li>
        </ul>
    </div>

    <div class="admin-content">
        <div class="content-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Gallery Management</h1>
                    <p class="text-muted small">Kelola Foto & Dokumentasi InLET</p>
                </div>
                <?php if(!$show_form): ?>
                    <a href="admin_gallery.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Gallery Item
                    </a>
                <?php else: ?>
                    <a href="admin_gallery.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Gallery
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
            <!-- FORM ADD/EDIT GALLERY -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-<?php echo $edit_mode ? 'warning' : 'primary'; ?> text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'plus-circle'; ?> me-2"></i>
                        <?php echo $edit_mode ? 'Edit Gallery Item' : 'Add New Gallery Item'; ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="admin_gallery.php">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Title *</label>
                                <input type="text" class="form-control" name="title" required 
                                       placeholder="e.g. Workshop IoT 2024"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Category *</label>
                                <select class="form-select" name="category" required>
                                    <option value="events" <?php echo ($edit_mode && $edit_data['category'] == 'events') ? 'selected' : ''; ?>>Events</option>
                                    <option value="research" <?php echo ($edit_mode && $edit_data['category'] == 'research') ? 'selected' : ''; ?>>Research</option>
                                    <option value="facilities" <?php echo ($edit_mode && $edit_data['category'] == 'facilities') ? 'selected' : ''; ?>>Facilities</option>
                                    <option value="team" <?php echo ($edit_mode && $edit_data['category'] == 'team') ? 'selected' : ''; ?>>Team</option>
                                    <option value="products" <?php echo ($edit_mode && $edit_data['category'] == 'products') ? 'selected' : ''; ?>>Products</option>
                                    <option value="documentation" <?php echo ($edit_mode && $edit_data['category'] == 'documentation') ? 'selected' : ''; ?>>Documentation</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Description of this gallery item..."><?php echo $edit_mode ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Image URL *</label>
                                <input type="url" class="form-control" name="image_url" required 
                                       placeholder="https://example.com/image.jpg"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['image_url']) : ''; ?>">
                                <small class="form-text text-muted">Enter the full URL of the image</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo ($edit_mode && $edit_data['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($edit_mode && $edit_data['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <?php if($edit_mode && !empty($edit_data['image_url'])): ?>
                            <div class="mb-3">
                                <label class="form-label">Current Image Preview</label>
                                <div class="border rounded p-2 bg-light text-center">
                                    <img src="<?php echo htmlspecialchars($edit_data['image_url']); ?>" 
                                         alt="Preview" 
                                         class="img-fluid rounded" 
                                         style="max-height: 200px; object-fit: contain;">
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="<?php echo $edit_mode ? 'update_gallery' : 'add_gallery'; ?>" 
                                    class="btn btn-<?php echo $edit_mode ? 'warning' : 'primary'; ?> px-4">
                                <i class="fas fa-save me-1"></i>
                                <?php echo $edit_mode ? 'Update Gallery' : 'Add to Gallery'; ?>
                            </button>
                            <a href="admin_gallery.php" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- GALLERY GRID -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Gallery Items</h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="categoryFilter" style="width: 200px;">
                                <option value="">All Categories</option>
                                <option value="events">Events</option>
                                <option value="research">Research</option>
                                <option value="facilities">Facilities</option>
                                <option value="team">Team</option>
                                <option value="products">Products</option>
                                <option value="documentation">Documentation</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if($gallery_items && $gallery_items->rowCount() > 0): ?>
                        <div class="gallery-grid">
                            <?php while($item = $gallery_items->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="gallery-item" data-category="<?php echo $item['category']; ?>">
                                <div class="gallery-card">
                                    <div class="gallery-image">
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['title']); ?>"
                                             onerror="this.src='https://via.placeholder.com/400x300/e9ecef/6c757d?text=Image+Not+Found'">
                                        <div class="gallery-overlay">
                                            <div class="gallery-actions">
                                                <a href="admin_gallery.php?action=edit&id=<?php echo $item['id']; ?>" 
                                                   class="btn btn-sm btn-light me-1">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="admin_gallery.php?delete_id=<?php echo $item['id']; ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Are you sure you want to delete this gallery item?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
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
                                            <span class="badge bg-info"><?php echo ucfirst($item['category']); ?></span>
                                            <span class="badge bg-<?php echo $item['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($item['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-images fa-4x mb-3 opacity-50"></i>
                            <h5>No Gallery Items Yet</h5>
                            <p>Click "Add Gallery Item" to upload your first image.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Category filter functionality
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
    gap: 0.5rem;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .gallery-image {
        height: 180px;
    }
}

@media (max-width: 576px) {
    .gallery-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include_once 'includes/footer.php'; ?>