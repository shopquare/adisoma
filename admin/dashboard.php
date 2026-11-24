<?php
require_once __DIR__ . '/../includes/functions.php';
admin_only();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'clear_cache') {
    if (!csrf_ok()) {
        flash('Invalid session token. Please try again.', 'err');
    } else {
        $new_v = clear_all_cache();
        log_action('Cleared', 'Cache', 0, "New asset version: {$new_v}");
        flash("Cache cleared successfully! Dynamic asset version updated to {$new_v}. All desktop & mobile browsers will now fetch the latest styles and scripts.", 'ok');
    }
    redirect('dashboard.php');
}

include '_header.php'; 
$q=fn($s)=>(int)db()->query($s)->fetchColumn();

// Enhanced array with Icons and Links to the respective pages
$st = [
    ['label' => 'Published Jobs', 'count' => $q("SELECT COUNT(*) FROM jobs WHERE status='published'"), 'icon' => '💼', 'link' => 'manage-jobs.php'],
    ['label' => 'Draft Jobs', 'count' => $q("SELECT COUNT(*) FROM jobs WHERE status='draft'"), 'icon' => '📝', 'link' => 'manage-jobs.php'],
    ['label' => 'Total Apps', 'count' => $q('SELECT COUNT(*) FROM applications'), 'icon' => '📄', 'link' => 'applications.php'],
    ['label' => 'New Apps', 'count' => $q("SELECT COUNT(*) FROM applications WHERE status='new'"), 'icon' => '✨', 'link' => 'applications.php'],
    ['label' => 'Employers', 'count' => $q("SELECT COUNT(*) FROM inquiries WHERE type='employer'"), 'icon' => '🏢', 'link' => 'inquiries.php?type=employer'],
    ['label' => 'Messages', 'count' => $q("SELECT COUNT(*) FROM inquiries WHERE type='contact'"), 'icon' => '✉️', 'link' => 'inquiries.php?type=contact'],
    ['label' => 'Services', 'count' => $q("SELECT COUNT(*) FROM services WHERE active=1"), 'icon' => '🛠️', 'link' => 'manage-services.php'],
    ['label' => 'About Page', 'count' => $q("SELECT COUNT(*) FROM about_sections WHERE active=1") . ' Secs', 'icon' => '📖', 'link' => 'manage-about.php'],
    ['label' => 'Blog Posts', 'count' => $q("SELECT COUNT(*) FROM blogs WHERE status='published'"), 'icon' => '📰', 'link' => 'manage-blogs.php'],
    ['label' => 'Testimonials', 'count' => $q("SELECT COUNT(*) FROM testimonials"), 'icon' => '💬', 'link' => 'manage-testimonials.php'],
    ['label' => 'Site Stats', 'count' => $q("SELECT COUNT(*) FROM site_stats WHERE active=1"), 'icon' => '📊', 'link' => 'manage-stats.php'],
    ['label' => 'Menu Links', 'count' => $q("SELECT COUNT(*) FROM nav_items WHERE menu_type='header' AND active=1"), 'icon' => '🧭', 'link' => 'manage-menu.php'],
    ['label' => 'Contact & Info', 'count' => 'Live', 'icon' => '⚙️', 'link' => 'manage-settings.php']
];

$recent=db()->query('SELECT a.*,j.title jt FROM applications a LEFT JOIN jobs j ON j.id=a.job_id ORDER BY a.id DESC LIMIT 5')->fetchAll(); 
?>

<div class="dash-head">
    <div>
        <h1 style="margin:0 0 6px;">Dashboard Overview</h1>
        <span style="color:#64748b; font-size:14px;">Welcome back to your administration portal</span>
    </div>
    <div class="dash-actions" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <form method="POST" style="margin:0; display:inline;" onsubmit="return confirm('Clear system and browser cache? This will force all browsers to load updated assets.');">
            <?=csrf()?>
            <input type="hidden" name="action" value="clear_cache">
            <button type="submit" class="btn sm" style="background:#f59e0b; border-color:#f59e0b; color:#fff !important; box-shadow:0 4px 14px rgba(245, 158, 11, 0.25); display:inline-flex; align-items:center; justify-content:center; gap:6px; font-weight:600; cursor:pointer;">
                <span>🧹</span> Clear Cache
            </button>
        </form>
        <a href="../index.php" target="_blank" class="btn sm" style="background:#0284c7; border-color:#0284c7; color:#fff !important; box-shadow:0 4px 14px rgba(2, 132, 199, 0.25); white-space:nowrap; display:inline-flex; align-items:center; justify-content:center; font-weight:600;">
            View Live Site ➔
        </a>
    </div>
</div>

<!-- Clickable, attractive stat cards -->
<div class="stats">
<?php foreach($st as $item): ?>
    <a href="<?= $item['link'] ?>" style="text-decoration:none; color:inherit;">
        <div class="card" style="text-align:center; padding:25px 15px;">
            <div style="font-size:36px; margin-bottom:10px;"><?= $item['icon'] ?></div>
            <b style="font-size:32px; color:var(--o); display:block; margin-bottom:5px; line-height:1;"><?= $item['count'] ?></b>
            <span style="font-weight:600; color:var(--g); font-size:14px; text-transform:uppercase; letter-spacing:0.5px;"><?= e($item['label']) ?></span>
        </div>
    </a>
<?php endforeach; ?>
</div>

<!-- System Performance & Cache Management Card -->
<div class="card" style="margin-top:25px; padding:20px 24px; background:#ffffff; border-radius:12px; border:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
    <div style="display:flex; align-items:center; gap:14px;">
        <div style="width:44px; height:44px; border-radius:10px; background:#fef3c7; color:#d97706; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0;">⚡</div>
        <div>
            <h4 style="margin:0 0 4px; font-size:15px; color:#1e293b; font-weight:700;">Cache & Asset Performance</h4>
            <div style="font-size:13px; color:#64748b; line-height:1.5;">
                Active Asset Version: <strong style="color:#0284c7;"><?= e(asset_v()) ?></strong>
                &bull; Last Cleared: <strong><?= e(asset_v_time()) ?></strong>
                &bull; OPcache: <span style="display:inline-block; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:600; background:<?= function_exists('opcache_reset') ? '#dcfce7; color:#15803d' : '#f1f5f9; color:#64748b' ?>;"><?= function_exists('opcache_reset') ? 'Active' : 'Standard' ?></span>
            </div>
        </div>
    </div>
    <form method="POST" style="margin:0;" onsubmit="return confirm('Purge server and browser caches? All visitors will instantly receive newly updated styles and scripts.');">
        <?=csrf()?>
        <input type="hidden" name="action" value="clear_cache">
        <button type="submit" class="btn sm" style="background:#f59e0b; border-color:#f59e0b; color:#fff !important; font-weight:600; padding:9px 18px; border-radius:8px; box-shadow:0 2px 10px rgba(245, 158, 11, 0.25); display:inline-flex; align-items:center; gap:6px; cursor:pointer;">
            <span>🧹</span> Purge Cache Now
        </button>
    </form>
</div>

<h2 style="margin-top:40px; border-bottom:2px solid #eee; padding-bottom:10px;">Recent Applications</h2>
<table>
    <tr>
        <th>Candidate Name</th>
        <th>Applied For (Job)</th>
        <th>Contact Email</th>
        <th>Date Applied</th>
    </tr>
    <?php if(empty($recent)): ?>
        <tr><td colspan="4" style="text-align:center; padding:20px; color:var(--g);">No applications received yet.</td></tr>
    <?php else: ?>
        <?php foreach($recent as $r): ?>
        <tr>
            <td style="font-weight:600; color:var(--n);"><?=e($r['name'])?></td>
            <td><span class="tag" style="margin:0;"><?=e($r['jt']?:'General Application')?></span></td>
            <td><a href="mailto:<?=e($r['email'])?>"><?=e($r['email'])?></a></td>
            <td style="color:var(--g);"><?=date('d M Y, h:i A', strtotime($r['created_at']))?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<?php include '_footer.php'; ?>