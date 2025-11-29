<?php
$page_title = "Manage Team - LET Lab Admin";
include_once 'includes/header.php';

// 1. CEK LOGIN
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

include_once 'config/database.php';
include_once 'models/Team.php';

$database = new Database();
$db = $database->getConnection();
$team = new Team($db);

// Variable untuk kontrol tampilan form
$show_form = false;
$edit_mode = false;
$edit_data = null;

// --- HANDLE FORM SUBMISSION (POST) ---
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // A. TAMBAH MEMBER (CREATE)
    if(isset($_POST['add_member'])){
        if(empty($_POST['name']) || empty($_POST['position'])) {
            $error_msg = "Name and Position are required!";
        } else {
            $team->name = $_POST['name'];
            $team->position = $_POST['position'];
            $team->email = $_POST['email'] ?? '';
            $team->phone = $_POST['phone'] ?? '';
            $team->bio = $_POST['bio'] ?? '';
            $team->photo = $_POST['photo'] ?? '';
            $team->status = $_POST['status'] ?? 'active';
            
            if($team->create()){
                $_SESSION['message'] = "Team member added successfully!";
                echo "<script>window.location.href='admin_team.php';</script>";
                exit;
            } else {
                $error_msg = "Failed to save to database.";
            }
        }
    }

    // B. UPDATE MEMBER
    if(isset($_POST['update_member'])){
        $team->id = $_POST['id'];
        $team->name = $_POST['name'];
        $team->position = $_POST['position'];
        $team->email = $_POST['email'];
        $team->phone = $_POST['phone'];
        $team->bio = $_POST['bio'];
        $team->photo = $_POST['photo'];
        $team->status = $_POST['status'];
        
        if($team->update()){
            $_SESSION['message'] = "Team member updated successfully!";
            echo "<script>window.location.href='admin_team.php';</script>";
            exit;
        } else {
            $error_msg = "Failed to update data.";
        }
    }
}

// --- HANDLE DELETE (GET) ---
if(isset($_GET['delete_id'])){
    $team->id = $_GET['delete_id'];
    if($team->delete()){
        $_SESSION['message'] = "Member deleted successfully!";
        echo "<script>window.location.href='admin_team.php';</script>";
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
        // Ambil data member untuk di-edit
        $stmt = $team->read();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($row['id'] == $_GET['id']){
                $edit_data = $row;
                break;
            }
        }
    }
}

// 2. AMBIL DATA TEAM
$team_members = $team->read();
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
            <li class="menu-item"><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i><span>Dashboard</span></a></li>
            <li class="menu-item"><a href="admin_users.php"><i class="fas fa-users-cog me-2"></i><span>Users</span></a></li>
            <li class="menu-item"><a href="admin_partners.php"><i class="fas fa-handshake me-2"></i><span>Partners</span></a></li>
            <li class="menu-item active"><a href="admin_team.php"><i class="fas fa-users me-2"></i><span>Team</span></a></li>
            <li class="menu-item"><a href="admin_products.php"><i class="fas fa-box me-2"></i><span>Products</span></a></li>
            <li class="menu-item"><a href="admin_news.php"><i class="fas fa-newspaper me-2"></i><span>News</span></a></li>
            <li class="menu-item"><a href="admin_gallery.php"><i class="fas fa-images me-2"></i><span>Gallery</span></a></li>
            <li class="menu-item"><a href="admin_activity.php"><i class="fas fa-chart-line me-2"></i><span>Activity</span></a></li>
            <li class="menu-item"><a href="admin_booking.php"><i class="fas fa-calendar-check me-2"></i><span>Booking</span></a></li>
            <li class="menu-item"><a href="admin_absent.php"><i class="fas fa-clipboard-list me-2"></i><span>Absent</span></a></li>
        </ul>
    </div>

    <div class="admin-content">
        <div class="content-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Manage Team</h1>
                    <p class="text-muted small">Kelola anggota tim dan dosen</p>
                </div>
                <?php if(!$show_form): ?>
                    <a href="admin_team.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Member
                    </a>
                <?php else: ?>
                    <a href="admin_team.php" class="btn btn-secondary">
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
            <!-- FORM ADD/EDIT MEMBER -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-<?php echo $edit_mode ? 'warning' : 'primary'; ?> text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'user-plus'; ?> me-2"></i>
                        <?php echo $edit_mode ? 'Edit Team Member' : 'Add New Member'; ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="admin_team.php">
                        <?php if($edit_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Full Name *</label>
                                <input type="text" class="form-control" name="name" required 
                                       placeholder="e.g. Dr. John Doe"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['name']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Position *</label>
                                <input type="text" class="form-control" name="position" required 
                                       placeholder="e.g. Lecturer, Researcher"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['position']) : ''; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="email" 
                                           placeholder="email@example.com"
                                           value="<?php echo $edit_mode ? htmlspecialchars($edit_data['email']) : ''; ?>">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control" name="phone" 
                                           placeholder="+62 xxx xxxx xxxx"
                                           value="<?php echo $edit_mode ? htmlspecialchars($edit_data['phone']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Photo URL</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-image"></i></span>
                                <input type="url" class="form-control" name="photo" 
                                       placeholder="https://example.com/photo.jpg"
                                       value="<?php echo $edit_mode ? htmlspecialchars($edit_data['photo']) : ''; ?>">
                            </div>
                            <small class="form-text text-muted">Recommended: Square image, min 400x400px</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bio / Description</label>
                            <textarea class="form-control" name="bio" rows="4" 
                                      placeholder="Brief bio or description about the team member..."><?php echo $edit_mode ? htmlspecialchars($edit_data['bio']) : ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="active" <?php echo ($edit_mode && $edit_data['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($edit_mode && $edit_data['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="<?php echo $edit_mode ? 'update_member' : 'add_member'; ?>" 
                                    class="btn btn-<?php echo $edit_mode ? 'warning' : 'primary'; ?> px-4">
                                <i class="fas fa-save me-1"></i>
                                <?php echo $edit_mode ? 'Update Changes' : 'Save Member'; ?>
                            </button>
                            <a href="admin_team.php" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- GRID CARD LIST TEAM MEMBERS -->
            <div class="row">
                <?php if($team_members->rowCount() > 0): ?>
                    <?php while($member = $team_members->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 shadow-sm border-0 hover-card">
                            <div class="card-body text-center p-4">
                                <div class="mb-3 position-relative d-inline-block">
                                    <?php if($member['photo']): ?>
                                        <img src="<?php echo htmlspecialchars($member['photo']); ?>" 
                                             alt="Photo" class="rounded-circle border border-3 border-light shadow" 
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light border border-3 d-flex align-items-center justify-content-center mx-auto shadow-sm" 
                                             style="width: 100px; height: 100px;">
                                            <i class="fas fa-user fa-3x text-secondary"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-<?php echo $member['status']=='active'?'success':'secondary'; ?>">
                                        <?php echo ucfirst($member['status']); ?>
                                    </span>
                                </div>
                                
                                <h5 class="card-title fw-bold mb-1"><?php echo htmlspecialchars($member['name']); ?></h5>
                                <p class="text-primary small fw-semibold mb-3"><?php echo htmlspecialchars($member['position']); ?></p>
                                
                                <?php if($member['bio']): ?>
                                    <p class="text-muted small mb-3" style="min-height: 40px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?php echo htmlspecialchars($member['bio']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="small text-muted mb-3 text-start">
                                    <?php if($member['email']): ?>
                                        <div class="mb-1"><i class="fas fa-envelope me-2 text-primary"></i> <?php echo htmlspecialchars($member['email']); ?></div>
                                    <?php endif; ?>
                                    <?php if($member['phone']): ?>
                                        <div><i class="fas fa-phone me-2 text-success"></i> <?php echo htmlspecialchars($member['phone']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top py-3">
                                <div class="d-grid gap-2">
                                    <a href="admin_team.php?action=edit&id=<?php echo $member['id']; ?>" 
                                       class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="admin_team.php?delete_id=<?php echo $member['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this member?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-users fa-4x mb-3 opacity-50"></i>
                            <h5>No Team Members Yet</h5>
                            <p>Click "Add Member" to add your first team member.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .sidebar-header {
        text-align: center;
        padding: 1rem 1rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
    .admin-container {
        background-color: #f8f9fa;
        min-height: 100vh;
    }
    .hover-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
    }
</style>

<?php include_once 'includes/footer.php'; ?>