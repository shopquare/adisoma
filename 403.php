<?php
http_response_code(403);
$title = '403 - Access Forbidden';
$sub = 'You do not have permission to access this resource';
require_once __DIR__ . '/includes/header.php';
?>

<section class="s" style="text-align:center; padding:70px 20px;">
    <div class="wrap" style="max-width:620px; margin:0 auto;">
        <div style="font-size:72px; font-weight:800; color:#ef4444; line-height:1; margin-bottom:12px;">403</div>
        <h2 style="font-size:28px; color:var(--n); margin:0 0 16px;">Access Forbidden</h2>
        <p style="color:#64748b; font-size:16px; line-height:1.7; margin-bottom:30px;">
            The requested area is protected and requires verified administrative credentials to view.
        </p>
        
        <div class="btn-group" style="justify-content:center; gap:12px;">
            <a href="<?= BASE_URL ?>/index.php" class="btn">Return to Homepage</a>
            <a href="<?= BASE_URL ?>/admin/login.php" class="btn alt">Admin Login</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
