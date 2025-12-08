<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="modern-sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header-modern">
        <div class="sidebar-brand">
            <img src="assets/img/logo_let.png" alt="Logo" class="sidebar-logo" onerror="this.style.display='none'">
            <div class="brand-text">
                <h5 class="mb-0">LET Lab</h5>
                <small>Admin Panel</small>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Menu -->
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link-modern <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                    <div class="nav-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <span class="nav-text">Dashboard</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="users.php" class="nav-link-modern <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
                    <div class="nav-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="nav-text">Manajemen User</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="absensi.php" class="nav-link-modern <?php echo ($current_page == 'absensi.php') ? 'active' : ''; ?>">
                    <div class="nav-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <span class="nav-text">Absensi</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="booking.php" class="nav-link-modern <?php echo ($current_page == 'booking.php') ? 'active' : ''; ?>">
                    <div class="nav-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <span class="nav-text">Booking</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
            
            <li class="nav-divider">
                <span>Analytics & Reports</span>
            </li>
            
            <li class="nav-item">
                <a href="analytics.php" class="nav-link-modern <?php echo ($current_page == 'analytics.php') ? 'active' : ''; ?>">
                    <div class="nav-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <span class="nav-text">Analitik</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
            
            <li class="nav-divider">
                <span>System</span>
            </li>
            
            <li class="nav-item">
                <a href="settings.php" class="nav-link-modern <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                    <div class="nav-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span class="nav-text">Pengaturan</span>
                    <div class="nav-indicator"></div>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer-modern">
        <div class="user-profile-mini">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-info">
                <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong>
                <small><?php echo ucfirst($_SESSION['role'] ?? 'Administrator'); ?></small>
            </div>
        </div>
        <button class="btn-logout-mini" onclick="document.getElementById('logoutModal').classList.add('show');" title="Logout">
            <i class="fas fa-sign-out-alt"></i>
        </button>
    </div>
</div>

<style>
.modern-sidebar {
    width: 280px;
    height: 100vh;
    background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
    position: fixed;
    left: 0;
    top: 0;
    display: flex;
    flex-direction: column;
    border-right: 1px solid #e0e6ed;
    box-shadow: 2px 0 20px rgba(0, 0, 0, 0.03);
    z-index: 1000;
    transition: all 0.3s ease;
}

.sidebar-header-modern {
    padding: 25px 20px;
    border-bottom: 1px solid #e0e6ed;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 15px;
}

.sidebar-logo {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
}

.brand-text h5 {
    font-weight: 800;
    color: #2c3e50;
    margin: 0;
    font-size: 1.2rem;
}

.brand-text small {
    color: #7f8c8d;
    font-size: 0.8rem;
}

.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 20px 0;
}

.nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin-bottom: 5px;
    padding: 0 15px;
}

.nav-link-modern {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #2c3e50;
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.nav-link-modern::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), rgba(52, 152, 219, 0.05));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.nav-link-modern:hover::before {
    opacity: 1;
}

.nav-link-modern:hover {
    transform: translateX(5px);
}

.nav-link-modern.active {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}

.nav-link-modern.active .nav-icon {
    color: white;
}

.nav-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(52, 152, 219, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3498db;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.nav-link-modern.active .nav-icon {
    background: rgba(255, 255, 255, 0.2);
}

.nav-text {
    flex: 1;
    margin-left: 15px;
    font-weight: 600;
    font-size: 0.95rem;
}

.nav-indicator {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.nav-link-modern.active .nav-indicator {
    opacity: 1;
}

.nav-divider {
    padding: 20px 30px 10px;
    margin-bottom: 10px;
}

.nav-divider span {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #95a5a6;
}

.sidebar-footer-modern {
    padding: 20px;
    border-top: 1px solid #e0e6ed;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.user-profile-mini {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #3498db, #2980b9);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
}

.user-info {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
}

.user-info strong {
    font-size: 0.9rem;
    color: #2c3e50;
}

.user-info small {
    font-size: 0.75rem;
    color: #7f8c8d;
}

.btn-logout-mini {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(231, 76, 60, 0.1);
    border: none;
    color: #e74c3c;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-logout-mini:hover {
    background: #e74c3c;
    color: white;
    transform: scale(1.05);
}

.sidebar-nav::-webkit-scrollbar {
    width: 6px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

@media (max-width: 992px) {
    .modern-sidebar {
        transform: translateX(-100%);
    }
    
    .modern-sidebar.show {
        transform: translateX(0);
    }
}

body.has-sidebar {
    padding-left: 280px;
}

@media (max-width: 992px) {
    body.has-sidebar {
        padding-left: 0;
    }
}
</style>

<script>
document.body.classList.add('has-sidebar');

function toggleSidebar() {
    document.querySelector('.modern-sidebar').classList.toggle('show');
}
</script>