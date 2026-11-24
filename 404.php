<?php
http_response_code(404);
$title = '404 - Page Not Found';
$sub = 'The page you requested could not be found';
require_once __DIR__ . '/includes/header.php';
?>

<section class="s" style="text-align:center; padding:70px 20px;">
    <div class="wrap" style="max-width:680px; margin:0 auto;">
        <div style="font-size:72px; font-weight:800; color:var(--o); line-height:1; margin-bottom:12px;">404</div>
        <h2 style="font-size:28px; color:var(--n); margin:0 0 16px;">Oops! Page Not Found</h2>
        <p style="color:#64748b; font-size:16px; line-height:1.7; margin-bottom:30px;">
            The link you followed may be broken, or the page may have been relocated or updated. Don't worry, our career and staffing opportunities are always available below:
        </p>
        
        <div class="btn-group" style="justify-content:center; gap:12px; margin-bottom:40px;">
            <a href="<?= BASE_URL ?>/index.php" class="btn">🏠 Return to Homepage</a>
            <a href="<?= BASE_URL ?>/jobs.php" class="btn alt">💼 Explore Jobs</a>
            <a href="<?= BASE_URL ?>/services.php" class="btn alt">🛠️ Our Services</a>
        </div>

        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px; text-align:left;">
            <strong style="color:var(--n); font-size:15px; display:block; margin-bottom:8px;">Looking for something specific?</strong>
            <div style="display:flex; gap:15px; flex-wrap:wrap; font-size:14px;">
                <a href="<?= BASE_URL ?>/about.php">About Us</a> &bull;
                <a href="<?= BASE_URL ?>/blogs.php">Industry Insights & Blogs</a> &bull;
                <a href="<?= BASE_URL ?>/employers.php">For Employers</a> &bull;
                <a href="<?= BASE_URL ?>/contact.php">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
