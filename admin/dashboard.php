<?php include '_header.php'; 
$q=fn($s)=>(int)db()->query($s)->fetchColumn();

// Enhanced array with Icons and Links to the respective pages
$st = [
    ['label' => 'Published Jobs', 'count' => $q("SELECT COUNT(*) FROM jobs WHERE status='published'"), 'icon' => '💼', 'link' => 'manage-jobs.php'],
    ['label' => 'Draft Jobs', 'count' => $q("SELECT COUNT(*) FROM jobs WHERE status='draft'"), 'icon' => '📝', 'link' => 'manage-jobs.php'],
    ['label' => 'Total Apps', 'count' => $q('SELECT COUNT(*) FROM applications'), 'icon' => '📄', 'link' => 'applications.php'],
    ['label' => 'New Apps', 'count' => $q("SELECT COUNT(*) FROM applications WHERE status='new'"), 'icon' => '✨', 'link' => 'applications.php'],
    ['label' => 'Employers', 'count' => $q("SELECT COUNT(*) FROM inquiries WHERE type='employer'"), 'icon' => '🏢', 'link' => 'inquiries.php?type=employer'],
    ['label' => 'Messages', 'count' => $q("SELECT COUNT(*) FROM inquiries WHERE type='contact'"), 'icon' => '✉️', 'link' => 'inquiries.php?type=contact'],
    ['label' => 'Testimonials', 'count' => $q("SELECT COUNT(*) FROM testimonials"), 'icon' => '💬', 'link' => 'manage-testimonials.php']
];

$recent=db()->query('SELECT a.*,j.title jt FROM applications a LEFT JOIN jobs j ON j.id=a.job_id ORDER BY a.id DESC LIMIT 5')->fetchAll(); 
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h1 style="margin:0;">Dashboard Overview</h1>
    <a href="../index.php" target="_blank" class="btn sm alt">View Live Site ➔</a>
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