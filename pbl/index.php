<?php
$page_title = "LET Lab - Home";
include_once 'includes/header.php';
include_once 'includes/navbar.php';

include_once 'config/database.php';
include_once 'models/News.php';
include_once 'models/Partner.php';

include_once 'models/Products.php'; 

$database = new Database();
$db = $database->getConnection();

$news = new News($db);
$recent_news = $news->read();

$product = new Product($db);
$products = $product->read();

$partner = new Partner($db);
$partners = $partner->read();
?>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Learning Engineering Technology Laboratory</h1>
        <p class="lead">Berdasarkan inovasi dan riset dalam rekayasa pembelajaran untuk membangun ekosistem pendidikan berkualitas.</p>
        <a href="#about" class="btn btn-primary btn-lg mt-3">Pelajari Lebih Lanjut</a>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <h2 class="mb-4">Tentang Kami</h2>
                <p class="lead">Learning Engineering Technology Laboratory adalah pusat inovasi dan riset dalam teknologi pembelajaran yang berkomitmen untuk meningkatkan kualitas pendidikan melalui pemanfaatan berbagai data dari teknologi.</p>
                <p>Kami membangun sistem pendukung lengkap berdasarkan perilaku belajar siswa. Pendekatan komprehensif kami mencakup aplikasi pembelajaran, analitik, integrasi AI, sistem dukungan adaptif, gamifikasi, dan pemantauan manajemen.</p>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" class="img-fluid rounded" alt="Team Collaboration">
            </div>
        </div>
    </div>
</section>

<section id="research" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Penelitian Kami</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Smart Monitoring in Learning (SMILE)</h5>
                        <p class="card-text">Sistem pemantauan cerdas untuk memantau dan menganalisis proses pembelajaran siswa secara real-time.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Smart Adaptive Technology in Education (SArt)</h5>
                        <p class="card-text">Teknologi adaptif yang menyesuaikan konten pembelajaran berdasarkan kemampuan dan kebutuhan individu siswa.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Gamification</h5>
                        <p class="card-text">Penerapan elemen permainan dalam pembelajaran untuk meningkatkan motivasi dan keterlibatan siswa.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Partners Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Partner Kami</h2>
        <div class="row justify-content-center">
            <?php 
            $count = 0;
            while($row = $partners->fetch(PDO::FETCH_ASSOC)): 
                if($row['status'] == 'active' && $count < 6):
                    $count++;
            ?>
            <div class="col-6 col-md-4 col-lg-2 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center justify-content-center p-3">
                        <?php if(!empty($row['logo'])): ?>
                            <img src="<?php echo htmlspecialchars($row['logo']); ?>" 
                                 alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                 class="img-fluid" 
                                 style="max-height: 60px; object-fit: contain;">
                        <?php else: ?>
                            <div class="text-center">
                                <i class="fas fa-handshake fa-2x text-muted mb-2"></i>
                                <small class="d-block"><?php echo htmlspecialchars($row['name']); ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php 
                endif;
            endwhile; 
            ?>
        </div>
    </div>
</section>

<!-- News Section (Berita/Artikel Terbaru) -->
<section id="news" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Berita & Artikel Terbaru</h2>
        <div class="row">
            <?php 
            $count = 0;
            while($article = $recent_news->fetch(PDO::FETCH_ASSOC)): 
                if($article['status'] == 'published' && $count < 3):
                    $count++;
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if(!empty($article['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($article['image_url']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($article['title']); ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-newspaper fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($article['category']); ?></span>
                        <h5 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h5>
                        <p class="card-text text-muted small">
                            <?php echo htmlspecialchars(substr(strip_tags($article['content']), 0, 100)); ?>...
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="far fa-calendar"></i> 
                                <?php echo date('d M Y', strtotime($article['publish_date'])); ?>
                            </small>
                            <a href="news_detail.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-outline-primary">
                                Baca Selengkapnya
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                endif;
            endwhile; 
            ?>
        </div>
        <div class="text-center mt-4">
            <a href="news.php" class="btn btn-primary">Lihat Semua Berita</a>
        </div>
    </div>
</section>

<!-- Products Section -->
<section id="store" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Produk & Aplikasi Pembelajaran</h2>
        <div class="row">
            <?php 
            $count = 0;
            while($prod = $products->fetch(PDO::FETCH_ASSOC)): 
                if($prod['status'] == 'active' && $count < 4):
                    $count++;
            ?>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if(!empty($prod['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($prod['name']); ?>"
                             style="height: 180px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                            <i class="fas fa-box fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="badge bg-info text-dark mb-2"><?php echo htmlspecialchars($prod['category']); ?></span>
                        <h5 class="card-title"><?php echo htmlspecialchars($prod['name']); ?></h5>
                        <p class="card-text text-muted small">
                            <?php echo htmlspecialchars(substr($prod['description'], 0, 80)); ?>...
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <?php if($prod['price'] > 0): ?>
                                <strong class="text-primary">$<?php echo number_format($prod['price'], 2); ?></strong>
                            <?php else: ?>
                                <span class="badge bg-success">Gratis</span>
                            <?php endif; ?>
                            <a href="product_detail.php?id=<?php echo $prod['id']; ?>" class="btn btn-sm btn-primary">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
                endif;
            endwhile; 
            ?>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section id="gallery" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Galeri Kami</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="https://images.unsplash.com/photo-1523580494863-6f3031224c94?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" class="card-img-top" alt="Conference" style="height: 250px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">ICCE 2024</h5>
                        <p class="card-text">International Conference on Computers in Education</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" class="card-img-top" alt="Research" style="height: 250px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">Penelitian Lapangan</h5>
                        <p class="card-text">Aktivitas penelitian di lingkungan pendidikan nyata</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="https://images.unsplash.com/photo-1553877522-43269d4ea984?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" class="card-img-top" alt="Workshop" style="height: 250px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title">Workshop Pembelajaran</h5>
                        <p class="card-text">Sesi workshop untuk guru dan pendidik</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="gallery.php" class="btn btn-primary">Lihat Semua Galeri</a>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h2 class="text-center mb-5">Hubungi Kami</h2>
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-map-marker-alt fa-2x mb-3 text-primary"></i>
                                <h5>Alamat</h5>
                                <p>Politeknik Negeri Malang<br>Jl. Soekarno Hatta No.9, Malang</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-phone fa-2x mb-3 text-primary"></i>
                                <h5>Telepon</h5>
                                <p>(0341) 404424</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope fa-2x mb-3 text-primary"></i>
                                <h5>Email</h5>
                                <p>let@polinema.ac.id</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-globe fa-2x mb-3 text-primary"></i>
                                <h5>Website</h5>
                                <p>www.letlab.polinema.ac.id</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once 'includes/footer.php'; ?>