<?php
// ============================================
// SESSION KHUSUS UNTUK MEMBER/MAHASISWA
// ============================================
session_name('MEMBER_SESSION');
session_start();

// Database connection
include_once 'config/database.php';
include_once 'models/Absent.php';

$database = new Database();
$db = $database->getConnection();
$absent = new Absent($db);

// ============================================
// CEK STATUS LOGIN MEMBER
// ============================================
$member_logged_in = isset($_SESSION['member_logged_in']) && $_SESSION['member_logged_in'] === true;

// Jika belum login, redirect ke halaman login umum
if(!$member_logged_in){
    header("Location: login.php");
    exit;
}

// Ambil data user dari session
$user_id = $_SESSION['member_user_id'];
$full_name = $_SESSION['member_full_name'];
$nim = $_SESSION['member_nim'];
$institution = $_SESSION['member_institution'];
$email = $_SESSION['member_email'];
$student_type = $_SESSION['member_student_type'];
$student_type_label = Absent::getStudentTypeLabel($student_type);

// AMBIL DATA DARI MODEL
$today_attendance = $absent->getTodayAttendance($user_id);
$attendance_history = $absent->getHistory($user_id, 10);
$stats = $absent->getStats($user_id);
$user_info = $absent->getUserInfo($user_id);

// ============================================
// PROSES ABSENSI (Menggunakan Model)
// ============================================
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // 1. PROSES CHECK-IN/ABSENSI
    if(isset($_POST['submit_attendance'])){
        $matakuliah = $_POST['matakuliah'] ?? '';
        $keterangan = $_POST['keterangan'] ?? 'Hadir';
        $catatan = $_POST['catatan'] ?? '';
        
        if(empty($matakuliah)){
            $attendance_error = "❌ Mata kuliah/kegiatan harus diisi!";
        } else {
            // Upload foto jika ada
            $photo_path = '';
            if(isset($_FILES['photo_file']) && $_FILES['photo_file']['error'] == 0){
                $upload_result = $absent->uploadPhoto($_FILES['photo_file'], $user_id);
                if($upload_result['success']){
                    $photo_path = $upload_result['path'];
                } else {
                    $attendance_error = $upload_result['message'];
                }
            }
            
            // Simpan absensi menggunakan model
            if(!isset($attendance_error)){
                $result = $absent->create($user_id, $matakuliah, $keterangan, $catatan, $photo_path);
                
                if($result['success']){
                    $_SESSION['attendance_success'] = $result['message'];
                    $_SESSION['last_attendance'] = date('Y-m-d H:i:s');
                    header("Location: member_absent.php");
                    exit;
                } else {
                    $attendance_error = $result['message'];
                }
            }
        }
    }
    
    // 2. PROSES CHECK-OUT/PULANG
    if(isset($_POST['checkout'])){
        $result = $absent->checkout($user_id);
        
        if($result['success']){
            $_SESSION['checkout_success'] = $result['message'];
            header("Location: member_absent.php");
            exit;
        } else {
            $checkout_error = $result['message'];
        }
    }
}

// ============================================
// PROSES LOGOUT MEMBER
// ============================================
if(isset($_GET['logout'])){
    // Clear semua session member
    unset($_SESSION['member_logged_in']);
    unset($_SESSION['member_user_id']);
    unset($_SESSION['member_username']);
    unset($_SESSION['member_full_name']);
    unset($_SESSION['member_nim']);
    unset($_SESSION['member_institution']);
    unset($_SESSION['member_email']);
    unset($_SESSION['member_role']);
    unset($_SESSION['member_student_type']);
    unset($_SESSION['member_login_time']);
    
    session_destroy();
    setcookie('MEMBER_SESSION', '', time() - 3600, '/');
    
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Mahasiswa - LET Lab</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href=assets/css/style.css>
    <style>
        .attendance-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            border-bottom: none;
        }
        
        .profile-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-left: 5px solid var(--primary-color);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.25rem;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            border-top: 4px solid var(--primary-color);
        }
        
        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .attendance-item {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-left: 4px solid var(--primary-color);
            transition: all 0.2s;
        }
        
        .attendance-item:hover {
            background: #f1f5f9;
            transform: translateX(5px);
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .student-badge {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .institution-badge {
            background-color: #f0f9ff;
            color: #0369a1;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
        }
        
        .photo-upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .photo-upload-area:hover {
            border-color: var(--primary-color);
            background: #f3f4f6;
        }
        
        .today-status-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #bae6fd;
        }
        
        .time-display {
            font-family: 'Courier New', monospace;
            background: #1f2937;
            color: #10b981;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .attendance-container {
                margin: 10px auto;
                padding: 0 10px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="attendance-container bg-linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%)">
        <!-- Header Profil -->
        <div class="profile-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px;">
                                <i class="fas fa-user-graduate fa-2x"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="mb-1"><?php echo htmlspecialchars($full_name); ?></h3>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <span class="student-badge">
                                    <i class="fas fa-id-card me-1"></i>
                                    NIM: <?php echo htmlspecialchars($nim); ?>
                                </span>
                                <span class="institution-badge">
                                    <i class="fas fa-university me-1"></i>
                                    <?php echo htmlspecialchars($institution); ?>
                                </span>
                                <span class="badge bg-info">
                                    <i class="fas fa-user-tag me-1"></i>
                                    <?php 
                                    $role_labels = [
                                        'member' => 'Mahasiswa',
                                        'admin' => 'Administrator',
                                        'dosen' => 'Dosen'
                                    ];
                                    echo htmlspecialchars($role_labels[$_SESSION['member_role']] ?? $_SESSION['member_role']);
                                    ?>
                                </span>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-graduation-cap me-1"></i>
                                    <?php echo htmlspecialchars($student_type_label); ?>
                                </span>
                            </div>
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar-day me-1"></i> 
                                <?php echo date('l, d F Y'); ?> | 
                                <span class="time-display"><?php echo date('H:i:s'); ?></span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="?logout=true" class="btn btn-danger" onclick="return confirm('Yakin ingin logout?')">
                        <i class="fas fa-sign-out-alt me-1"></i> Keluar
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Pesan Sukses/Error -->
        <?php if(isset($_SESSION['attendance_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> 
                <strong>Berhasil!</strong> <?php echo $_SESSION['attendance_success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['attendance_success']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['checkout_success'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> 
                <strong>Berhasil!</strong> <?php echo $_SESSION['checkout_success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['checkout_success']); ?>
        <?php endif; ?>
        
        <?php if(isset($attendance_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> 
                <?php echo $attendance_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($checkout_error)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> 
                <?php echo $checkout_error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Status Hari Ini -->
        <div class="today-status-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-2">Status Kehadiran Hari Ini</h5>
                    <?php if($today_attendance): 
                        $status = Absent::getAttendanceStatus($today_attendance['check_in_time'], $today_attendance['check_out_time']);
                    ?>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-<?php echo $status['badge']; ?> me-3">
                                <i class="fas <?php echo $status['icon']; ?> me-1"></i>
                                <?php echo $status['status']; ?>
                            </span>
                            <div>
                                <?php if($today_attendance['check_in_time']): ?>
                                    <small class="text-muted">Check-in: <?php echo date('H:i', strtotime($today_attendance['check_in_time'])); ?></small>
                                <?php endif; ?>
                                <?php if($today_attendance['check_out_time']): ?>
                                    <small class="text-muted ms-3">Check-out: <?php echo date('H:i', strtotime($today_attendance['check_out_time'])); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php 
                        $note = Absent::parseLocationNote($today_attendance['location_note']);
                        if(!empty($note['matakuliah'])): 
                        ?>
                            <div class="mt-2">
                                <small><strong>Kegiatan:</strong> <?php echo htmlspecialchars($note['matakuliah']); ?></small>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-secondary me-3">
                                <i class="fas fa-clock me-1"></i>
                                Belum Absen
                            </span>
                            <small class="text-muted">Silakan lakukan absensi di bawah</small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-end">
                    <?php if($today_attendance && empty($today_attendance['check_out_time'])): ?>
                        <form method="POST" action="" class="d-inline">
                            <button type="submit" name="checkout" class="btn btn-warning">
                                <i class="fas fa-sign-out-alt me-1"></i> Check-out
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Statistik -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_absensi'] ?? 0; ?></div>
                <div class="stat-label">Total Absensi</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['hari_ini'] ?? 0; ?></div>
                <div class="stat-label">Hari Ini</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['tepat_waktu'] ?? 0; ?></div>
                <div class="stat-label">Tepat Waktu</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['rata_jam_kerja'] ?? 0; ?>h</div>
                <div class="stat-label">Rata-rata Jam Kerja</div>
            </div>
        </div>
        
        <!-- Form Absensi (hanya tampil jika belum absen hari ini) -->
        <?php if(!$today_attendance): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Form Absensi Harian</h5>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mx-auto">

                        <!-- Keterangan Kehadiran -->
                        <label class="form-label fw-bold">Keterangan Kehadiran *</label>
                        <select name="status" class="form-select mb-3 w-100">
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                        </select>

                        <!-- Catatan -->
                        <label class="form-label fw-bold">Catatan Tambahan</label>
                        <textarea class="form-control mb-3 w-100" rows="3"
                            placeholder="Isi dengan kegiatan yang dilakukan atau alasan jika izin/sakit..."></textarea>

                        <!-- Upload Foto -->
                        <label class="form-label fw-bold">Foto Bukti Kehadiran (Opsional)</label>

                        <div class="border border-2 rounded p-4 text-center mb-4 w-100"
                            style="border-style: dashed !important;">
                            <i class="fas fa-camera fa-2x mb-2 text-secondary"></i>
                            <p>Klik untuk upload foto</p>
                            <p class="text-muted small mb-0">Max 5MB | JPG, PNG, GIF</p>
                        </div>

                        <!-- Submit -->
                        <button class="btn btn-primary w-100 py-2">
                            <i class="fas fa-paper-plane me-2"></i> Submit Absensi
                        </button>

                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Riwayat Absensi -->
        <div class="card col-12">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Absensi 10 Terakhir</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($attendance_history)): ?>
                    <?php foreach($attendance_history as $history): 
                        $status = Absent::getAttendanceStatus($history['check_in_time'], $history['check_out_time']);
                        $note = Absent::parseLocationNote($history['location_note']);
                    ?>
                    <div class="attendance-item">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <strong><?php echo date('d M Y', strtotime($history['date'])); ?></strong>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-<?php echo $status['badge']; ?>">
                                    <i class="fas <?php echo $status['icon']; ?> me-1"></i>
                                    <?php echo $status['status']; ?>
                                </span>
                            </div>
                            <div class="col-md-2">
                                <?php if(!empty($history['check_in_time'])): ?>
                                    <small><i class="fas fa-sign-in-alt me-1"></i> <?php echo date('H:i', strtotime($history['check_in_time'])); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-2">
                                <?php if(!empty($history['check_out_time'])): ?>
                                    <small><i class="fas fa-sign-out-alt me-1"></i> <?php echo date('H:i', strtotime($history['check_out_time'])); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <small class="text-truncate d-block">
                                    <?php echo htmlspecialchars($note['matakuliah'] ?: '-'); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Belum ada riwayat absensi</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-4 mb-3">
            <small class="text-muted">
                <i class="fas fa-shield-alt me-1"></i>
                Sistem Absensi Mahasiswa &copy; <?php echo date('Y'); ?> LET Lab
            </small>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview image
        function previewImage(input) {
            const preview = document.getElementById('photoPreview');
            
            if (input.files && input.files[0]) {
                if(input.files[0].size > 5 * 1024 * 1024){
                    alert('Ukuran file terlalu besar! Maksimal 5MB.');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="img-fluid rounded">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Update waktu real-time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            
            document.querySelectorAll('.time-display').forEach(el => {
                el.textContent = timeString;
            });
            
            document.title = `Absensi [${timeString}] - LET Lab`;
        }
        
        setInterval(updateTime, 1000);
        updateTime();
        
        // Auto-logout setelah 2 jam
        let inactivityTimer;
        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                if(confirm('Session akan berakhir karena tidak ada aktivitas. Lanjutkan?')) {
                    resetInactivityTimer();
                } else {
                    window.location.href = '?logout=true';
                }
            }, 7200000); // 2 jam
        }
        
        // Reset timer pada aktivitas user
        window.onload = resetInactivityTimer;
        window.onmousemove = resetInactivityTimer;
        window.onmousedown = resetInactivityTimer;
        window.ontouchstart = resetInactivityTimer;
        window.onclick = resetInactivityTimer;
        window.onkeypress = resetInactivityTimer;
        
        // Auto-scroll ke form jika ada error
        document.addEventListener('DOMContentLoaded', function() {
            const attendanceError = document.querySelector('.alert-danger');
            if(attendanceError) {
                attendanceError.scrollIntoView({ behavior: 'smooth' });
            }
        });
    </script>
</body>
</html>