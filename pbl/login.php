<?php
// 1. LOGIKA PHP DILETAKKAN DI PALING ATAS (Sebelum HTML apapun)
// -----------------------------------------------------------

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// HAPUS SESSION LAMA JIKA MEMBUKA HALAMAN LOGIN
// Ini memastikan user melihat form login, bukan di-redirect otomatis jika session masih nyangkut
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
$user = new User($db);

$login_err = "";

// Proses Form Login (POST)
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $user->username = trim($_POST["username"]);
    $user->password = trim($_POST["password"]);
    
    if($user->login()){
        // Set Variabel Session
        $_SESSION["loggedin"] = true;
        $_SESSION["id"] = $user->id;
        $_SESSION["user_id"] = $user->id; // Konsistensi nama variabel
        $_SESSION["username"] = $user->username;
        $_SESSION["role"] = $user->role;
        
        // Redirect berdasarkan Role
        if($user->role === 'admin'){
            header("location: admin_dashboard.php");
        } else {
            // Dosen & Mahasiswa ke Halaman Utama
            header("location: index.php");
        }
        exit; // PENTING: Hentikan script setelah redirect
    } else {
        $login_err = "Username atau password salah.";
    }
}

// 2. BARU TAMPILKAN HTML (Setelah logika redirect selesai)
// -----------------------------------------------------------
$page_title = "LET Lab - Login";
include_once 'includes/header.php'; 
?>

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
        
        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required>
                </div>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                </div>
            </div>
            
            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            
            <button type="submit" class="btn btn-login w-100 mb-3">Login</button>
            
            <div class="text-center">
                <a href="index.php" class="back-link">Kembali ke Halaman Utama</a>
            </div>
        </form>
    </div>
</div>

