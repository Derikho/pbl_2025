<?php
$page_title = "Manage News - LET Lab Admin";
include_once 'includes/header.php';

// 1. Cek Sesi Admin
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    header("location: login.php");
    exit;
}

include_once 'config/database.php';
include_once 'models/News.php';

$database = new Database();
$db = $database->getConnection();
$news = new News($db);

// Variable untuk kontrol tampilan form
$show_form = false;
$edit_mode = false;
$edit_data = null;

// --- HANDLE FORM SUBMISSION (POST) ---
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // A. TAMBAH NEWS (CREATE)
    if(isset($_POST['add_news'])){
        if(empty($_POST['title']) || empty($_POST['content'])) {
            $error_msg = "Title and Content are required!";
        } else {
            $news->title = $_POST['title'];
            $news->content = $_POST['content'];
            $news->category = $_POST['category'] ?? 'General';
            $news->image_url = $_POST['image_url'] ?? '';
            $news->status = $_POST['status'] ?? 'draft';
            $news->publish_date = !empty($_POST['publish_date']) ? $_POST['publish_date'] : date('Y-m-d H:i:s');
            
            if($news->create()){
                $_SESSION['message'] = "News article added successfully!";
                echo "<script>window.location.href='admin_news.php';</script>";
                exit;
            } else {
                $error_msg = "Failed to save to database.";
            }
        }
    }

    // B. UPDATE NEWS
    if(isset($_POST['update_news'])){
        $news->id = $_POST['id'];
        $news->title = $_POST['title'];
        $news->content = $_POST['content'];
        $news->category = $_POST['category'];
        $news->image_url = $_POST['image_url'];
        $news->status = $_POST['status'];
        $news->publish_date = $_POST['publish_date'];
        
        if($news->update()){
            $_SESSION['message'] = "News article updated successfully!";
            echo "<script>window.location.href='admin_news.php';</script>";
            exit;
        } else {
            $error_msg = "Failed to update data.";
        }
    }
}

// --- HANDLE DELETE (GET) ---
if(isset($_GET['delete_id'])){
    $news->id = $_GET['delete_id'];
    if($news->delete()){
        $_SESSION['message'] = "News deleted successfully!";
        echo "<script>window.location.href='admin_news.php';</script>";
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
        // Ambil data news untuk di-edit
        $stmt = $news->read();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($row['id'] == $_GET['id']){
                $edit_data = $row;
                break;
            }
        }
    }
}

// AMBIL DATA NEWS
$news_articles = $news->read();
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
                <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i><span>Dashboard</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_users.php"><i class="fas fa-users-cog me-2"></i><span>Users</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_partners.php"><i class="fas fa-handshake me-2"></i><span>Partners</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_team.php"><i class="fas fa-users me-2"></i><span>Team</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_products.php"><i class="fas fa-box me-2"></i><span>Products</span></a>
            </li>
            <li class="menu-item active">
                <a href="admin_news.php"><i class="fas fa-newspaper me-2"></i><span>News</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_gallery.php"><i class="fas fa-images me-2"></i><span>Gallery</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_activity.php"><i class="fas fa-chart-line me-2"></i><span>Activity</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_booking.php"><i class="fas fa-calendar-check me-2"></i><span>Booking</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_absent.php"><i class="fas fa-clipboard-list me-2"></i><span>Absent</span></a>
            </li>
        </ul>
    </div>

    <div class="admin-content">
        <div class="content-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Manage News</h1>
                    <p class="text-muted small">Create and manage news articles</p>
                </div>
                <?php if(!$show_form): ?>
                    <a href="admin_news.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add News
                    </a>
                <?php else: ?>
                    <a href="admin_news.php" class="btn btn-secondary">
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
            <!-- FORM ADD/EDIT NEWS -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-<?php echo $edit_mode ? 'warning' : 'primary'; ?> text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'newspaper'; ?> me-2"></i>
                        <?php echo $edit_mode ? 'Edit News Article' : 'Add New Article'; ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="admin_news.php">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Article Title *</label>
                            <input type="text" class="form-control form-control-lg" name="title" required 
                                   placeholder="Enter article title..."
                                   value="<?php echo $edit_mode ? htmlspecialchars($edit_data['title']) : ''; ?>">
                            <small class="form-text text-muted">Slug will be generated automatically</small>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category">
                                    <option value="General" <?php echo ($edit_mode && $edit_data['category'] == 'General') ? 'selected' : ''; ?>>General</option>
                                    <option value="Research" <?php echo ($edit_mode && $edit_data['category'] == 'Research') ? 'selected' : ''; ?>>Research</option>
                                    <option value="Events" <?php echo ($edit_mode && $edit_data['category'] == 'Events') ? 'selected' : ''; ?>>Events</option>
                                    <option value="Achievement" <?php echo ($edit_mode && $edit_data['category'] == 'Achievement') ? 'selected' : ''; ?>>Achievement</option>
                                    <option value="Technology" <?php echo ($edit_mode && $edit_data['category'] == 'Technology') ? 'selected' : ''; ?>>Technology</option>
                                    <option value="Education" <?php echo ($edit_mode && $edit_data['category'] == 'Education') ? 'selected' : ''; ?>>Education</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="published" <?php echo ($edit_mode && $edit_data['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                                    <option value="draft" <?php echo ($edit_mode && $edit_data['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                    <option value="archived" <?php echo ($edit_mode && $edit_data['status'] == 'archived') ? 'selected' : ''; ?>>Archived</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Publish Date</label>
                                <input type="datetime-local" class="form-control" name="publish_date"
                                       value="<?php 
                                       if($edit_mode && !empty($edit_data['publish_date'])) {
                                           echo date('Y-m-d\TH:i', strtotime($edit_data['publish_date']));
                                       } else {
                                           echo date('Y-m-d\TH:i');
                                       }
                                       ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thumbnail Image URL</label>
                            <input type="url" class="form-control" name="image_url" 
                                   placeholder="https://example.com/image.jpg"
                                   value="<?php echo $edit_mode ? htmlspecialchars($edit_data['image_url']) : ''; ?>">
                            <small class="form-text text-muted">Recommended: 1200x630px</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Article Content *</label>
                            <textarea class="form-control" name="content" rows="12" required 
                                      placeholder="Write your article content here..."><?php echo $edit_mode ? htmlspecialchars($edit_data['content']) : ''; ?></textarea>
                            <small class="form-text text-muted">You can use HTML tags for formatting</small>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="<?php echo $edit_mode ? 'update_news' : 'add_news'; ?>" 
                                    class="btn btn-<?php echo $edit_mode ? 'warning' : 'primary'; ?> px-4">
                                <i class="fas fa-save me-1"></i>
                                <?php echo $edit_mode ? 'Update Article' : 'Publish Article'; ?>
                            </button>
                            <a href="admin_news.php" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- NEWS LIST TABLE -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">News Articles List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="40%">Title</th>
                                    <th width="12%">Category</th>
                                    <th width="13%">Date</th>
                                    <th width="10%">Status</th>
                                    <th width="20%" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($news_articles->rowCount() > 0): ?>
                                    <?php while($article = $news_articles->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td><?php echo $article['id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if(!empty($article['image_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>" 
                                                         alt="Thumb" class="rounded me-3" 
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 60px;">
                                                        <i class="fas fa-newspaper text-muted fa-2x"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong class="d-block text-dark">
                                                        <?php echo htmlspecialchars(substr($article['title'], 0, 50)); ?>
                                                        <?php echo strlen($article['title']) > 50 ? '...' : ''; ?>
                                                    </strong>
                                                    <small class="text-muted">
                                                        Slug: <?php echo htmlspecialchars($article['slug']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?php echo htmlspecialchars($article['category'] ?? 'General'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo date('M j, Y', strtotime($article['publish_date'])); ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($article['publish_date'])); ?></small>
                                        </td>
                                        <td>
                                            <?php 
                                            $badge_class = 'secondary';
                                            if($article['status'] == 'published') $badge_class = 'success';
                                            elseif($article['status'] == 'draft') $badge_class = 'warning';
                                            ?>
                                            <span class="badge bg-<?php echo $badge_class; ?>">
                                                <?php echo ucfirst($article['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="admin_news.php?action=edit&id=<?php echo $article['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="admin_news.php?delete_id=<?php echo $article['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this article?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-newspaper fa-3x mb-3 d-block"></i>
                                            <h5>No News Articles Yet</h5>
                                            <p>Click "Add News" to create your first article.</p>
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