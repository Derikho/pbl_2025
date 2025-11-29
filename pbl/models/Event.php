<?php
$page_title = "Activity Management - LET Lab Admin";
include_once 'includes/header.php';

// Check admin session
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    header("location: login.php");
    exit;
}

include_once 'config/database.php';
include_once 'models/Event.php'; // Gunakan class Event yang baru
include_once 'models/Activity.php'; // Class yang sudah ada untuk activity log

$database = new Database();
$db = $database->getConnection();
$event = new Event($db);
$activityLog = new Activity($db); // Untuk activity log

// Handle form actions untuk events
if($_POST){
    if(isset($_POST['add_event'])){
        $event->title = $_POST['title'];
        $event->description = $_POST['description'];
        $event->event_date = $_POST['event_date'];
        $event->location = $_POST['location'];
        $event->image_url = $_POST['image_url'];
        $event->status = $_POST['status'];
        $event->link = $_POST['link'];
        
        if($event->create()){
            $_SESSION['message'] = "Event added successfully!";
            header("location: admin_activity.php");
            exit;
        }
    }
    
    if(isset($_POST['update_event'])){
        $event->id = $_POST['id'];
        $event->title = $_POST['title'];
        $event->description = $_POST['description'];
        $event->event_date = $_POST['event_date'];
        $event->location = $_POST['location'];
        $event->image_url = $_POST['image_url'];
        $event->status = $_POST['status'];
        $event->link = $_POST['link'];
        
        if($event->update()){
            $_SESSION['message'] = "Event updated successfully!";
            header("location: admin_activity.php");
            exit;
        }
    }
}

if(isset($_GET['delete_id'])){
    $event->id = $_GET['delete_id'];
    if($event->delete()){
        $_SESSION['message'] = "Event deleted successfully!";
        header("location: admin_activity.php");
        exit;
    }
}

$events = $event->read();
$eventsCount = $event->getEventsCountByStatus();
?>

<!-- Admin Navbar -->
<nav class="navbar navbar-expand-lg navbar-admin sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php">
            <div class="admin-logo">
                <i class="fas fa-crown me-2"></i>
                <span>Admin Panel</span>
            </div>
        </a>
        
        <div class="navbar-actions ms-auto">
            <div class="admin-info me-3">
                <i class="fas fa-user-shield me-1"></i>
                <span class="admin-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <small class="badge bg-warning">Admin</small>
            </div>
            <a href="logout.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>
    </div>
</nav>

<div class="admin-container">
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <h5>INFORMATION AND LEARNING</h5>
            <h6>MANUFACTURED TECHNOLOGY</h6>
        </div>
        <ul class="sidebar-menu">
            <!-- ... menu items sama seperti sebelumnya ... -->
            <li class="menu-item active">
                <a href="admin_activity.php">
                    <i class="fas fa-chart-line me-2"></i>
                    <span>Activity</span>
                </a>
            </li>
            <!-- ... menu items lainnya ... -->
        </ul>
    </div>

    <div class="admin-content">
        <div class="content-header">
            <h1>Activity Management</h1>
            <p>Kelola Kegiatan InLET</p>
        </div>

        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo $event->getTotalEvents(); ?></h3>
                        <p>Total Events</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: #28a745;">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo $eventsCount['ongoing'] ?? 0; ?></h3>
                        <p>Ongoing</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: #6c757d;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo $eventsCount['completed'] ?? 0; ?></h3>
                        <p>Completed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: #17a2b8;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo $eventsCount['upcoming'] ?? 0; ?></h3>
                        <p>Upcoming</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Events Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Events List</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                        <i class="fas fa-plus me-1"></i>Add Event
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
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
                            <?php while($item = $events->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td>
                                    <div class="event-title">
                                        <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                        <?php if($item['description']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>...</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="event-date">
                                        <?php echo date('M j, Y', strtotime($item['event_date'])); ?>
                                        <?php if($item['event_date'] == date('Y-m-d')): ?>
                                            <br><small class="badge bg-warning">Today</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if($item['location']): ?>
                                        <i class="fas fa-map-marker-alt me-1 text-muted"></i>
                                        <?php echo htmlspecialchars($item['location']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $item['status'] == 'upcoming' ? 'info' : 
                                             ($item['status'] == 'ongoing' ? 'success' : 
                                             ($item['status'] == 'completed' ? 'secondary' : 'warning')); 
                                    ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($item['link']): ?>
                                        <a href="<?php echo $item['link']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt me-1"></i>View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editEventModal"
                                                data-id="<?php echo $item['id']; ?>"
                                                data-title="<?php echo htmlspecialchars($item['title']); ?>"
                                                data-description="<?php echo htmlspecialchars($item['description']); ?>"
                                                data-event_date="<?php echo $item['event_date']; ?>"
                                                data-location="<?php echo $item['location']; ?>"
                                                data-image_url="<?php echo $item['image_url']; ?>"
                                                data-link="<?php echo $item['link']; ?>"
                                                data-status="<?php echo $item['status']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="admin_activity.php?delete_id=<?php echo $item['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this event?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals untuk Add/Edit Event (sama seperti sebelumnya, tapi ganti nama form menjadi add_event/update_event) -->
<!-- ... kode modal sama seperti sebelumnya ... -->

<?php include_once 'includes/footer.php'; ?>