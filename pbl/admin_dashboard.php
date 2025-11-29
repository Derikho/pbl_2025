<?php
$page_title = "Admin Dashboard - LET Lab";
include_once 'includes/header.php';

// Check admin session
if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    header("location: login.php");
    exit;
}

include_once 'config/database.php';
include_once 'models/User.php';
include_once 'models/Activity.php';
include_once 'models/Booking.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$activity = new Activity($db);
$booking = new Booking($db);

$total_users = $user->getTotalUsers();
$total_activities = $activity->getTotalActivities();
$active_bookings = $booking->getActiveBookings();
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
            <h5>Navigation</h5>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item active">
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
            <li class="menu-item">
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
        <div class="content-header">
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo $active_bookings; ?></h3>
                        <p>Active Bookings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo $total_activities; ?></h3>
                        <p>Activities</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stats-info">
                        <h3>45</h3>
                        <p>Products Sold</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Activity</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Admin</td>
                                        <td>Updated system settings</td>
                                        <td>2 minutes ago</td>
                                    </tr>
                                    <tr>
                                        <td>User1</td>
                                        <td>Booked laboratory equipment</td>
                                        <td>1 hour ago</td>
                                    </tr>
                                    <tr>
                                        <td>Admin</td>
                                        <td>Added new product</td>
                                        <td>3 hours ago</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="admin_products.php" class="action-btn">
                                <i class="fas fa-plus"></i>
                                <span>Add Product</span>
                            </a>
                            <a href="admin_news.php" class="action-btn">
                                <i class="fas fa-newspaper"></i>
                                <span>Add News</span>
                            </a>
                            <a href="admin_booking.php" class="action-btn">
                                <i class="fas fa-calendar"></i>
                                <span>Manage Bookings</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .sidebar-header {
    text-align: center;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 1rem;
}
</style>
<?php include_once 'includes/footer.php'; ?>