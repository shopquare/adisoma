<?php
require_once 'includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$id = (int)($_GET['id'] ?? 0);

$service = null;
if ($slug !== '') {
    $stmt = db()->prepare("SELECT * FROM services WHERE slug = ? AND active = 1");
    $stmt->execute([$slug]);
    $service = $stmt->fetch();
} elseif ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM services WHERE id = ? AND active = 1");
    $stmt->execute([$id]);
    $service = $stmt->fetch();
}

// Fallback to first active service if not found
if (!$service) {
    redirect('services.php');
}

$title = $service['title'];
$sub = 'Specialized Talent Acquisition & Workforce Solutions';
$meta_desc = mb_strimwidth(strip_tags($service['description'] ?: $service['content']), 0, 160, '...');
$og_image = !empty($service['image_url']) ? $service['image_url'] : (BASE_URL . '/assets/images/adisoma-logo.png');
$canonical_url = 'https://adisoma.infy.click/service-detail.php?slug=' . urlencode($service['slug'] ?: $service['id']);
include 'includes/header.php';

// Fetch all other active services for the sidebar
$other_services = db()->prepare("SELECT * FROM services WHERE id != ? AND active = 1 ORDER BY sort_order ASC");
$other_services->execute([$service['id']]);
$other_services = $other_services->fetchAll();

// Fetch matching jobs if available
$cat = (stripos($service['title'], 'Non-IT') !== false) ? 'Non-IT' : ((stripos($service['title'], 'IT') !== false) ? 'IT' : null);
$related_jobs = [];
if ($cat) {
    $jstmt = db()->prepare("SELECT * FROM jobs WHERE status = 'published' AND category = ? ORDER BY id DESC LIMIT 3");
    $jstmt->execute([$cat]);
    $related_jobs = $jstmt->fetchAll();
} else {
    $jstmt = db()->query("SELECT * FROM jobs WHERE status = 'published' ORDER BY id DESC LIMIT 3");
    $related_jobs = $jstmt->fetchAll();
}

// Helper to render rich markdown/text content
function render_service_content($text) {
    return render_markdown_content($text);
}
?>

<section class="s" style="padding-top:35px;">
    <div class="wrap">
        <!-- Breadcrumb Navigation -->
        <nav style="margin-bottom:28px; font-size:14px; color:#64748b; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <a href="index.php" style="color:#0284c7; text-decoration:none;">Home</a>
            <span>/</span>
            <a href="services.php" style="color:#0284c7; text-decoration:none;">Services</a>
            <span>/</span>
            <span style="color:#1e293b; font-weight:600;"><?= e($service['title']) ?></span>
        </nav>

        <div style="display:grid; grid-template-columns: 2.1fr 1fr; gap:40px; align-items:start;">
            <!-- Left: Main Service Content -->
            <article>
                <!-- Hero Image with subtle overlay -->
                <div style="position:relative; border-radius:14px; overflow:hidden; margin-bottom:30px; box-shadow:0 10px 25px rgba(0,0,0,0.08); background:#f1f5f9;">
                    <img src="<?= e($service['image_url'] ?: 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=1200&q=80') ?>" 
                         alt="<?= e($service['title']) ?>" 
                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=1200&q=80';"
                         style="width:100%; height:380px; object-fit:cover; display:block;">
                    <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(to top, rgba(15,23,42,0.85) 0%, rgba(15,23,42,0) 100%); padding:30px 25px 20px;">
                        <span style="background:var(--o); color:#fff; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; padding:4px 12px; border-radius:20px; display:inline-block; margin-bottom:10px;">
                            ADISOMA Core Practice
                        </span>
                        <h1 style="color:#fff; margin:0; font-size:30px; font-weight:800; text-shadow:0 2px 4px rgba(0,0,0,0.3);"><?= e($service['title']) ?></h1>
                    </div>
                </div>

                <!-- Short Highlights / Lead Box -->
                <div style="background:#f8fafc; border-left:4px solid var(--o); padding:20px 24px; border-radius:0 10px 10px 0; margin-bottom:32px;">
                    <p style="margin:0; font-size:17px; line-height:1.7; color:#1e293b; font-weight:500;">
                        <?= e($service['description']) ?>
                    </p>
                </div>

                <!-- 4 Value Pillars -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:18px; margin-bottom:35px;">
                    <div class="card" style="padding:18px; text-align:center; border-radius:10px;">
                        <div style="font-size:30px; margin-bottom:8px;">🎯</div>
                        <h4 style="margin:0 0 5px; font-size:15px; color:var(--n);">Precision Matching</h4>
                        <p style="margin:0; font-size:12px; color:#64748b; line-height:1.4;">Custom candidate fit for skill and culture</p>
                    </div>
                    <div class="card" style="padding:18px; text-align:center; border-radius:10px;">
                        <div style="font-size:30px; margin-bottom:8px;">⚡</div>
                        <h4 style="margin:0 0 5px; font-size:15px; color:var(--n);">Fast Turnaround</h4>
                        <p style="margin:0; font-size:12px; color:#64748b; line-height:1.4;">Pre-screened profiles within 48 to 72 hours</p>
                    </div>
                    <div class="card" style="padding:18px; text-align:center; border-radius:10px;">
                        <div style="font-size:30px; margin-bottom:8px;">🔍</div>
                        <h4 style="margin:0 0 5px; font-size:15px; color:var(--n);">Multi-Stage Vetting</h4>
                        <p style="margin:0; font-size:12px; color:#64748b; line-height:1.4;">Technical and background checks verified</p>
                    </div>
                    <div class="card" style="padding:18px; text-align:center; border-radius:10px;">
                        <div style="font-size:30px; margin-bottom:8px;">🤝</div>
                        <h4 style="margin:0 0 5px; font-size:15px; color:var(--n);">Dedicated Desk</h4>
                        <p style="margin:0; font-size:12px; color:#64748b; line-height:1.4;">Specialized account manager support</p>
                    </div>
                </div>

                <!-- Rich Service Details -->
                <div class="service-body" style="font-size:16px; color:#334155; line-height:1.8; margin-bottom:40px;">
                    <?php if(!empty($service['content'])): ?>
                        <?= render_service_content($service['content']) ?>
                    <?php else: ?>
                        <h3 style="color:var(--n); margin-top:20px;">Comprehensive <?= e($service['title']) ?> Services</h3>
                        <p>At ADISOMA Solution, we deliver tailored recruitment methodologies to ensure your business attracts, engages, and secures the best market talent for your <?= e($service['title']) ?> requirements.</p>
                        <p>Our experienced talent acquisition teams combine extensive industry knowledge with rigorous screening protocols, delivering seamless hiring results that fuel business performance.</p>
                    <?php endif; ?>
                </div>

                <!-- 4-Step Working Process for this Service -->
                <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:28px; margin-bottom:40px; box-shadow:0 4px 15px rgba(0,0,0,0.03);">
                    <h3 style="margin:0 0 20px; font-size:20px; color:var(--n); display:flex; align-items:center; gap:8px;">
                        <span>⚙️</span> Our Delivery Process
                    </h3>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:16px; text-align:center;">
                        <div style="padding:12px; background:#f8fafc; border-radius:8px;">
                            <div style="width:32px; height:32px; border-radius:50%; background:var(--o); color:#fff; font-weight:700; display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">1</div>
                            <b style="font-size:13px; color:var(--n); display:block; margin-bottom:4px;">Discovery</b>
                            <span style="font-size:12px; color:#64748b;">Role & skills profiling</span>
                        </div>
                        <div style="padding:12px; background:#f8fafc; border-radius:8px;">
                            <div style="width:32px; height:32px; border-radius:50%; background:var(--o); color:#fff; font-weight:700; display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">2</div>
                            <b style="font-size:13px; color:var(--n); display:block; margin-bottom:4px;">Sourcing</b>
                            <span style="font-size:12px; color:#64748b;">Targeted headhunting</span>
                        </div>
                        <div style="padding:12px; background:#f8fafc; border-radius:8px;">
                            <div style="width:32px; height:32px; border-radius:50%; background:var(--o); color:#fff; font-weight:700; display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">3</div>
                            <b style="font-size:13px; color:var(--n); display:block; margin-bottom:4px;">Screening</b>
                            <span style="font-size:12px; color:#64748b;">Assessments & vetting</span>
                        </div>
                        <div style="padding:12px; background:#f8fafc; border-radius:8px;">
                            <div style="width:32px; height:32px; border-radius:50%; background:var(--o); color:#fff; font-weight:700; display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">4</div>
                            <b style="font-size:13px; color:var(--n); display:block; margin-bottom:4px;">Placement</b>
                            <span style="font-size:12px; color:#64748b;">Offer & onboarding</span>
                        </div>
                    </div>
                </div>

                <!-- Relevant Jobs Section (if any) -->
                <?php if(!empty($related_jobs)): ?>
                <div style="margin-bottom:40px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
                        <h3 style="margin:0; font-size:20px; color:var(--n);">Current Openings in this Domain</h3>
                        <a href="jobs.php" style="color:var(--o); font-size:14px; font-weight:600; text-decoration:none;">View All Jobs ➔</a>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <?php foreach($related_jobs as $j): ?>
                        <div class="job" style="margin:0; padding:18px 20px;">
                            <div>
                                <h4 style="margin:0 0 6px; font-size:17px;"><?= e($j['title']) ?></h4>
                                <span class="tag"><?= e($j['location']) ?></span>
                                <span class="tag"><?= e($j['experience']) ?></span>
                                <span class="tag" style="background:#e0f2fe; color:#0369a1;"><?= e($j['job_type']) ?></span>
                            </div>
                            <a class="btn sm" href="apply.php?id=<?= $j['id'] ?>">Apply Now</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Bottom Conversion CTA -->
                <div style="background:linear-gradient(135deg, var(--n) 0%, #1e293b 100%); color:#ffffff; padding:35px 30px; border-radius:14px; text-align:center; box-shadow:0 8px 24px rgba(15,23,42,0.15);">
                    <h3 style="color:#ffffff; font-size:24px; margin:0 0 10px; font-weight:700;">
                        Ready to Scale Your Team with <?= e($service['title']) ?>?
                    </h3>
                    <p style="color:#94a3b8; font-size:15px; max-width:560px; margin:0 auto 24px; line-height:1.6;">
                        Connect with ADISOMA's specialized talent consultants today to define your requirements and receive pre-vetted candidate profiles.
                    </p>
                    <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
                        <a href="employers.php?service=<?= urlencode($service['title']) ?>" class="btn" style="background:var(--o); border-color:var(--o); padding:12px 28px; font-weight:700;">
                            Request Talent & Hire ➔
                        </a>
                        <a href="contact.php?subject=<?= urlencode('Inquiry regarding ' . $service['title']) ?>" class="btn alt" style="border-color:#fff; color:#fff !important; padding:12px 24px;">
                            Speak with a Consultant
                        </a>
                    </div>
                </div>
            </article>

            <!-- Right Sidebar -->
            <aside>
                <!-- All Services Menu Card -->
                <div class="card" style="padding:24px; margin-bottom:28px;">
                    <h4 style="margin:0 0 16px; font-size:17px; color:var(--n); border-bottom:2px solid #f1f5f9; padding-bottom:10px;">
                        Our Services
                    </h4>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div style="padding:10px 14px; border-radius:8px; background:var(--o); color:#ffffff; font-weight:600; font-size:14px; display:flex; justify-content:space-between; align-items:center;">
                            <span><?= e($service['title']) ?></span>
                            <span>✓</span>
                        </div>
                        <?php foreach($other_services as $os): 
                            $os_url = 'service-detail.php?' . (!empty($os['slug']) ? 'slug=' . urlencode($os['slug']) : 'id=' . $os['id']);
                        ?>
                        <a href="<?= $os_url ?>" style="padding:10px 14px; border-radius:8px; background:#f8fafc; color:#334155; text-decoration:none; font-size:14px; display:flex; justify-content:space-between; align-items:center; transition:all 0.2s ease;">
                            <span><?= e($os['title']) ?></span>
                            <span style="color:#94a3b8;">➔</span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Quick Consultation Card -->
                <div class="card" style="padding:24px; margin-bottom:28px; background:#f8fafc; border:1px solid #e2e8f0;">
                    <div style="font-size:32px; margin-bottom:12px;">📞</div>
                    <h4 style="margin:0 0 8px; font-size:17px; color:var(--n);">Need Fast Hiring?</h4>
                    <p style="margin:0 0 18px; font-size:13px; color:#64748b; line-height:1.6;">
                        Talk to our recruitment desk for immediate talent sourcing assistance or custom proposals.
                    </p>
                    <div style="font-size:14px; margin-bottom:8px;">
                        <strong style="color:var(--n);">Phone:</strong> <a href="tel:<?= e(CO['phone']) ?>" style="color:#0284c7; text-decoration:none;"><?= e(CO['phone']) ?></a>
                    </div>
                    <div style="font-size:14px; margin-bottom:20px;">
                        <strong style="color:var(--n);">Email:</strong> <a href="mailto:<?= e(CO['email']) ?>" style="color:#0284c7; text-decoration:none;"><?= e(CO['email']) ?></a>
                    </div>
                    <a href="contact.php" class="btn sm" style="width:100%; text-align:center; display:block;">
                        Contact Recruitment Team
                    </a>
                </div>

                <!-- Job Seeker Widget -->
                <div class="card" style="padding:24px; text-align:center;">
                    <div style="font-size:32px; margin-bottom:10px;">💼</div>
                    <h4 style="margin:0 0 8px; font-size:16px; color:var(--n);">Are You Looking for a Job?</h4>
                    <p style="margin:0 0 16px; font-size:13px; color:#64748b; line-height:1.5;">
                        Explore our active job openings across IT and Non-IT domains and submit your resume.
                    </p>
                    <a href="jobs.php" class="btn sm alt" style="width:100%; text-align:center; display:block;">
                        Explore Open Jobs ➔
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>