<?php
$page_title = "Activity Management - LET Lab Admin";
include_once 'includes/header.php';

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    header("location: login.php");
    exit;
}

include_once 'config/database.php';
include_once 'models/Activity.php';

$database = new Database();
$db = $database->getConnection();
$activity = new Activity($db);

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    if(isset($_POST['add_activity'])){
        $activity->title = $_POST['title'];
        $activity->description = $_POST['description'];
        $activity->activity_date = $_POST['activity_date'];
        $activity->location = $_POST['location'];
        $activity->image_url = $_POST['image_url'];
        $activity->status = $_POST['status']; 
        $activity->link = $_POST['link'];
        
        if($activity->create()){
            $_SESSION['message'] = "Activity added successfully!";
            echo "<script>window.location.href='admin_activity.php';</script>";
            exit;
        }
    }
    
    if(isset($_POST['update_activity'])){
        $activity->id = $_POST['id'];
        $activity->title = $_POST['title'];
        $activity->description = $_POST['description'];
        $activity->activity_date = $_POST['activity_date'];
        $activity->location = $_POST['location'];
        $activity->image_url = $_POST['image_url'];
        $activity->status = $_POST['status'];
        $activity->link = $_POST['link'];
        
        if($activity->update()){
            $_SESSION['message'] = "Activity updated successfully!";
            echo "<script>window.location.href='admin_activity.php';</script>";
            exit;
        }
    }
}

if(isset($_GET['delete_id'])){
    $activity->id = $_GET['delete_id'];
    if($activity->delete()){
        $_SESSION['message'] = "Activity deleted successfully!";
        echo "<script>window.location.href='admin_activity.php';</script>";
        exit;
    }
}

$total_activities = $activity->getTotalActivities();
$ongoing_count = $activity->getCountByStatus('ongoing');
$completed_count = $activity->getCountByStatus('completed');
$planned_count = $activity->getCountByStatus('planned'); 

$activities = $activity->read();
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
            <li class="menu-item">
                <a href="admin_news.php"><i class="fas fa-newspaper me-2"></i><span>News</span></a>
            </li>
            <li class="menu-item">
                <a href="admin_gallery.php"><i class="fas fa-images me-2"></i><span>Gallery</span></a>
            </li>
            <li class="menu-item active">
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
                    <h1 class="h3 mb-0 text-gray-800">Activity Management</h1>
                    <p class="text-muted small">Kelola Kegiatan InLET</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addActivityModal">
                    <i class="fas fa-plus me-1"></i> Add Activity
                </button>
            </div>
        </div>

        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card bg-primary text-white p-3 rounded shadow-sm d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-0 fw-bold"><?php echo $total_activities; ?></h3>
                        <p class="mb-0 small">Total</p>
                    </div>
                    <i class="fas fa-calendar-check fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card bg-info text-white p-3 rounded shadow-sm d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-0 fw-bold"><?php echo $ongoing_count; ?></h3>
                        <p class="mb-0 small">Ongoing</p>
                    </div>
                    <i class="fas fa-play-circle fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card bg-success text-white p-3 rounded shadow-sm d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-0 fw-bold"><?php echo $completed_count; ?></h3>
                        <p class="mb-0 small">Completed</p>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card bg-warning text-dark p-3 rounded shadow-sm d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-0 fw-bold"><?php echo $planned_count; ?></h3>
                        <p class="mb-0 small">Planned</p>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-5">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Activities List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Link</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($activities->rowCount() > 0): ?>
                                <?php while($row = $activities->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if($row['image_url']): ?>
                                                <img src="<?php echo htmlspecialchars($row['image_url']); ?>" class="rounded me-2" style="width:40px; height:40px; object-fit:cover;">
                                            <?php endif; ?>
                                            <div>
                                                <strong class="d-block text-dark"><?php echo htmlspecialchars($row['title']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($row['activity_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo ucfirst($row['status']); ?></span>
                                    </td>
                                    <td>
                                        <?php if($row['link']): ?>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#videoModal"
                                                    data-video-url="<?php echo htmlspecialchars($row['link']); ?>">
                                                <i class="fas fa-play-circle me-1"></i> Play
                                            </button>
                                        <?php else: ?> - <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" data-bs-target="#editActivityModal"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($row['title']); ?>"
                                                data-desc="<?php echo htmlspecialchars($row['description']); ?>"
                                                data-date="<?php echo $row['activity_date']; ?>"
                                                data-loc="<?php echo htmlspecialchars($row['location']); ?>"
                                                data-img="<?php echo htmlspecialchars($row['image_url']); ?>"
                                                data-stat="<?php echo $row['status']; ?>"
                                                data-link="<?php echo htmlspecialchars($row['link']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="admin_activity.php?delete_id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Delete this activity?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">No activities found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Add Activity Modal -->
<div class="modal fade" id="addActivityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New Activity</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="admin_activity.php">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Activity Title *</label>
                            <input type="text" class="form-control" name="title" required placeholder="e.g. Workshop IoT 2025">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Date *</label>
                            <input type="date" class="form-control" name="activity_date" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" placeholder="e.g. Aula Pertemuan">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select" name="status">
                                <option value="planned">Planned</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Activity details..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" name="image_url" placeholder="https://example.com/img.jpg">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">External Link (Video)</label>
                            <input type="url" class="form-control" name="link" placeholder="https://youtube.com/...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_activity" class="btn btn-primary px-4">Save Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Activity Modal -->
<div class="modal fade" id="editActivityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Edit Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="admin_activity.php">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Activity Title *</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Date *</label>
                            <input type="date" class="form-control" name="activity_date" id="edit_date" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" id="edit_loc">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select" name="status" id="edit_stat">
                                <option value="planned">Planned</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_desc" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" name="image_url" id="edit_img">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">External Link</label>
                            <input type="url" class="form-control" name="link" id="edit_link">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_activity" class="btn btn-warning px-4">Update Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white">Video Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <iframe id="youtubeFrame" src="" title="YouTube video" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // SCRIPT EDIT
    const editModal = document.getElementById('editActivityModal');
    if(editModal){
        editModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('edit_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_title').value = btn.getAttribute('data-title');
            document.getElementById('edit_desc').value = btn.getAttribute('data-desc');
            document.getElementById('edit_date').value = btn.getAttribute('data-date');
            document.getElementById('edit_loc').value = btn.getAttribute('data-loc');
            document.getElementById('edit_img').value = btn.getAttribute('data-img');
            document.getElementById('edit_stat').value = btn.getAttribute('data-stat');
            document.getElementById('edit_link').value = btn.getAttribute('data-link');
        });
    }

    // SCRIPT VIDEO
    const videoModal = document.getElementById('videoModal');
    const videoFrame = document.getElementById('youtubeFrame');
    if(videoModal){
        videoModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const rawUrl = button.getAttribute('data-video-url');
            let videoId = "";
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
            const match = rawUrl.match(regExp);

            if (match && match[2].length === 11) {
                videoId = match[2];
                videoFrame.src = "https://www.youtube.com/embed/" + videoId + "?autoplay=1";
            } else {
                alert("Link YouTube tidak valid!");
                event.preventDefault(); 
            }
        });
        videoModal.addEventListener('hidden.bs.modal', function () {
            videoFrame.src = "";
        });
    }
});
</script>

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