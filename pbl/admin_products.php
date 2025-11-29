<?php
$page_title = "Product Management - Admin";
include_once 'includes/header.php';

// Check admin session
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    header("location: login.php");
    exit;
}

// KONEKSI DATABASE & MODEL
include_once 'config/database.php';
include_once 'models/Products.php';

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

// Variable untuk kontrol tampilan form
$show_form = false;
$edit_mode = false;
$edit_data = null;

// --- HANDLE FORM SUBMISSION (POST) ---
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // A. TAMBAH PRODUCT (CREATE)
    if(isset($_POST['add_product'])){
        if(empty($_POST['name'])) {
            $error_msg = "Product name is required!";
        } else {
            $product->name = $_POST['name'];
            $product->category = $_POST['category'] ?? '';
            $product->description = $_POST['description'] ?? '';
            $product->price = $_POST['price'] ?? 0;
            $product->image_url = $_POST['image_url'] ?? '';
            $product->status = $_POST['status'] ?? 'active';
            
            if($product->create()){
                $_SESSION['message'] = "Product added successfully!";
                echo "<script>window.location.href='admin_products.php';</script>";
                exit;
            } else {
                $error_msg = "Failed to save to database.";
            }
        }
    }

    // B. UPDATE PRODUCT
    if(isset($_POST['update_product'])){
        $product->id = $_POST['id'];
        $product->name = $_POST['name'];
        $product->category = $_POST['category'];
        $product->description = $_POST['description'];
        $product->price = $_POST['price'];
        $product->image_url = $_POST['image_url'];
        $product->status = $_POST['status'];
        
        if($product->update()){
            $_SESSION['message'] = "Product updated successfully!";
            echo "<script>window.location.href='admin_products.php';</script>";
            exit;
        } else {
            $error_msg = "Failed to update data.";
        }
    }
}

// --- HANDLE DELETE (GET) ---
if(isset($_GET['delete_id'])){
    $product->id = $_GET['delete_id'];
    if($product->delete()){
        $_SESSION['message'] = "Product deleted successfully!";
        echo "<script>window.location.href='admin_products.php';</script>";
        exit;
    }
}

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
            <li class="menu-item">
                <a href="admin_dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="admin_users.php"><i class="fas fa-users-cog me-2"></i><span>Users</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_partners.php">
                    <i class="fas fa-handshake me-2"></i>
                    <span>Partners</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="admin_team.php">
                    <i class="fas fa-users me-2"></i>
                    <span>Team</span>
                </a>
            </li>
            <li class="menu-item active">
                <a href="admin_products.php">
                    <i class="fas fa-box me-2"></i>
                    <span>Products</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="admin_news.php">
                    <i class="fas fa-newspaper me-2"></i>
                    <span>News</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="admin_gallery.php">
                    <i class="fas fa-images me-2"></i>
                    <span>Gallery</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="admin_activity.php">
                    <i class="fas fa-chart-line me-2"></i>
                    <span>Activity</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="admin_booking.php">
                    <i class="fas fa-calendar-check me-2"></i>
                    <span>Booking</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="admin_absent.php">
                    <i class="fas fa-clipboard-list me-2"></i>
                    <span>Absent</span>
                </a>
            </li>

        </ul>
    </div>

    <div class="admin-content">
        <div class="content-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Product Management</h1>
                    <p class="text-muted small">Manage your products and services</p>
                </div>
                <?php if(!$show_form): ?>
                    <a href="admin_products.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Product
                    </a>
                <?php else: ?>
                    <a href="admin_products.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
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
            <!-- FORM ADD/EDIT PRODUCT -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-<?php echo $edit_mode ? 'warning' : 'primary'; ?> text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'plus-circle'; ?> me-2"></i>
                        <?php echo $edit_mode ? 'Edit Product' : 'Add New Product'; ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="admin_products.php">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Product Name *</label>
                                <input type="text" class="form-control" name="name" required 
                                       placeholder="e.g. Viat Map Application"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['name']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Category</label>
                                <select class="form-select" name="category">
                                    <option value="Software" <?php echo ($edit_mode && $edit_data['category'] == 'Software') ? 'selected' : ''; ?>>Software</option>
                                    <option value="Hardware" <?php echo ($edit_mode && $edit_data['category'] == 'Hardware') ? 'selected' : ''; ?>>Hardware</option>
                                    <option value="Service" <?php echo ($edit_mode && $edit_data['category'] == 'Service') ? 'selected' : ''; ?>>Service</option>
                                    <option value="Research" <?php echo ($edit_mode && $edit_data['category'] == 'Research') ? 'selected' : ''; ?>>Research</option>
                                    <option value="Other" <?php echo ($edit_mode && $edit_data['category'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4" 
                                      placeholder="Detailed product description..."><?php echo $edit_mode ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="price" step="0.01" 
                                           placeholder="0.00"
                                           value="<?php echo $edit_mode ? htmlspecialchars($edit_data['price']) : ''; ?>">
                                </div>
                                <small class="form-text text-muted">Leave 0 for free products</small>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo ($edit_mode && $edit_data['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($edit_mode && $edit_data['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="coming_soon" <?php echo ($edit_mode && $edit_data['status'] == 'coming_soon') ? 'selected' : ''; ?>>Coming Soon</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Product Image URL</label>
                                <input type="url" class="form-control" name="image_url" 
                                       placeholder="https://example.com/image.jpg"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['image_url']) : ''; ?>">
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="<?php echo $edit_mode ? 'update_product' : 'add_product'; ?>" 
                                    class="btn btn-<?php echo $edit_mode ? 'warning' : 'primary'; ?> px-4">
                                <i class="fas fa-save me-1"></i>
                                <?php echo $edit_mode ? 'Update Product' : 'Save Product'; ?>
                            </button>
                            <a href="admin_products.php" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- PRODUCT LIST TABLE -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Products List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($products && $products->rowCount() > 0): ?>
                                    <?php while($row = $products->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td>
                                            <?php if(!empty($row['image_url'])): ?>
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
                                            <span class="badge bg-info text-dark">
                                                <?php echo htmlspecialchars($row['category']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($row['price'] > 0): ?>
                                                <strong class="text-success">$<?php echo number_format($row['price'], 2); ?></strong>
                                            <?php else: ?>
                                                <span class="badge bg-success">Free</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $badge_class = 'secondary';
                                            if($row['status'] == 'active') $badge_class = 'success';
                                            elseif($row['status'] == 'coming_soon') $badge_class = 'warning';
                                            ?>
                                            <span class="badge bg-<?php echo $badge_class; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                            </span>
                                        </td>
                                        
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="admin_products.php?action=edit&id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                
                                                <a href="admin_products.php?delete_id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this product?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3 d-block"></i>
                                            <h5>No Products Yet</h5>
                                            <p>Click "Add Product" to add your first product.</p>
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
</style>

<?php include_once 'includes/footer.php'; ?>