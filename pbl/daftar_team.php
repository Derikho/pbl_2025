<?php
$page_title = "Tim Kami - LET Lab";
include_once 'includes/header.php';
include_once 'includes/navbar.php';

include_once 'config/database.php';
include_once 'models/Team.php';

$database = new Database();
$db = $database->getConnection();
$team = new Team($db);

$stmt = $team->read();
$all_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="bg-primary text-white text-center py-5">
    <div class="container">
        <h1 class="fw-bold">Tim Kami</h1>
        <p class="lead">Para ahli, peneliti, dan pengajar yang berdedikasi.</p>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        
        <div class="row justify-content-center mb-5">
            <div class="col-md-6">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchTeam" class="form-control border-start-0" placeholder="Cari nama dosen...">
                </div>
            </div>
        </div>

        <div class="row" id="teamContainer">
            <?php foreach($all_members as $member): 
                if($member['status'] !== 'active') continue;
                
                $social = !empty($member['social_links']) ? json_decode($member['social_links'], true) : [];
                $details = !empty($member['profile_details']) ? json_decode($member['profile_details'], true) : [];
                $safe_details = htmlspecialchars(json_encode($details), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="col-md-6 col-lg-4 mb-4 team-item">
                <div class="card h-100 shadow-sm border-0 team-card">
                    <div class="card-body text-center p-4 d-flex flex-column">
                        
                        <div class="mb-3">
                            <?php if(!empty($member['photo'])): ?>
                                <img src="<?php echo htmlspecialchars($member['photo']); ?>" alt="Foto" class="profile-img">
                            <?php else: ?>
                                <div class="profile-img-placeholder mx-auto">
                                    <i class="fas fa-user fa-3x text-secondary"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h5 class="fw-bold text-dark mb-1 name-text"><?php echo htmlspecialchars($member['name']); ?></h5>
                        <p class="text-primary small fw-bold mb-3"><?php echo htmlspecialchars($member['position']); ?></p>
                        
                        <div class="mb-1">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4"
                                    onclick='showProfileModal(<?php echo json_encode($member["name"]); ?>, <?php echo json_encode($member["photo"]); ?>, <?php echo json_encode($member["position"]); ?>, <?php echo $safe_details; ?>)'>
                                <i class="fas fa-id-card me-1"></i> Lihat Detail Profil
                            </button>
                        </div>

                        <div class="d-flex justify-content-center my-3">
                            <div style="width: 40px; height: 3px; background-color: #0d6efd; border-radius: 2px;"></div>
                        </div>

                        <div class="d-flex justify-content-center flex-wrap gap-2 mb-2">
                            <?php if(!empty($member['email'])): ?>
                                <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" class="btn btn-sm btn-outline-primary" title="Email" target="_blank">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if(!empty($member['phone'])): ?>
                                <a href="https://wa.me/<?php echo htmlspecialchars($member['phone']); ?>" class="btn btn-sm btn-outline-success" title="WhatsApp" target="_blank">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if(!empty($social['linkedin'])): ?>
                                <a href="<?php echo $social['linkedin']; ?>" class="btn btn-sm btn-outline-primary" title="LinkedIn" target="_blank">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            <?php endif; ?>

                            <?php if(!empty($social['instagram'])): ?>
                                <a href="<?php echo $social['instagram']; ?>" class="btn-social btn-instagram" title="Instagram" target="_blank">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            <?php endif; ?>

                            <?php if(!empty($social['website'])): ?>
                                <a href="<?php echo $social['website']; ?>" class="btn btn-sm btn-outline-primary" title="Instagram" target="_blank">
                                    <i class="fab fa-globe"></i>
                                </a>
                            <?php endif; ?>

                            <?php if(!empty($social['twitter'])): ?>
                                <a href="<?php echo $social['twitter']; ?>" class="btn btn-sm btn-outline-info" title="twitter" target="_blank">
                                    <i class="fab fa-twitter-in"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-center flex-wrap gap-2 mt-1">
                            <?php if(!empty($social['google_scholar'])): ?>
                                <a href="<?php echo $social['google_scholar']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                    <i class="fas fa-book"></i> Scholar
                                </a>
                            <?php endif; ?>

                            <?php if(!empty($social['researchgate'])): ?>
                                <a href="<?php echo $social['researchgate']; ?>" class="btn btn-sm btn-success" target="_blank">
                                    <i class="fab fa-researchgate"></i> RG
                                </a>
                            <?php endif; ?>

                            <?php if(!empty($social['orcid'])): ?>
                                <a href="<?php echo $social['orcid']; ?>" class="btn btn-sm btn-info text-white" target="_blank">
                                    <i class="fab fa-id-card"></i> ORCID
                                </a>
                            <?php endif; ?>

                            <?php if(!empty($social['scopus'])): ?>
                                <a href="<?php echo $social['scopus']; ?>" class="btn btn-sm btn-danger" target="_blank">
                                    <i class="fas fa-flask"></i> Scopus
                                </a>
                            <?php endif; ?>

                            <?php if(!empty($social['sinta'])): ?>
                                <a href="<?php echo $social['sinta']; ?>" class="btn btn-sm btn-warning text-white" target="_blank">
                                    <i class="fas fa-graduation-cap"></i> <small>SINTA</small>
                                </a>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
                <div class="text-center mt-5">
            <a href="index.php#news" class="btn btn-outline-primary px-4 rounded-pill">
                ← Kembali ke Beranda
            </a>
        </div>
    </div>
</section>

<div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-tie me-2"></i>Detail Profil Dosen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <img id="modalPhoto" src="" class="rounded-circle border border-3 border-white shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                    <h5 class="fw-bold mt-3 mb-1" id="modalName"></h5>
                    <span class="badge bg-light text-primary border" id="modalPosition"></span>
                </div>
                
                <div class="bg-light p-3 rounded mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="text-primary me-3" style="width: 20px;"><i class="fas fa-id-badge"></i></div>
                        <div><small class="text-muted d-block">NIP</small><span class="fw-bold text-dark" id="modalNIP">-</span></div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <div class="text-primary me-3" style="width: 20px;"><i class="fas fa-id-card"></i></div>
                        <div><small class="text-muted d-block">NIDN</small><span class="fw-bold text-dark" id="modalNIDN">-</span></div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="text-primary me-3" style="width: 20px;"><i class="fas fa-university"></i></div>
                        <div><small class="text-muted d-block">Program Studi</small><span class="fw-bold text-dark" id="modalProdi">-</span></div>
                    </div>
                </div>

                <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">Riwayat Pendidikan</h6>
                <ul class="list-unstyled small" id="modalEducation"></ul>

                <h6 class="fw-bold border-bottom pb-2 mb-3 mt-4 text-primary">Sertifikasi</h6>
                <ul class="list-unstyled small" id="modalCertifications"></ul>
            </div>
        </div>
    </div>
</div>

<script>
function showProfileModal(name, photo, position, details) {
    document.getElementById('modalName').textContent = name;
    document.getElementById('modalPosition').textContent = position;
    document.getElementById('modalPhoto').src = photo ? photo : 'assets/img/default-user.png';
    document.getElementById('modalNIP').textContent = (details && details.nip) ? details.nip : '-';
    document.getElementById('modalNIDN').textContent = (details && details.nidn) ? details.nidn : '-';
    document.getElementById('modalProdi').textContent = (details && details.prodi) ? details.prodi : '-';

    var eduList = document.getElementById('modalEducation');
    eduList.innerHTML = ''; 
    if (details && details.education && details.education.length > 0) {
        details.education.forEach(function(edu) {
            var li = document.createElement('li');
            li.className = 'mb-2 d-flex align-items-start';
            li.innerHTML = '<i class="fas fa-graduation-cap text-success me-2 mt-1"></i> ' + edu;
            eduList.appendChild(li);
        });
    } else {
        eduList.innerHTML = '<li class="text-muted fst-italic">Data pendidikan belum tersedia.</li>';
    }

    var certList = document.getElementById('modalCertifications');
    certList.innerHTML = ''; 
    if (details && details.certifications && details.certifications.length > 0) {
        details.certifications.forEach(function(cert) {
            var li = document.createElement('li');
            li.className = 'mb-2 d-flex align-items-start';
            li.innerHTML = '<i class="fas fa-certificate text-warning me-2 mt-1"></i> ' + cert;
            certList.appendChild(li);
        });
    } else {
        certList.innerHTML = '<li class="text-muted fst-italic">Belum ada data sertifikasi.</li>';
    }

    var myModal = new bootstrap.Modal(document.getElementById('profileModal'));
    myModal.show();
}

document.getElementById('searchTeam').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let items = document.querySelectorAll('.team-item');
    items.forEach(function(item) {
        let name = item.querySelector('.name-text').textContent.toLowerCase();
        if (name.includes(filter)) { item.style.display = ''; } else { item.style.display = 'none'; }
    });
});
</script>

<style>
.team-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
    border-radius: 12px;
}
.team-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
}
.profile-img {
    width: 110px; height: 110px; border-radius: 50%; object-fit: cover;
    border: 3px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.profile-img-placeholder {
    width: 110px; height: 110px; border-radius: 50%; background: #f8f9fa;
    display: flex; align-items: center; justify-content: center;
    border: 3px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.btn-social {
    width: 40px; height: 40px; border-radius: 8px; 
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.2rem; transition: transform 0.2s; text-decoration: none;
}
.btn-social:hover { transform: translateY(-3px); color: #fff; opacity: 0.9; }

.btn-email { background: #ea4335; }
.btn-whatsapp { background: #25D366; }
.btn-linkedin { background: #0077b5; }
.btn-instagram { background: #C13584; }

.btn-academic {
    padding: 6px 12px; border-radius: 6px; color: #fff; font-size: 0.85rem; font-weight: 600;
    text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
    transition: transform 0.2s;
}
.btn-academic:hover { transform: translateY(-3px); color: #fff; opacity: 0.9; }

.btn-scholar { background: #4285F4; }
.btn-rg { background: #00CCBB; }
.btn-orcid { background: #A6CE39; }
.btn-scopus { background: #E9711C; }
.btn-sinta { background: #FFC107; color: #333; }
.btn-sinta:hover { color: #333; }
</style>

<?php include_once 'includes/footer.php'; ?>