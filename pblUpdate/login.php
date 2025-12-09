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
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #7c3aed;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 450px;
            margin: 20px auto;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            border-bottom: none;
        }
        
        .login-logo {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            color: white;
        }
        
        .login-title {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .main-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
        }
        
        .sub-title {
            font-size: 1.2rem;
            font-weight: 400;
            color: rgba(255,255,255,0.9);
            margin-bottom: 1rem;
        }
        
        .admin-text {
            font-size: 1rem;
            color: rgba(255,255,255,0.8);
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.2);
            margin-top: 1rem;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .input-group-text {
            background-color: #f9fafb;
            border-right: none;
            color: #6b7280;
        }
        
        .form-control {
            border-left: none;
            padding-left: 0;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.3);
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .back-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        .demo-credentials {
            background: #f0f9ff;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1.5rem;
            border-left: 4px solid #0ea5e9;
        }
        
        .role-info {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            border-left: 4px solid var(--primary-color);
        }
        
        .demo-credentials h6,
        .role-info h6 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .demo-credentials p,
        .role-info p {
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
            color: #6b7280;
        }
        
        .demo-credentials code {
            background: #e5e7eb;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.85rem;
            color: #1f2937;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 2rem;
            color: #6b7280;
            font-size: 0.85rem;
        }
        
        .debug-info {
            background: #fef3c7;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            border-left: 4px solid #f59e0b;
            font-size: 0.8rem;
            color: #92400e;
        }
        
        @media (max-width: 768px) {
            .login-container {
                margin: 10px auto;
                padding: 0 15px;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .card-header {
                padding: 1.5rem;
            }
            
            .main-title {
                font-size: 1.5rem;
            }
            
            .sub-title {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <div class="login-logo">
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
                        <label for="username" class="form-label">Username / NIM</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   placeholder="Masukkan username atau NIM" required
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
                
                <div class="footer-text">
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