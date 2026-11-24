<?php $title='Home'; include 'includes/header.php';
$svc=db()->query('SELECT * FROM services WHERE active=1 ORDER BY sort_order LIMIT 6')->fetchAll();
$jobs=db()->query("SELECT * FROM jobs WHERE status='published' ORDER BY id DESC LIMIT 4")->fetchAll(); 
$testimonials=db()->query('SELECT * FROM testimonials WHERE active=1 ORDER BY id DESC')->fetchAll(); ?>

<!-- Hero Section -->
<section class="hero"><div class="wrap">
<h1>Connect. Hire. <span>Grow.</span></h1>
<p>ADISOMA Solution is your premier talent partner for a brighter tomorrow, matching skilled people with growing businesses through modern recruitment strategies.</p>
<a class="btn" href="jobs.php">Find a Job</a><a class="btn alt" href="employers.php" style="margin-left:15px;">Hire Talent</a>
</div></section>

<!-- About / Intro Section -->
<section class="s"><div class="wrap">
<div style="display:flex; gap:40px; align-items:center; flex-wrap:wrap;">
    <div style="flex:1; min-width:300px;">
        <span class="process-subtitle">ABOUT ADISOMA SOLUTIONS</span>
        <h2 style="color:var(--n); font-size:36px; margin-top:10px; margin-bottom:20px; font-weight:700; line-height:1.3;">Reliable & Cost Efficient<br>Recruitment Services</h2>
        <p style="font-size:16px; color:var(--g); line-height:1.8; margin-bottom:30px;">ADISOMA Solutions Pvt. Ltd. is your trusted partner in comprehensive HR Consulting, IT Recruitment & Staffing Solutions. With an unwavering commitment to excellence, we have been serving businesses across industries with top-notch IT & NON IT recruitment services.</p>
        
        <div style="display:flex; gap:20px; margin-bottom:30px; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:15px;">
                <div style="font-size:30px; color:var(--o);">📋</div>
                <div style="font-weight:600; color:var(--n); line-height:1.2;">Understand Your<br>Needs</div>
            </div>
            <div style="display:flex; align-items:center; gap:15px;">
                <div style="font-size:30px; color:var(--o);">🎯</div>
                <div style="font-weight:600; color:var(--n); line-height:1.2;">Find the Perfect<br>Candidate</div>
            </div>
        </div>
        <a class="btn" href="about.php">LEARN MORE</a>
    </div>
    <div style="flex:1; min-width:300px; text-align:center;">
        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=600&q=80" style="width:100%; max-width:500px; border-radius:50% 0 50% 50%; box-shadow:0 15px 40px rgba(0,0,0,0.1); border:8px solid #fff;" alt="Team">
    </div>
</div>
</div></section>

<!-- Services Section -->
<section class="s bg"><div class="wrap"><h2 class="t">Our Services</h2><div class="grid3">
<?php foreach($svc as $s): ?>
<div class="card">
<?php if(!empty($s['image_url'])): ?><img src="<?=e($s['image_url'])?>" alt="Service"><?php else: ?><img src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=400&q=80" alt="Service"><?php endif; ?>
<h3><?=e($s['title'])?></h3><p><?=e($s['description'])?></p>
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

<!-- Stats Section -->
<section class="stat"><div class="wrap grid4">
<div><b>500+</b>Placements</div><div><b>100+</b>Clients</div><div><b>10+</b>Industries</div><div><b>24h</b>Response</div>
</div></section>

<?php include 'includes/footer.php'; ?>
