<?php
$page_title = "Semua Aktivitas - LET Lab";
include_once 'includes/header.php';
include_once 'includes/navbar.php';

include_once 'config/database.php';
include_once 'models/Activity.php';

function getYoutubeId($url) {
    if (empty($url)) return null;

    preg_match('/(youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $url, $matches);

    return isset($matches[2]) ? $matches[2] : null;
}

$database = new Database();
$db = $database->getConnection();
$activity = new Activity($db);

$stmt = $activity->read();
$all_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- HEADER SECTION -->
<section class="bg-primary text-white text-center py-5 mb-5">
    <div class="container">
        <h1 class="fw-bold display-5">Daftar Aktivitas</h1>
        <p class="lead text-white-50">Semua dokumentasi kegiatan, penelitian, seminar, dan acara di LET Lab.</p>
    </div>
</section>

<div class="container mb-5">

    <!-- SEARCH & FILTER -->
    <div class="row mb-5 justify-content-center">
        <div class="col-md-8">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>

                <input type="text" id="searchActivity" class="form-control border-start-0 border-end-0"
                       placeholder="Cari judul aktivitas...">

                <select class="form-select border-start-0" id="filterType" style="max-width: 180px;">
                    <option value="all">Semua Tipe</option>
                    <option value="Research">Penelitian</option>
                    <option value="Conference">Konferensi</option>
                    <option value="Workshop">Workshop</option>
                    <option value="Seminar">Seminar</option>
                    <option value="Other">Lainnya</option>
                </select>
            </div>
        </div>
    </div>

    <!-- LIST AKTIVITAS -->
    <div class="row g-4" id="activityContainer">
        <?php 
        if(count($all_activities) > 0): 
            foreach($all_activities as $act):

                $videoId = getYoutubeId($act['link'] ?? '');

                // Gambar thumbnail
                $thumb = "";
                if (!empty($act['image_url'])) {
                    $thumb = htmlspecialchars($act['image_url']);
                } elseif ($videoId) {
                    $thumb = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
                }
        ?>
        <div class="col-md-4 activity-item" 
             data-type="<?php echo htmlspecialchars($act['activity_type'] ?? 'Other'); ?>">

            <div class="card h-100 shadow-sm border-0 activity-card">

                <!-- GAMBAR / VIDEO -->
                <div class="position-relative overflow-hidden">

                    <?php if($thumb): ?>
                        <img src="<?php echo $thumb; ?>" 
                             class="card-img-top activity-img" 
                             alt="<?php echo htmlspecialchars($act['title']); ?>"
                             style="height: 220px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-dark d-flex align-items-center justify-content-center text-white" style="height: 220px;">
                            <i class="fas fa-video fa-3x"></i>
                        </div>
                    <?php endif; ?>

                    <?php if($videoId): ?>
                        <a href="#" class="play-overlay" 
                           data-bs-toggle="modal" 
                           data-bs-target="#videoModalPage" 
                           data-video-id="<?php echo $videoId; ?>">
                            <i class="fas fa-play-circle fa-5x text-white opacity-75"></i>
                        </a>
                    <?php endif; ?>

                    <span class="badge bg-primary position-absolute top-0 end-0 m-3 shadow-sm">
                        <?php 
                            echo htmlspecialchars(
                                $act['activity_type'] == "Research" ? "Penelitian" : 
                                ($act['activity_type'] == "Conference" ? "Konferensi" :
                                ($act['activity_type'] == "Workshop" ? "Workshop" :
                                ($act['activity_type'] == "Seminar" ? "Seminar" : "Lainnya")))
                            );
                        ?>
                    </span>
                </div>

                <!-- BODY CARD -->
                <div class="card-body d-flex flex-column">

                    <div class="d-flex align-items-center text-muted small mb-2">
                        <i class="far fa-calendar-alt me-2"></i>
                        <?php echo date('d M Y', strtotime($act['activity_date'])); ?>
                        <span class="mx-2">•</span>
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <?php echo htmlspecialchars($act['location'] ?? 'Lokasi tidak tersedia'); ?>
                    </div>

                    <h5 class="card-title fw-bold text-dark mb-2 activity-title">
                        <?php echo htmlspecialchars($act['title']); ?>
                    </h5>
                    
                    <p class="card-text text-muted small mb-3 flex-grow-1">
                        <?php 
                        echo htmlspecialchars(substr($act['description'], 0, 120)) . "...";
                        ?>
                    </p>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-user-circle me-1"></i> 
                            <?php echo htmlspecialchars($act['username'] ?? 'Admin'); ?>
                        </small>

                        <?php if($videoId): ?>
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                           data-bs-toggle="modal" 
                           data-bs-target="#videoModalPage"
                           data-video-id="<?php echo $videoId; ?>">
                            Tonton Video
                        </button>
                        <?php endif; ?>
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
                <i class="fas fa-film fa-4x mb-3 opacity-50"></i>
                <h5>Belum ada aktivitas yang tersedia.</h5>
            </div>
        </div>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="index.php#news" class="btn btn-outline-primary px-4 rounded-pill">
                ← Kembali ke Beranda
            </a>
        </div>
    </div>
</div>

<!-- MODAL VIDEO -->
<div class="modal fade" id="videoModalPage" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-black border-0">
            <div class="modal-header border-0 position-absolute w-100" style="z-index: 1055; background: transparent;">
                <button type="button" class="btn-close btn-close-white ms-auto me-2 mt-2 bg-white opacity-75" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <iframe id="pageVideoFrame" src="" allowfullscreen allow="autoplay"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ========== FILTER & SEARCH ==========
    const searchInput = document.getElementById('searchActivity');
    const filterType = document.getElementById('filterType');
    const items = document.querySelectorAll('.activity-item');

    function filterActivities() {
        const searchValue = searchInput.value.toLowerCase();
        const typeValue = filterType.value;

        items.forEach(item => {
            const title = item.querySelector('.activity-title').textContent.toLowerCase();
            const type = item.getAttribute('data-type');

            const matchesSearch = title.includes(searchValue);
            const matchesType = (typeValue === 'all' || type === typeValue);

            if (matchesSearch && matchesType) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });
    }

    searchInput.addEventListener('keyup', filterActivities);
    filterType.addEventListener('change', filterActivities);

    // ========== YOUTUBE PLAYER ==========
    const videoModalPage = document.getElementById('videoModalPage');
    const pageVideoFrame = document.getElementById('pageVideoFrame');

    if(videoModalPage){
        videoModalPage.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const videoId = button.getAttribute('data-video-id');

            if (videoId) {
                pageVideoFrame.src = "https://www.youtube.com/embed/" + videoId + "?autoplay=1&rel=0&modestbranding=1";
            }
        });

        videoModalPage.addEventListener('hidden.bs.modal', function () {
            pageVideoFrame.src = "";
        });
    }
});
</script>

<!-- STYLE -->
<style>
.activity-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.activity-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

.activity-img {
    transition: transform 0.5s ease;
}
.activity-card:hover .activity-img {
    transform: scale(1.05);
}

.play-overlay {
    position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.3);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: 0.3s; z-index: 10;
}
.activity-card:hover .play-overlay { opacity: 1; }
</style>

<?php include_once 'includes/footer.php'; ?>
