<?php
$page_title = "Form Peminjaman - LET Lab";
include_once 'includes/header.php';
include_once 'includes/navbar.php';

include_once 'config/database.php';
include_once 'models/Booking.php';
include_once 'models/Assets.php'; 

$database = new Database();
$db = $database->getConnection();

$booking = new Booking($db);
$asset = new Asset($db);

$assets_list = $asset->read(); 

$message = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking->borrower_name = $_POST['name'];
    $booking->borrower_email = $_POST['email'];
    $booking->borrower_contact = $_POST['contact'];
    $booking->institution = $_POST['institution'];
    $booking->asset_id = $_POST['asset_id'];
    $booking->qty = $_POST['qty'];
    
    // Gabungkan tanggal dan jam
    $booking->start_time = $_POST['date'] . ' ' . $_POST['start_time'];
    $booking->end_time = $_POST['date'] . ' ' . $_POST['end_time'];

    if ($booking->create()) {
        $message = "Permintaan peminjaman berhasil dikirim! Silakan tunggu konfirmasi dari Admin.";
        $msg_type = "success";
    } else {
        $message = "Gagal mengirim permintaan. Silakan coba lagi.";
        $msg_type = "danger";
    }
}
?>

<section class="bg-primary text-white text-center py-5 mb-5">
    <div class="container">
        <h2 class="fw-bold">Form Peminjaman Laboratorium</h2>
        <p class="lead">Isi formulir di bawah ini untuk mengajukan peminjaman alat atau ruangan.</p>
    </div>
</section>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <?php if($message): ?>
                <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $msg_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow border-0 rounded-3">
                <div class="card-body p-5">
                    <form method="POST" action="booking.php">
                        
                        <h5 class="text-primary mb-4"><i class="fas fa-user-edit me-2"></i>Data Peminjam</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input type="text" class="form-control" name="name" required placeholder="Contoh: Budi Santoso">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Asal Institusi / Jurusan</label>
                                <input type="text" class="form-control" name="institution" required placeholder="Contoh: D4 Teknik Informatika">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" name="email" required placeholder="email@example.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">No. HP / WhatsApp</label>
                                <input type="text" class="form-control" name="contact" required placeholder="0812xxxxxxxx">
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="text-primary mb-4"><i class="fas fa-box-open me-2"></i>Detail Peminjaman</h5>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Alat / Ruangan</label>
                            <select class="form-select" name="asset_id" required>
                                <option value="" selected disabled>-- Pilih Aset --</option>
                                <?php 
                                if($assets_list && $assets_list->rowCount() > 0) {
                                    while ($row = $assets_list->fetch(PDO::FETCH_ASSOC)) {
                                        // Hanya tampilkan yang aktif dan stok tersedia
                                        if($row['is_active'] && $row['available_quantity'] > 0){
                                            echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . " (Tersedia: " . $row['available_quantity'] . ")</option>";
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Jumlah (Qty)</label>
                                <input type="number" class="form-control" name="qty" value="1" min="1" required>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Tanggal Peminjaman</label>
                                <input type="date" class="form-control" name="date" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jam Mulai</label>
                                <input type="time" class="form-control" name="start_time" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jam Selesai</label>
                                <input type="time" class="form-control" name="end_time" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Permintaan Peminjaman
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">Kembali ke Beranda</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>