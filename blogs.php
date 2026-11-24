<?php
$title = 'Insights & Blogs';
$sub = 'Latest trends, hiring strategies, and workplace insights';
include 'includes/header.php';

$q = trim($_GET['q'] ?? '');
$params = [];
$sql = "SELECT * FROM blogs WHERE status = 'published'";

if ($q !== '') {
    $sql .= " AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$sql .= " ORDER BY id DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$blogs = $stmt->fetchAll();
?>

<section class="s"><div class="wrap">
    <!-- Search Bar -->
    <form class="filters" method="get" style="max-width:600px; margin: 0 auto 40px;">
        <input name="q" placeholder="Search insights & articles..." value="<?= e($q) ?>">
        <button class="btn" style="flex:0 0 auto; white-space:nowrap;">Search</button>
        <?php if ($q !== ''): ?>
            <a href="blogs.php" class="btn alt" style="flex:0 0 auto; white-space:nowrap;">Clear</a>
        <?php endif; ?>
    </form>

    <?php if ($q !== ''): ?>
        <p style="text-align:center; color:#64748b; margin-bottom:30px;">
            Showing results for "<b><?= e($q) ?></b>" (<?= count($blogs) ?> found)
        </p>
    <?php endif; ?>

    <?php if (empty($blogs)): ?>
        <div style="text-align:center; padding:50px 20px; background:#fff; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.03);">
            <div style="font-size:48px; margin-bottom:15px;">📰</div>
            <h3 style="color:var(--n); margin-bottom:8px;">No Articles Found</h3>
            <p style="color:#64748b; margin-bottom:20px;">We couldn't find any articles matching your search query.</p>
            <a href="blogs.php" class="btn sm">View All Articles</a>
        </div>
    <?php else: ?>
        <div class="grid3">
            <?php foreach ($blogs as $b): ?>
                <article class="card" style="display:flex; flex-direction:column; padding:0; overflow:hidden;">
                    <a href="blog-detail.php?slug=<?= urlencode($b['slug']) ?>" style="display:block; overflow:hidden;">
                        <?php if (!empty($b['image_url'])): ?>
                            <img src="<?= e($b['image_url']) ?>" alt="<?= e($b['title']) ?>" style="width:100%; height:200px; object-fit:cover; margin-bottom:0; transition:transform 0.3s ease;">
                        <?php else: ?>
                            <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80" alt="Article" style="width:100%; height:200px; object-fit:cover; margin-bottom:0;">
                        <?php endif; ?>
                    </a>
                    
                    <div style="padding:24px; display:flex; flex-direction:column; flex:1;">
                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px; font-size:13px; color:#64748b;">
                            <span>🗓️ <?= date('M d, Y', strtotime($b['created_at'])) ?></span>
                            <span>&middot;</span>
                            <span>✍️ <?= e($b['author']) ?></span>
                        </div>
                        
                        <h3 style="font-size:19px; line-height:1.4; margin-bottom:10px;">
                            <a href="blog-detail.php?slug=<?= urlencode($b['slug']) ?>" style="color:var(--n);">
                                <?= e($b['title']) ?>
                            </a>
                        </h3>
                        
                        <p style="font-size:14px; color:#64748b; line-height:1.6; margin-bottom:20px; flex:1;">
                            <?= e(mb_strimwidth($b['excerpt'] ?: strip_tags($b['content']), 0, 130, '...')) ?>
                        </p>
                        
                        <div style="margin-top:auto;">
                            <a href="blog-detail.php?slug=<?= urlencode($b['slug']) ?>" class="btn sm alt" style="width:100%; text-align:center;">
                                Read Article ➔
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div></section>

<?php include 'includes/footer.php'; ?>
