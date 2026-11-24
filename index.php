<?php $title='Home'; include 'includes/header.php';
$svc=db()->query('SELECT * FROM services WHERE active=1 ORDER BY sort_order LIMIT 6')->fetchAll();
$jobs=db()->query("SELECT * FROM jobs WHERE status='published' ORDER BY id DESC LIMIT 4")->fetchAll(); 
$testimonials=db()->query('SELECT * FROM testimonials WHERE active=1 ORDER BY id DESC')->fetchAll();
$latest_blogs=db()->query("SELECT * FROM blogs WHERE status='published' ORDER BY id DESC LIMIT 3")->fetchAll(); ?>

<!-- Hero Section -->
<section class="hero"><div class="wrap">
<h1>Connect. Hire. <span>Grow.</span></h1>
<p>ADISOMA Solution is your premier talent partner for a brighter tomorrow, matching skilled people with growing businesses through modern recruitment strategies.</p>
<div class="btn-group">
    <a class="btn" href="jobs.php">Find a Job</a>
    <a class="btn alt" href="employers.php">Hire Talent</a>
</div>
</div></section>

<!-- About / Intro Section -->
<section class="s"><div class="wrap">
<div class="about-grid">
    <div class="about-content">
        <span class="process-subtitle">ABOUT ADISOMA SOLUTIONS</span>
        <h2 style="color:var(--n); font-size:34px; margin-top:10px; margin-bottom:20px; font-weight:700; line-height:1.3;">Reliable & Cost Efficient<br>Recruitment Services</h2>
        <p style="font-size:16px; color:var(--g); line-height:1.8; margin-bottom:25px;">ADISOMA Solutions Pvt. Ltd. is your trusted partner in comprehensive HR Consulting, IT Recruitment & Staffing Solutions. With an unwavering commitment to excellence, we have been serving businesses across industries with top-notch IT & NON IT recruitment services.</p>
        
        <div class="about-features">
            <div class="about-feat">
                <div style="font-size:28px; color:var(--o);">📋</div>
                <div style="font-weight:600; color:var(--n); line-height:1.3;">Understand Your<br>Needs</div>
            </div>
            <div class="about-feat">
                <div style="font-size:28px; color:var(--o);">🎯</div>
                <div style="font-weight:600; color:var(--n); line-height:1.3;">Find the Perfect<br>Candidate</div>
            </div>
        </div>
        <a class="btn" href="about.php">Learn More ➔</a>
    </div>
    <div class="about-img-box">
        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=600&q=80" class="about-img" alt="Team" loading="lazy">
    </div>
</div>
</div></section>

<!-- Services Section -->
<section class="s bg"><div class="wrap"><h2 class="t">Our Services</h2><div class="grid3">
<?php foreach($svc as $s): 
    $s_url = BASE_URL . '/service-detail.php?' . (!empty($s['slug']) ? 'slug=' . urlencode($s['slug']) : 'id=' . $s['id']);
?>
<div class="card" style="display:flex; flex-direction:column; justify-content:space-between; transition:all 0.3s ease; padding:0; overflow:hidden;">
    <div>
        <a href="<?= $s_url ?>" style="display:block; overflow:hidden;">
            <img src="<?= e($s['image_url'] ?: 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=600&q=80') ?>" 
                 alt="<?= e($s['title']) ?>" 
                 loading="lazy"
                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=600&q=80';"
                 style="width:100%; height:200px; object-fit:cover; display:block; transition:transform 0.3s ease;">
        </a>
        <div style="padding:22px 22px 10px;">
            <h3 style="margin:0 0 10px; font-size:20px; font-weight:700;">
                <a href="<?= $s_url ?>" style="color:var(--n); text-decoration:none; transition:color 0.2s;"><?= e($s['title']) ?></a>
            </h3>
            <p style="color:#64748b; font-size:14px; line-height:1.6; margin:0 0 15px;"><?= e($s['description']) ?></p>
        </div>
    </div>
    <div style="padding:0 22px 22px;">
        <a href="<?= $s_url ?>" class="btn sm" style="width:100%; text-align:center; display:flex; align-items:center; justify-content:center; gap:6px; font-weight:600;">
            Explore Service ➔
        </a>
    </div>
</div>
<?php endforeach; ?>
</div></div></section>

<!-- Standard Working Process Section -->
<section class="s"><div class="wrap process-wrapper">
    <div class="process-subtitle">HOW WE WORK</div>
    <h2 class="t" style="margin-top:10px;">Our Standard Working Process</h2>
    <p class="process-desc">Recruitment is a process of finding and attracting the potential resources for filling up the vacant positions in an organization. We source the candidates with the abilities and attitude, which are required for achieving the objectives of an organization.</p>
    
    <div class="process-steps">
        <div class="process-step">
            <div class="process-icon-box">
                <span class="process-number">01</span>
                <div style="font-size:40px; color:var(--n);">📝</div>
            </div>
            <h3>Requirement Analysis</h3>
            <p>We analyse critical requirements of our clients like experience, skills & budget to put best resource to fit into the clients requirements.</p>
            <div class="process-arrow">➔</div>
        </div>
        
        <div class="process-step">
            <div class="process-icon-box">
                <span class="process-number">02</span>
                <div style="font-size:40px; color:var(--n);">📅</div>
            </div>
            <h3>Sourcing & Prescreening</h3>
            <p>We source right candidate and do pre screening based on filter criteria shared by client. Resumes with summary process to client to shortlist.</p>
            <div class="process-arrow">➔</div>
        </div>
        
        <div class="process-step">
            <div class="process-icon-box">
                <span class="process-number">03</span>
                <div style="font-size:40px; color:var(--n);">🤝</div>
            </div>
            <h3>Interview & Selection</h3>
            <p>We use multiple mode of interview like Telephone, Video & Personal meeting to speed up the selection process. After selection, we do follow all documents.</p>
        </div>
    </div>
</div></section>

<!-- Testimonial Section with Dynamic Slider -->
<section class="testimonial-section"><div class="wrap">
    <div class="test-container">
        <div class="test-img-group">
            <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=200&q=80" class="test-img test-img-1" alt="Person">
            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=200&q=80" class="test-img test-img-2" alt="Person">
            <img src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=200&q=80" class="test-img test-img-3" alt="Person">
        </div>
        
        <div class="test-content">
            <div class="test-quote-icon">"</div>
            <div class="test-slider">
                <?php if(empty($testimonials)): ?>
                    <div class="test-slide active">
                        <p class="test-text">"Outstanding service and dedicated professionals."</p>
                    </div>
                <?php else: ?>
                    <?php foreach($testimonials as $key => $t): ?>
                    <div class="test-slide <?= $key === 0 ? 'active' : '' ?>">
                        <p class="test-text">"<?= e($t['content']) ?>"</p>
                        <div class="test-stars"><?= str_repeat('★', $t['rating']) ?><?= str_repeat('☆', 5 - $t['rating']) ?></div>
                        <div class="test-author"><?= e($t['author_name']) ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Slider Dots -->
            <div class="test-dots">
                <?php foreach($testimonials as $key => $t): ?>
                <span class="dot <?= $key === 0 ? 'active' : '' ?>" onclick="currentSlide(<?= $key ?>)"></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div></section>

<!-- Latest Openings Section -->
<section class="s bg"><div class="wrap"><h2 class="t">Latest Openings</h2>
<?php foreach($jobs as $j): ?>
<div class="job">
<div><h3 style="margin:0; margin-bottom:8px;"><?=e($j['title'])?></h3>
<span class="tag"><?=e($j['category'])?></span><span class="tag">📍 <?=e($j['location'])?></span><span class="tag">💼 <?=e($j['job_type'])?></span>
</div><a class="btn sm" href="apply.php?id=<?=$j['id']?>">Apply Now</a>
</div>
<?php endforeach; ?>
<p style="text-align:center; margin-top:40px;"><a class="btn alt" style="background:#fff; color:var(--o) !important; border-color:var(--o);" href="jobs.php">View All Jobs</a></p>
</div></section>

<!-- Latest Insights & Blogs Section -->
<?php if(!empty($latest_blogs)): ?>
<section class="s"><div class="wrap">
    <div class="process-subtitle" style="text-align:center;">FROM OUR BLOG</div>
    <h2 class="t" style="margin-top:10px;">Latest Articles & Insights</h2>
    <div class="grid3">
        <?php foreach($latest_blogs as $b): ?>
        <article class="card" style="display:flex; flex-direction:column; padding:0; overflow:hidden;">
            <a href="blog-detail.php?slug=<?=urlencode($b['slug'])?>" style="display:block; overflow:hidden;">
                <?php if(!empty($b['image_url'])): ?>
                    <img src="<?=e($b['image_url'])?>" alt="<?=e($b['title'])?>" loading="lazy" style="width:100%; height:190px; object-fit:cover; margin-bottom:0; transition:transform 0.3s ease;">
                <?php else: ?>
                    <div style="width:100%; height:190px; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-size:32px;">📰</div>
                <?php endif; ?>
            </a>
            <div style="padding:22px; display:flex; flex-direction:column; flex:1;">
                <div style="font-size:12px; color:#64748b; margin-bottom:10px;">
                    🗓️ <?=date('M d, Y', strtotime($b['created_at']))?> &middot; ✍️ <?=e($b['author'])?>
                </div>
                <h3 style="font-size:18px; line-height:1.4; margin-bottom:10px;">
                    <a href="blog-detail.php?slug=<?=urlencode($b['slug'])?>" style="color:var(--n);">
                        <?=e(mb_strimwidth($b['title'], 0, 65, '...'))?>
                    </a>
                </h3>
                <p style="font-size:14px; color:#64748b; line-height:1.6; margin-bottom:20px; flex:1;">
                    <?=e(mb_strimwidth($b['excerpt'] ?: strip_tags($b['content']), 0, 110, '...'))?>
                </p>
                <div style="margin-top:auto;">
                    <a href="blog-detail.php?slug=<?=urlencode($b['slug'])?>" class="btn sm alt" style="width:100%; text-align:center;">Read More ➔</a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <div class="btn-group" style="margin-top:35px;">
        <a class="btn" href="blogs.php">View All Articles ➔</a>
    </div>
</div></section>
<?php endif; ?>

<!-- Stats Section -->
<?php 
$site_stats = db()->query("SELECT * FROM site_stats WHERE active=1 ORDER BY sort_order ASC, id ASC")->fetchAll();
if (!empty($site_stats)):
?>
<section class="stat" id="stats"><div class="wrap" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:24px;">
<?php foreach($site_stats as $st_item): ?>
    <div><b><?= e($st_item['metric']) ?></b><?= e($st_item['label']) ?></div>
<?php endforeach; ?>
</div></section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
