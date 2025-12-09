<?php
// 1. LOGIKA PHP DILETAKKAN DI PALING ATAS
// -----------------------------------------------------------

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// HAPUS SESSION LAMA JIKA MEMBUKA HALAMAN LOGIN
if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true){
    $_SESSION = array();
    session_destroy();
    session_start(); 
}

// Include database & model
include_once 'config/database.php';
include_once 'models/User.php';

$database = new Database();
$db = $database->getConnection();

$login_err = "";

// Proses Form Login (POST)
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    echo "<!-- DEBUG: Username: $username, Password: $password -->";
    
    // Query untuk mencari user dengan debugging
    $query = "SELECT * FROM users WHERE (username = :username OR identification_number = :username)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    
    try {
        $stmt->execute();
        $user_count = $stmt->rowCount();
        echo "<!-- DEBUG: Found $user_count user(s) -->";
        
        if($user_data = $stmt->fetch(PDO::FETCH_ASSOC)){
            echo "<!-- DEBUG: User found - Username: " . $user_data['username'] . ", Role: " . $user_data['role'] . ", is_active: " . $user_data['is_active'] . " -->";
            echo "<!-- DEBUG: Stored password: " . $user_data['password'] . " -->";
            echo "<!-- DEBUG: Input password: $password -->";
            
            // Verifikasi password sederhana (untuk testing)
            if($password === 'password'){ 
                
                echo "<!-- DEBUG: Password verification successful -->";
                
                // SET SESSION BERDASARKAN ROLE
                if($user_data['role'] === 'admin'){
                    echo "<!-- DEBUG: Admin login detected, redirecting to admin_dashboard.php -->";
                    
                    // SESSION UNTUK ADMIN
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $user_data['user_id'];
                    $_SESSION['username'] = $user_data['username'];
                    $_SESSION['full_name'] = $user_data['full_name'];
                    $_SESSION['role'] = $user_data['role'];
                    $_SESSION['login_type'] = 'admin';
                    
                    header("location: admin_dashboard.php");
                    exit;
                    
                } elseif($user_data['role'] === 'member'){
                    echo "<!-- DEBUG: Member login detected, redirecting to member_absent.php -->";
                    
                    // Destroy any existing session first
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        session_destroy();
                    }
                    
                    // Start member session
                    session_name('MEMBER_SESSION');
                    session_start();
                    
                    // Clear any existing member session
                    $_SESSION = array();
                    
                    // SET SESSION MEMBER
                    $_SESSION['member_logged_in'] = true;
                    $_SESSION['member_user_id'] = $user_data['user_id'];
                    $_SESSION['member_username'] = $user_data['username'];
                    $_SESSION['member_full_name'] = $user_data['full_name'];
                    $_SESSION['member_nim'] = $user_data['identification_number'];
                    $_SESSION['member_institution'] = $user_data['institution'];
                    $_SESSION['member_email'] = $user_data['email'];
                    $_SESSION['member_role'] = $user_data['role'];
                    $_SESSION['member_student_type'] = $user_data['student_type'];
                    $_SESSION['member_login_time'] = date('Y-m-d H:i:s');
                    
                    echo "<!-- DEBUG: Member session set, redirecting... -->";
                    
                    // Check if session was set
                    if(isset($_SESSION['member_logged_in'])) {
                        echo "<!-- DEBUG: member_logged_in is SET -->";
                    } else {
                        echo "<!-- DEBUG: member_logged_in is NOT SET -->";
                    }
                    
                    header("Location: member_absent.php");
                    exit();
                    
                } else {
                    // Role lain (dosen, dll)
                    echo "<!-- DEBUG: Other role login detected, redirecting to index.php -->";
                    
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $user_data['user_id'];
                    $_SESSION['username'] = $user_data['username'];
                    $_SESSION['role'] = $user_data['role'];
                    
                    header("location: index.php");
                    exit;
                }
                
            } else {
                echo "<!-- DEBUG: Password verification failed -->";
                $login_err = "Username atau password salah.";
            }
        } else {
            echo "<!-- DEBUG: No user found with username: $username -->";
            $login_err = "Username atau password salah.";
        }
        
    } catch (PDOException $e) {
        echo "<!-- DEBUG: Database error: " . $e->getMessage() . " -->";
        $login_err = "Terjadi kesalahan sistem. Silakan coba lagi.";
    }
}

// 2. BARU TAMPILKAN HTML
// -----------------------------------------------------------
$page_title = "LET Lab - Login";
include_once 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LET Lab - Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header text-center mb-5">
                <div class="login-logo mb-4">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="login-title">
                    <h1 class="main-title">INFORMATION AND LEARNING</h1>
                    <h2 class="sub-title">ENGINEERING TECHNOLOGY</h2>
                    <p class="admin-text">Login Portal</p>
                </div>
            </div>
            
            <div class="card-body">
                <?php 
                if(!empty($login_err)){
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                    echo '<i class="fas fa-exclamation-circle me-2"></i>' . $login_err;
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    echo '</div>';
                }        
                ?>
                
                <!-- Debug Info Box -->
               
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-4">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Masukkan username" required
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Masukkan password" required>
                        </div>
                    </div>
                    
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                    
                    <button type="submit" class="btn btn-login w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                    
                    <div class="text-center mt-4">
                        <a href="index.php" class="back-link">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Halaman Utama
                        </a>
                    </div>
                </form>
                
                <div class="footer-text text-center">
                    <small>
                        <i class="fas fa-shield-alt me-1"></i>
                        Sistem Terintegrasi &copy; <?php echo date('Y'); ?> LET Lab
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus pada input username
        document.getElementById('username').focus();
        
        // Show/hide password toggle
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const inputGroup = passwordInput.parentNode;
            
            // Create show/hide button
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'btn btn-outline-secondary';
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
            toggleBtn.style.borderLeft = 'none';
            toggleBtn.style.borderTopLeftRadius = '0';
            toggleBtn.style.borderBottomLeftRadius = '0';
            
            // Add click event
            toggleBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });
            
            // Add button to input group
            inputGroup.appendChild(toggleBtn);
            
            // Fix input border
            passwordInput.style.borderTopRightRadius = '0';
            passwordInput.style.borderBottomRightRadius = '0';
        });
    </script>
</body>
</html>