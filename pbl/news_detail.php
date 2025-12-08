<?php
$page_title = "Semua Berita - LET Lab";

include_once 'includes/header.php';
include_once 'includes/navbar.php';

include_once 'config/database.php';
include_once 'models/News.php';

$database = new Database();
$db = $database->getConnection();

$news = new News($db);
$all_news = $news->read();
?>

<section class="bg-primary text-white text-center py-5 mb-5">
    <div class="container">
        <h1 class="fw-bold display-5">Semua Berita & Artikel</h1>
        <p class="lead text-white-50">Informasi terbaru dari LET Lab</p>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">

        <div class="row">

            <?php
            $hasData = false;
            while ($article = $all_news->fetch(PDO::FETCH_ASSOC)):

                if ($article['status'] !== 'published') continue;
                $hasData = true;
            ?>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm">

                    <?php if (!empty($article['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($article['image_url']); ?>"
                             class="card-img-top"
                             style="height:200px; object-fit:cover"
                             alt="<?php echo htmlspecialchars($article['title']); ?>">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height:200px">
                            <i class="fas fa-newspaper fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>

                    <div class="card-body d-flex flex-column">

                        <span class="badge bg-primary mb-2 align-self-start">
                            <?php echo htmlspecialchars($article['category']); ?>
                        </span>

                        <h5 class="fw-bold mb-2">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </h5>

                        <small class="text-muted mb-2">
                            <i class="far fa-calendar"></i>
                            <?php echo date('d M Y', strtotime($article['publish_date'])); ?>
                        </small>

                        <p class="text-muted small flex-grow-1">
                            <?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 120)); ?>...
                        </p>

                        <button class="btn btn-outline-primary btn-sm mt-auto"
                                data-bs-toggle="modal"
                                data-bs-target="#newsModal<?php echo $article['id']; ?>">
                            Baca Selengkapnya
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODAL BERITA DETAIL -->
            <div class="modal fade" id="newsModal<?php echo $article['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <?php if (!empty($article['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($article['image_url']); ?>"
                                     class="img-fluid rounded mb-3"
                                     alt="<?php echo htmlspecialchars($article['title']); ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars($article['category']); ?>
                                </span>
                                <small class="text-muted ms-2">
                                    <i class="far fa-calendar"></i>
                                    <?php echo date('d M Y', strtotime($article['publish_date'])); ?>
                                </small>
                            </div>

                            <p style="white-space: pre-line;">
                                <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                            </p>

                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">
                                Tutup
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <?php endwhile; ?>

            <?php if(!$hasData): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada berita yang dipublikasikan</p>
                </div>
            <?php endif; ?>

        </div>

        <div class="text-center mt-5">
            <a href="index.php#news" class="btn btn-outline-primary px-4 rounded-pill">
                ← Kembali ke Beranda
            </a>
        </div>

    </div>
</section>

<?php include_once 'includes/footer.php'; ?>
