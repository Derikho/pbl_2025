<?php
$page_title = "Booking Management - LET Lab Admin";
include_once 'includes/header.php';

if(!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin'){
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

include_once 'config/database.php';
include_once 'models/Booking.php';
include_once 'models/Assets.php'; 

$database = new Database();
$db = $database->getConnection();
$booking = new Booking($db);
$asset = new Asset($db);

$show_asset_form = false;
$edit_asset_mode = false;
$edit_asset_data = null;

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    if(isset($_POST['add_asset'])){
        if(empty($_POST['name'])) {
            $error_msg = "Asset name is required!";
        } else {
            $asset->name = $_POST['name'];
            $asset->category = $_POST['category'];
            $asset->description = $_POST['description'] ?? '';
            $asset->total_quantity = $_POST['total_quantity'];
            $asset->available_quantity = $_POST['total_quantity'];
            $asset->capacity = $_POST['capacity'] ?? 0;
            $asset->image_url = $_POST['image_url'] ?? '';
            $asset->is_active = true;
            
            if($asset->create()){
                $_SESSION['message'] = "Asset added successfully!";
                echo "<script>window.location.href='admin_booking.php';</script>";
                exit;
            } else {
                $error_msg = "Failed to add asset.";
            }
        }
    }

    if(isset($_POST['update_asset'])){
        $asset->id = $_POST['id'];
        $asset->name = $_POST['name'];
        $asset->category = $_POST['category'];
        $asset->description = $_POST['description'];
        $asset->total_quantity = $_POST['total_quantity'];
        $asset->available_quantity = $_POST['available_quantity'];
        $asset->capacity = $_POST['capacity'];
        $asset->image_url = $_POST['image_url'];
        $asset->is_active = isset($_POST['is_active']) ? true : false;
        
        if($asset->update()){
            $_SESSION['message'] = "Asset updated successfully!";
            echo "<script>window.location.href='admin_booking.php';</script>";
            exit;
        } else {
            $error_msg = "Failed to update asset.";
        }
    }

    if(isset($_POST['update_status'])){
        $id = $_POST['booking_id'];
        $status = $_POST['status'];
        $admin_note = $_POST['admin_note'] ?? '';
        
        if($booking->updateStatus($id, $status, $admin_note)){
            $_SESSION['message'] = "Booking status updated successfully!";
            echo "<script>window.location.href='admin_booking.php';</script>";
            exit;
        } else {
            $_SESSION['error'] = "Failed to update status.";
        }
    }
}

if(isset($_GET['delete_booking'])){
    $booking->id = $_GET['delete_booking'];
    if($booking->delete()){
        $_SESSION['message'] = "Booking deleted successfully!";
        echo "<script>window.location.href='admin_booking.php';</script>";
        exit;
    }
}

if(isset($_GET['delete_asset'])){
    $asset->id = $_GET['delete_asset'];
    if($asset->delete()){
        $_SESSION['message'] = "Asset deleted successfully!";
        echo "<script>window.location.href='admin_booking.php';</script>";
        exit;
    }
}

if(isset($_GET['action'])){
    if($_GET['action'] == 'add_asset'){
        $show_asset_form = true;
    } elseif($_GET['action'] == 'edit_asset' && isset($_GET['id'])){
        $show_asset_form = true;
        $edit_asset_mode = true;
        $stmt = $asset->read();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($row['id'] == $_GET['id']){
                $edit_asset_data = $row;
                break;
            }
        }
    }
}

$totalBookings = $booking->getTotalBookings();
$pendingBookings = $booking->getBookingsCountByStatus('pending');
$approvedBookings = $booking->getBookingsCountByStatus('approved');
$returnedBookings = $booking->getBookingsCountByStatus('returned'); 
$rejectedBookings = $booking->getBookingsCountByStatus('rejected'); 
$isRoomAvailable = $booking->getRoomAvailabilityStatus();

$bookings = $booking->getAllBookings();
$assets = $asset->read();
?>

<nav class="navbar navbar-expand-lg navbar-admin sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand text-white" href="admin_dashboard.php">
            <div class="admin-logo">
                <i class="fas fa-crown me-2"></i> <span>Admin Panel</span>
            </div>
        </a>
        <div class="navbar-actions ms-auto">
            <div class="admin-info me-3 text-white">
                <i class="fas fa-user-shield me-1"></i>
                <span class="admin-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            <a href="logout.php" class="btn btn-sm btn-outline-light">Logout</a>
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
            <li class="menu-item"><a href="admin_gallery.php"><i class="fas fa-images me-2"></i><span>Gallery</span></a></li>
            <li class="menu-item"><a href="admin_activity.php"><i class="fas fa-chart-line me-2"></i><span>Activity</span></a></li>
            <li class="menu-item active"><a href="admin_booking.php"><i class="fas fa-calendar-check me-2"></i><span>Booking</span></a></li>
            <li class="menu-item"><a href="admin_absent.php"><i class="fas fa-clipboard-list me-2"></i><span>Absent</span></a></li>
        </ul>
    </div>

    <div class="admin-content">
        <div class="content-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Booking Management</h1>
                    <p class="text-muted small">Kelola peminjaman ruangan dan alat</p>
                </div>
                <?php if(!$show_asset_form): ?>
                    <a href="admin_booking.php?action=add_asset" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i> Add Stock
                    </a>
                <?php else: ?>
                    <a href="admin_booking.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Booking
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

        <?php if($show_asset_form): ?>
            <!-- FORM ADD/EDIT ASSET -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-<?php echo $edit_asset_mode ? 'warning' : 'success'; ?> text-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-<?php echo $edit_asset_mode ? 'edit' : 'plus-circle'; ?> me-2"></i>
                        <?php echo $edit_asset_mode ? 'Edit Asset' : 'Add New Asset (Stock)'; ?>
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="admin_booking.php">
                        <?php if($edit_asset_mode): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_asset_data['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Asset Name *</label>
                                <input type="text" class="form-control" name="name" required 
                                       placeholder="e.g. Projector, Meeting Room"
                                       value="<?php echo $edit_asset_mode ? htmlspecialchars($edit_asset_data['name']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Category *</label>
                                <select class="form-select" name="category" required>
                                    <option value="tool" <?php echo ($edit_asset_mode && $edit_asset_data['category'] == 'tool') ? 'selected' : ''; ?>>Tool/Equipment</option>
                                    <option value="room" <?php echo ($edit_asset_mode && $edit_asset_data['category'] == 'room') ? 'selected' : ''; ?>>Room</option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Total Quantity *</label>
                                <input type="number" class="form-control" name="total_quantity" required min="1"
                                       value="<?php echo $edit_asset_mode ? $edit_asset_data['total_quantity'] : '1'; ?>">
                            </div>
                        </div>

                        <?php if($edit_asset_mode): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Available Quantity</label>
                                <input type="number" class="form-control" name="available_quantity" required min="0"
                                       value="<?php echo $edit_asset_data['available_quantity']; ?>">
                                <small class="form-text text-muted">Currently available for booking</small>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Details about this asset..."><?php echo $edit_asset_mode ? htmlspecialchars($edit_asset_data['description']) : ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Capacity (for rooms)</label>
                                <input type="number" class="form-control" name="capacity" min="0"
                                       placeholder="e.g. 30 people"
                                       value="<?php echo $edit_asset_mode ? $edit_asset_data['capacity'] : '0'; ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Image URL</label>
                                <input type="url" class="form-control" name="image_url" 
                                       placeholder="https://example.com/image.jpg"
                                       value="<?php echo $edit_asset_mode ? htmlspecialchars($edit_asset_data['image_url']) : ''; ?>">
                            </div>
                        </div>

                        <?php if($edit_asset_mode): ?>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" id="is_active"
                                       <?php echo ($edit_asset_data['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">
                                    Active (available for booking)
                                </label>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" name="<?php echo $edit_asset_mode ? 'update_asset' : 'add_asset'; ?>" 
                                    class="btn btn-<?php echo $edit_asset_mode ? 'warning' : 'success'; ?> px-4">
                                <i class="fas fa-save me-1"></i>
                                <?php echo $edit_asset_mode ? 'Update Asset' : 'Add to Stock'; ?>
                            </button>
                            <a href="admin_booking.php" class="btn btn-secondary px-4">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- STATISTICS CARDS -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-primary border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $totalBookings; ?></h3>
                        <p class="text-muted mb-0 small">Total</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-warning border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $pendingBookings; ?></h3>
                        <p class="text-muted mb-0 small">Pending</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-success border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $approvedBookings; ?></h3>
                        <p class="text-muted mb-0 small">Approved</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-info border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $returnedBookings; ?></h3>
                        <p class="text-muted mb-0 small">Returned</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-danger border-4">
                        <h3 class="mb-0 fw-bold"><?php echo $rejectedBookings; ?></h3>
                        <p class="text-muted mb-0 small">Rejected</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card bg-white p-3 rounded shadow-sm border-start border-<?php echo $isRoomAvailable ? 'success' : 'secondary'; ?> border-4">
                        <h4 class="mb-0 fw-bold"><?php echo $isRoomAvailable ? 'Yes' : 'No'; ?></h4>
                        <p class="text-muted mb-0 small">Room Free</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 mb-4">
                    <!-- BOOKING LIST -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">Booking Requests</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item</th>
                                            <th>User</th>
                                            <th>Date/Time</th>
                                            <th>Status</th>
                                            <th width="200">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($bookings && $bookings->rowCount() > 0): ?>
                                            <?php while($row = $bookings->fetch(PDO::FETCH_ASSOC)): ?>
                                            <tr>
                                                <td>
                                                    <strong class="d-block"><?php echo htmlspecialchars($row['item_name']); ?></strong>
                                                    <span class="badge bg-light text-dark border"><?php echo ucfirst($row['booking_type']); ?></span>
                                                    <small class="d-block text-muted">Qty: <?php echo $row['qty']; ?></small>
                                                </td>
                                                <td>
                                                    <div class="small fw-bold"><?php echo htmlspecialchars($row['borrower_name']); ?></div>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($row['borrower_email']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="small"><?php echo date('d M Y', strtotime($row['start_date'])); ?></div>
                                                    <div class="text-muted small">
                                                        <?php echo date('H:i', strtotime($row['start_date'])); ?> - 
                                                        <?php echo date('H:i', strtotime($row['end_date'])); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $badge = 'secondary';
                                                        if($row['status'] == 'approved') $badge = 'success';
                                                        elseif($row['status'] == 'pending') $badge = 'warning';
                                                        elseif($row['status'] == 'rejected') $badge = 'danger';
                                                        elseif($row['status'] == 'returned') $badge = 'info';
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($row['status']); ?></span>
                                                </td>
                                                <td>
                                                    <?php if($row['status'] == 'pending'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                                            <input type="hidden" name="status" value="approved">
                                                            <button type="submit" name="update_status" class="btn btn-sm btn-success me-1" title="Approve">
                                                                <i class="fas fa-check"></i> Approve
                                                            </button>
                                                        </form>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                                            <input type="hidden" name="status" value="rejected">
                                                            <button type="submit" name="update_status" class="btn btn-sm btn-danger me-1" title="Reject">
                                                                <i class="fas fa-times"></i> Reject
                                                            </button>
                                                        </form>
                                                    <?php elseif($row['status'] == 'approved'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                                            <input type="hidden" name="status" value="returned">
                                                            <button type="submit" name="update_status" class="btn btn-sm btn-info me-1" title="Mark as Returned">
                                                                <i class="fas fa-undo"></i> Returned
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <a href="admin_booking.php?delete_booking=<?php echo $row['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Delete this booking?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No bookings yet.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- ASSET STATUS (REAL-TIME) -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">Asset Status (Real-time)</h5>
                        </div>
                        <div class="card-body">
                            <?php 
                            $assets_list = $asset->read();
                            if($assets_list && $assets_list->rowCount() > 0):
                                while($ast = $assets_list->fetch(PDO::FETCH_ASSOC)): 
                            ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold"><?php echo htmlspecialchars($ast['name']); ?></span>
                                    <span class="small text-muted"><?php echo $ast['available_quantity']; ?> / <?php echo $ast['total_quantity']; ?></span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <?php 
                                    $percentage = ($ast['total_quantity'] > 0) ? ($ast['available_quantity']/$ast['total_quantity'])*100 : 0;
                                    $color = $percentage > 50 ? 'success' : ($percentage > 20 ? 'warning' : 'danger');
                                    ?>
                                    <div class="progress-bar bg-<?php echo $color; ?>" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted"><?php echo ucfirst($ast['category']); ?></small>
                                    <div>
                                        <a href="admin_booking.php?action=edit_asset&id=<?php echo $ast['id']; ?>" class="btn btn-xs btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="admin_booking.php?delete_asset=<?php echo $ast['id']; ?>" 
                                           class="btn btn-xs btn-outline-danger"
                                           onclick="return confirm('Delete this asset?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <p class="text-muted text-center py-3">No assets available. Click "Add Stock" to add assets.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ROOM STATUS -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <h6 class="mb-3">Room Status</h6>
                            <?php if($isRoomAvailable): ?>
                                <h3 class="text-success fw-bold mb-0">
                                    <i class="fas fa-door-open fa-2x d-block mb-2"></i>
                                    Available
                                </h3>
                            <?php else: ?>
                                <h3 class="text-danger fw-bold mb-0">
                                    <i class="fas fa-door-closed fa-2x d-block mb-2"></i>
                                    In Use
                                </h3>
                            <?php endif; ?>
                        </div>
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
        display: flex;
        background-color: #f8f9fa;
        min-height: 100vh;
    }
    .sidebar-header {
        text-align: center;
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
    .admin-content {
        flex: 1;
        padding: 20px;
    }
    .btn-xs {
        padding: 0.15rem 0.4rem;
        font-size: 0.75rem;
    }
</style>

<?php include_once 'includes/footer.php'; ?>