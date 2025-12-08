<?php
$page_title = "LET Lab - Dashboard";
include_once 'includes/header.php';

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true){
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

// Get statistics
$total_users = $user->getTotalUsers();
$total_activities = $activity->getTotalActivities();
$active_bookings = $booking->getActiveBookings();

// Get recent data
$recent_activities = $activity->getRecentActivities(5);
$upcoming_bookings = $booking->getUpcomingBookings(4);
?>

<?php include_once 'includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include_once 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Hari Ini</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Minggu Ini</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Bulan Ini</button>
                    </div>
                </div>
            </div>

            <!-- Welcome Message -->
            <div class="alert alert-info">
                <h4>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h4>
                <p class="mb-0">Anda login sebagai <?php echo htmlspecialchars($_SESSION['role']); ?>.</p>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title text-muted">Total Pengguna</h5>
                                    <h3 class="card-text"><?php echo $total_users; ?></h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title text-muted">Booking Aktif</h5>
                                    <h3 class="card-text"><?php echo $active_bookings; ?></h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar-check fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title text-muted">Aktivitas Penelitian</h5>
                                    <h3 class="card-text"><?php echo $total_activities; ?></h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-flask fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title text-muted">Aplikasi Terjual</h5>
                                    <h3 class="card-text">45</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-shopping-cart fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row">
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Aktivitas Terbaru</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Aktivitas</th>
                                            <th>Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $recent_activities->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                                            <td><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Booking Mendatang</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                               