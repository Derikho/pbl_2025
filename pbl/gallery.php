<?php
$page_title = "Galeri - LET Lab";
include_once 'includes/header.php';
include_once 'includes/navbar.php';

include_once 'config/database.php';
include_once 'models/Gallery.php';

$database = new Database();
$db = $database->getConnection();

$gallery = new Gallery($db);

// Filter kategori jika ada parameter
$selected_category = isset($_GET['category']) ? strtolower($_GET['category']) : 'all';

// Ambil semua data
$stmt = $gallery->read();
$all_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter hanya yang status = 'active'
$active_items = array_filter($all_items, function($item) {
    return ($item['status'] ?? 'active') == 'active';
});

// Filter berdasarkan kategori jika dipilih
if($selected_category !== 'all') {
    // Mapping filter bahasa Indonesia ke database (Inggris)
    $category_mapping = [
        'acara' => ['events', 'event'],
        'penelitian' => ['research'],
        'fasilitas' => ['facilities', 'facility'],
        'produk' => ['products', 'product', 'projects'],
        'tim' => ['team'],
        'dokumentasi' => ['documentation'],
        'workshop' => ['workshop'],
        'seminar' => ['seminar'],
        'rapat' => ['meeting'],
        'aktivitas' => ['activity'],
        'umum' => ['general']
    ];
    
    $selected_categories = $category_mapping[$selected_category] ?? [$selected_category];
    
    $gallery_items = array_filter($active_items, function($item) use ($selected_categories) {
        $item_category = strtolower($item['category'] ?? 'general');
        return in_array($item_category, $selected_categories);
    });
} else {
    $gallery_items = $active_items;
}

// Hitung total aktif
$total_active = count($active_items);
$displayed_count = count($gallery_items);

// Mapping kategori untuk tampilan
$category_display = [
    'general' => 'Umum',
    'events' => 'Acara',
    'event' => 'Acara',
    'research' => 'Penelitian',
    'facilities' => 'Fasilitas',
    'facility' => 'Fasilitas',
    'products' => 'Produk',
    'product' => 'Produk',
    'projects' => 'Produk',
    'team' => 'Tim',
    'documentation' => 'Dokumentasi',
    'workshop' => 'Workshop',
    'seminar' => 'Seminar',
    'meeting' => 'Rapat',
    'activity' => 'Aktivitas'
];

// Mapping untuk filter button
$filter_buttons = [
    'acara' => 'Acara',
    'penelitian' => 'Penelitian',
    'fasilitas' => 'Fasilitas',
    'produk' => 'Produk',
    'tim' => 'Tim',
    'dokumentasi' => 'Dokumentasi'
];
?>

<section class="bg-primary text-white text-center py-5 mb-5">
    <div class="container">
        <h1 class="fw-bold display-5">Galeri Kegiatan LET Lab</h1>
        <p class="lead text-white-50 mb-0">Dokumentasi aktivitas, fasilitas, dan momen terbaik di LET Lab.</p>
        
        <?php if(isset($_SESSION['loggedin']) && $_SESSION['role'] === 'admin'): ?>
        <div class="mt-3">
           
        </div>
        <?php endif; ?>
    </div>
</section>

<div class="container mb-5">
    
    <div class="row mb-4">
        <div class="col-12 text-center">
            <div class="btn-group flex-wrap" role="group" aria-label="Filter Galeri">
                <button type="button" class="btn btn-outline-primary <?php echo $selected_category === 'all' ? 'active' : ''; ?> filter-btn rounded-pill m-1 px-4" 
                        data-filter="all" onclick="filterGallery('all')">
                    Semua
                </button>
                
                <?php foreach($filter_buttons as $filter_value => $display_name): ?>
                <button type="button" class="btn btn-outline-primary <?php echo $selected_category === $filter_value ? 'active' : ''; ?> filter-btn rounded-pill m-1 px-4" 
                        data-filter="<?php echo $filter_value; ?>" onclick="filterGallery('<?php echo $filter_value; ?>')">
                    <?php echo $display_name; ?>
                </button>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Menampilkan <?php echo $displayed_count; ?> dari <?php echo $total_active; ?> item galeri
                </small>
            </div>
        </div>
    </div>

    <div class="row g-4" id="galleryContainer">
        <?php 
        if($displayed_count > 0): 
            foreach($gallery_items as $item):
                // Validasi URL gambar
                $image_url = $item['image_url'] ?? '';
                $image_error = false;
                
                if(!empty($image_url)) {
                    // Cek jika URL valid
                    if(!filter_var($image_url, FILTER_VALIDATE_URL)) {
                        // Cek jika path lokal
                        $local_path = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($image_url, '/');
                        if(!file_exists($local_path)) {
                            $image_error = true;
                            $image_url = 'https://via.placeholder.com/600x400/e9ecef/6c757d?text=Gambar+Tidak+Tersedia';
                        }
                    }
                } else {
                    $image_error = true;
                    $image_url = 'https://via.placeholder.com/600x400/dee2e6/6c757d?text=Tidak+Ada+Gambar';
                }
                
                // Tentukan kategori untuk filter
                $category_db = strtolower($item['category'] ?? 'general');
                
                // Untuk display
                $category_display_text = $category_display[$category_db] ?? ucfirst($category_db);
                $description = htmlspecialchars($item['description'] ?? '');
                $short_description = !empty($description) ? (strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description) :
                
                // Tentukan filter category untuk data attribute
                $filter_category = '';
                foreach($filter_buttons as $filter_key => $filter_name) {
                    if($category_db === $filter_key || 
                       ($filter_key === 'acara' && in_array($category_db, ['events', 'event'])) ||
                       ($filter_key === 'penelitian' && $category_db === 'research') ||
                       ($filter_key === 'fasilitas' && in_array($category_db, ['facilities', 'facility'])) ||
                       ($filter_key === 'produk' && in_array($category_db, ['products', 'product', 'projects'])) ||
                       ($filter_key === 'tim' && $category_db === 'team') ||
                       ($filter_key === 'dokumentasi' && $category_db === 'documentation')) {
                        $filter_category = $filter_key;
                        break;
                    }
                }
        ?>
        <div class="col-md-6 col-lg-4 gallery-item" data-category="<?php echo $filter_category; ?>">
            <div class="card h-100 border-0 shadow-sm gallery-card">
                <div class="overflow-hidden position-relative" style="height: 250px;">
                    <img src="<?php echo htmlspecialchars($image_url); ?>" 
                         class="card-img-top gallery-img h-100 w-100" 
                         alt="<?php echo htmlspecialchars($item['title']); ?>"
                         style="object-fit: cover;"
                         loading="lazy"
                         onclick="showImageModal('<?php echo htmlspecialchars($item['image_url']); ?>', '<?php echo htmlspecialchars($item['title']); ?>', '<?php echo addslashes($item['description'] ?? ''); ?>')">
                    
                    <div class="gallery-overlay cursor-pointer" 
                         onclick="showImageModal('<?php echo htmlspecialchars($item['image_url']); ?>', '<?php echo htmlspecialchars($item['title']); ?>', '<?php echo addslashes($item['description'] ?? ''); ?>')">
                        <i class="fas fa-search-plus text-white fa-2x"></i>
                    </div>

                    <span class="badge bg-primary position-absolute top-0 start-0 m-3 shadow-sm text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">
                        <?php echo $category_display_text; ?>
                    </span>
                    
                    <?php if($image_error): ?>
                    <div class="position-absolute bottom-0 start-0 w-100 bg-warning bg-opacity-75 text-dark p-1 text-center">
                        <small><i class="fas fa-exclamation-triangle me-1"></i> Gambar mungkin tidak tersedia</small>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($item['title']); ?></h5>
                    
                    <div class="flex-grow-1">
                        <p class="card-text text-muted small mb-3">
                            <?php echo $short_description; ?>
                        </p>
                    </div>
                    
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <small class="text-muted">
                                <i class="far fa-calendar-alt me-1"></i> 
                                <?php 
                                if(isset($item['created_at']) && !empty($item['created_at'])) {
                                    echo date('d M Y', strtotime($item['created_at']));
                                } else {
                                    echo 'Tanggal tidak tersedia';
                                }
                                ?>
                            </small>
                            <small class="text-primary cursor-pointer" 
                                   onclick="showImageModal('<?php echo htmlspecialchars($item['image_url']); ?>', '<?php echo htmlspecialchars($item['title']); ?>', '<?php echo addslashes($item['description'] ?? ''); ?>')">
                                 
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            endforeach; 
        else:
        ?>
        <div class="col-12 text-center py-5">
            <div class="text-muted">
                <i class="fas fa-images fa-4x mb-3 opacity-50"></i>
                <h5 class="mb-3"><?php echo $selected_category !== 'all' ? 'Tidak ada galeri di kategori ini' : 'Belum ada galeri yang diunggah'; ?></h5>
                <p class="mb-4">
                    <?php if($selected_category !== 'all'): ?>
                    Tidak ditemukan galeri dengan kategori "<?php echo ucfirst($selected_category); ?>".
                    <?php else: ?>
                    Silakan kembali lagi nanti untuk melihat dokumentasi kegiatan kami.
                    <?php endif; ?>
                </p>
                
                <?php if(isset($_SESSION['loggedin']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin_gallery.php?action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Tambah Galeri
                </a>
                <?php endif; ?>
                
                <a href="index.php" class="btn btn-outline-primary ms-2">
                    <i class="fas fa-home me-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if($displayed_count > 0): ?>
    <div class="text-center mt-5">
        <div class="d-flex justify-content-center gap-3">
            <a href="index.php" class="btn btn-outline-primary px-4 rounded-pill">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
            </a>
            
            <?php if(isset($_SESSION['loggedin']) && $_SESSION['role'] === 'admin'): ?>
            
            <?php endif; ?>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                <i class="fas fa-camera me-1"></i>
                Total <?php echo $total_active; ?> foto dokumentasi LET Lab
            </small>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Image Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-header border-0 p-0 position-absolute end-0 top-0 m-2" style="z-index: 1055;">
                <button type="button" class="btn-close btn-close-white bg-dark bg-opacity-50 rounded-circle p-2" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-0 text-center position-relative">
                <div class="bg-dark bg-opacity-10 rounded-lg p-1">
                    <img src="" id="modalImage" class="img-fluid rounded" style="max-height: 70vh; object-fit: contain;">
                </div>
                <div class="bg-white p-4 rounded-bottom shadow-sm">
                    <h4 id="modalTitle" class="fw-bold text-dark mb-2"></h4>
                    <p id="modalDesc" class="text-muted mb-3"></p>
                    <div class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Filter function dengan URL parameter
function filterGallery(category) {
    if(category === 'all') {
        window.location.href = 'gallery.php';
    } else {
        window.location.href = 'gallery.php?category=' + category;
    }
}

// Set active filter button berdasarkan URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = urlParams.get('category') || 'all';
    
    // Update button states
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        const btnCategory = btn.getAttribute('data-filter');
        if(btnCategory === categoryParam) {
            btn.classList.add('active', 'bg-primary', 'text-white');
            btn.classList.remove('btn-outline-primary');
        } else {
            btn.classList.remove('active', 'bg-primary', 'text-white');
            btn.classList.add('btn-outline-primary');
        }
    });
    
    // Filter items secara client-side sebagai fallback
    const galleryItems = document.querySelectorAll('.gallery-item');
    if(categoryParam !== 'all') {
        galleryItems.forEach(item => {
            const itemCategory = item.getAttribute('data-category');
            if(itemCategory !== categoryParam) {
                item.style.display = 'none';
            }
        });
    }
});

// Image Modal
var galleryModal = new bootstrap.Modal(document.getElementById('galleryModal'));

function showImageModal(src, title, desc) {
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.getElementById('modalTitle');
    const modalDesc = document.getElementById('modalDesc');
    
    // Set fallback image jika error
    modalImage.onerror = function() {
        this.src = 'https://via.placeholder.com/800x600/e9ecef/6c757d?text=Gambar+Tidak+Dapat+Dimuat';
        this.alt = 'Gambar tidak dapat dimuat';
    };
    
    modalImage.src = src;
    modalImage.alt = title;
    modalTitle.textContent = title;
    modalDesc.textContent = desc || 'Tidak ada deskripsi';
    
    galleryModal.show();
}

// Keyboard navigation untuk modal
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('galleryModal');
    if(modal.classList.contains('show')) {
        if(e.key === 'Escape') {
            galleryModal.hide();
        }
    }
});
</script>

<style>
.gallery-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
    border: 1px solid rgba(0,0,0,0.08);
}

.gallery-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.12) !important;
    border-color: #0d6efd;
}

.gallery-img {
    transition: transform 0.6s ease;
    object-fit: cover;
}

.gallery-card:hover .gallery-img {
    transform: scale(1.08);
}

.gallery-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.4));
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: var(--bs-border-radius) var(--bs-border-radius) 0 0;
}

.gallery-card:hover .gallery-overlay {
    opacity: 1;
}

.cursor-pointer {
    cursor: pointer;
}

/* Animation for filter */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.gallery-item {
    animation: fadeIn 0.5s ease forwards;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .gallery-card {
        margin-bottom: 1.5rem;
    }
    
    .btn-group {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-group .btn {
        width: 200px;
        margin-bottom: 0.5rem;
    }
}

/* Modal improvements */
#galleryModal .modal-content {
    border: none;
    background: transparent;
}

#galleryModal .btn-close {
    opacity: 0.8;
    transition: opacity 0.2s ease;
}

#galleryModal .btn-close:hover {
    opacity: 1;
}
</style>

<?php include_once 'includes/footer.php'; ?>