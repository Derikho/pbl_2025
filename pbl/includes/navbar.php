<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <!-- Logo dengan Gambar -->
        <a class="navbar-brand" href="index.php">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="LET Lab Logo" class="logo-img">
                <div class="logo-text">
                    <span class="logo-main">LET Lab</span>
                    <span class="logo-sub">Learning Engineering Technology</span>
                </div>
            </div>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#about">
                        <i class="fas fa-info-circle me-1"></i>About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#research">
                        <i class="fas fa-flask me-1"></i>Research
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#activities">
                        <i class="fas fa-chart-line me-1"></i>Activities
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#store">
                        <i class="fas fa-shopping-cart me-1"></i>Store
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#gallery">
                        <i class="fas fa-images me-1"></i>Gallery
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#contact">
                        <i class="fas fa-envelope me-1"></i>Contact
                    </a>
                </li>
            </ul>
            
            <div class="navbar-actions">
                <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <div class="user-info me-3">
                        <i class="fas fa-user-circle me-1"></i>
                        <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </div>
                    
                    <?php 
                    $link_tujuan = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'dashboard.php';
                    ?>

                    <a href="<?php echo $link_tujuan; ?>" class="btn btn-nav me-2">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>

                    <a href="logout.php" class="btn btn-nav-outline">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-nav">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>