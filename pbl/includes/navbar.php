<style>
    /* --- VARIABLES --- */
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --accent-color: #e74c3c;
    --text-dark: #2d3436;
    --text-muted: #636e72;
    --bg-light: #f8f9fa;
}


body {
    padding-top: 85px; 
}

.navbar-modern {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
    padding: 15px 0;
    transition: all 0.3s ease;
    z-index: 1040; 
}

.navbar-modern .navbar-brand {
    font-weight: 800;
    color: var(--primary-color);
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.navbar-modern .nav-link {
    font-weight: 600;
    color: var(--text-dark) !important;
    margin: 0 5px;
    padding: 8px 15px !important;
    border-radius: 25px;
    transition: all 0.3s ease;
    position: relative;
}

.navbar-modern .nav-link:hover,
.navbar-modern .nav-link.active {
    color: var(--secondary-color) !important;
    background: rgba(52, 152, 219, 0.1);
}

.brand-logo-img {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    object-fit: cover;
    box-shadow: 0 4px 10px rgba(52, 152, 219, 0.2);
}

.profile-dropdown {
    position: relative;
}

.profile-trigger {
    background: transparent;
    border: none;
    padding: 0;
    cursor: pointer;
    transition: transform 0.2s;
}

.profile-trigger:hover {
    transform: scale(1.05);
}

.profile-trigger:focus {
    outline: none;
}

.profile-menu {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    min-width: 280px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1050;
}

.profile-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.profile-header {
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.profile-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--secondary-color), #5dade2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.profile-info h6 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-dark);
}

.profile-info span {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.profile-links {
    padding: 10px;
}

.profile-links a,
.profile-links .dropdown-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    color: var(--text-dark);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s;
    font-size: 0.95rem;
    border: none;
    background: transparent;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.profile-links a:hover,
.profile-links .dropdown-btn:hover {
    background: rgba(52, 152, 219, 0.1);
    color: var(--secondary-color);
}

.profile-links a.text-danger:hover {
    background: rgba(231, 76, 60, 0.1);
    color: var(--accent-color);
}

.profile-links hr {
    margin: 10px 0;
    opacity: 0.1;
}

@media (max-width: 991px) {
    .profile-menu {
        right: -15px;
        min-width: 260px;
    }
    
    body {
        padding-top: 75px;
    }
}
</style>

<nav class="navbar navbar-expand-lg navbar-modern fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="assets/img/logo_let.png" alt="Logo" class="brand-logo-img me-2" onerror="this.style.display='none'">
            <div class="d-flex flex-column">
                <span style="line-height: 1;">LET Lab</span>
                <small style="font-size: 0.65rem; font-weight: 400; color: #666;">Politeknik Negeri Malang</small>
            </div>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#activities">Activities</a></li>
                <li class="nav-item"><a class="nav-link" href="visitor_booking.php">Booking</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#store">Store</a></li>
                <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
            </ul>

            <div class="navbar-actions ms-lg-3">
                <?php if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    
                    <div class="profile-dropdown">
                        <div class="profile-trigger" onclick="toggleProfileMenu()" id="profileTrigger">
                            <i class="fas fa-user text-white"></i>
                        </div>

                        <div class="profile-menu" id="profileMenu">
                            <div class="profile-header">
                                <div class="profile-avatar">
                                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                                </div>
                                <div class="profile-info">
                                    <h6><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></h6>
                                    <span class="badge bg-light text-dark border mt-1">
                                        <?php echo ucfirst($_SESSION['role'] ?? 'User'); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="profile-links">
                                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                    <a href="admin_dashboard.php">
                                        <i class="fas fa-tachometer-alt text-primary"></i> Admin Dashboard
                                    </a>
                                <?php endif; ?>

                                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'member'): ?>
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#attendanceModal" onclick="toggleProfileMenu()">
                                        <i class="fas fa-camera text-success"></i> Presensi & Upload
                                    </button>
                                    <a href="#">
                                        <i class="fas fa-history text-info"></i> Riwayat Peminjaman
                                    </a>
                                <?php endif; ?>
                                
                                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'dosen'): ?>
                                    <a href="#">
                                        <i class="fas fa-user-tie text-warning"></i> Profil Dosen
                                    </a>
                                <?php endif; ?>

                                <hr class="my-2">
                                <a href="#" class="text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt"></i> Sign Out
                                </a>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <a href="login.php" class="nav-link btn-nav-login">
                        Login <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'member'): ?>
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-camera me-2"></i>Presensi Harian</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="process_attendance.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark"><?php echo date('l, d F Y'); ?></h4>
                        <p class="text-muted small">Wajib upload foto bukti kehadiran di Laboratorium.</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-bold mb-2 small text-uppercase text-secondary">Bukti Foto</label>
                        <input type="file" class="form-control" name="photo_proof" accept="image/*" required>
                        <small class="text-muted" style="font-size: 0.75rem;">Format: JPG, PNG. Max 2MB.</small>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold mb-2 small text-uppercase text-secondary">Lokasi / Kegiatan</label>
                        <textarea class="form-control bg-light" name="location_note" rows="2" placeholder="Contoh: Sedang praktikum di Lab Jaringan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Kirim Presensi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    function toggleProfileMenu() {
        const menu = document.getElementById('profileMenu');
        menu.classList.toggle('active');
    }

    // Tutup dropdown jika klik di luar area
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('profileMenu');
        const trigger = document.getElementById('profileTrigger');
        
        if (menu && trigger && !menu.contains(event.target) && !trigger.contains(event.target)) {
            menu.classList.remove('active');
        }
    });

    // Navbar Scroll Effect (Agar berubah saat discroll)
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar-modern');
        if (window.scrollY > 50) {
            navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.08)';
            navbar.style.padding = '10px 0';
        } else {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            navbar.style.boxShadow = '0 4px 30px rgba(0,0,0,0.04)';
            navbar.style.padding = '12px 0';
        }
    });
</script>