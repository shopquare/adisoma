<?php
$title = 'Insights & Blogs';
$sub = 'Latest trends, hiring strategies, and workplace insights';
include 'includes/header.php';

// Sanitize query and sort inputs
$q = trim($_GET['q'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');

// Base query conditions
$where = ["status = 'published'"];
$params = [];

if ($q !== '') {
    $where[] = '(title LIKE ? OR excerpt LIKE ? OR content LIKE ?)';
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

$whereClause = implode(' AND ', $where);

// Sorting order - newest blog always on top by default
switch ($sort) {
    case 'oldest':
        $orderBy = 'created_at ASC, id ASC';
        break;
    case 'title':
        $orderBy = 'title ASC';
        break;
    case 'newest':
    default:
        $sort = 'newest';
        $orderBy = 'created_at DESC, id DESC';
        break;
}

// Pagination parameters (6 articles per page - optimal for 3-column grid)
$perPage = 6;
$page = max(1, (int)($_GET['page'] ?? 1));

// Count total matching blogs
$countStmt = db()->prepare("SELECT COUNT(*) FROM blogs WHERE $whereClause");
$countStmt->execute($params);
$totalBlogs = (int)$countStmt->fetchColumn();

$totalPages = max(1, (int)ceil($totalBlogs / $perPage));
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

// Fetch page slice with safe integer limit/offset
$sql = "SELECT * FROM blogs WHERE $whereClause ORDER BY $orderBy LIMIT $perPage OFFSET $offset";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$blogs = $stmt->fetchAll();

// URL builder preserving search query and sort
if (!function_exists('blog_page_url')) {
    function blog_page_url(int $targetPage): string {
        $queryParams = $_GET;
        $queryParams['page'] = $targetPage;
        return 'blogs.php?' . http_build_query($queryParams);
    }
}

$fromIndex = $totalBlogs > 0 ? $offset + 1 : 0;
$toIndex   = min($offset + $perPage, $totalBlogs);
?>

<section class="s"><div class="wrap">
    <!-- Filter and Sort Bar -->
    <form class="filters" method="get" style="max-width:720px; margin: 0 auto 35px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <input name="q" placeholder="Search insights & articles..." value="<?= e($q) ?>" style="flex:1; min-width:220px;">
        
        <select name="sort" onchange="this.form.submit()" style="flex:0 0 auto; min-width:160px; padding:12px 14px; border:1px solid #cbd5e1; border-radius:8px; background:#f8fafc; font-family:inherit; font-size:14px; color:#1e293b;">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
            <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Title (A-Z)</option>
        </select>
        
        <button class="btn" style="margin:0; flex:0 0 auto; white-space:nowrap;">Search</button>
        <?php if ($q !== '' || $sort !== 'newest'): ?>
            <a href="blogs.php" class="btn alt" style="margin:0; flex:0 0 auto; white-space:nowrap; text-decoration:none;">Reset</a>
        <?php endif; ?>
    </form>

    <!-- Results Header / Summary -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:28px;">
        <p style="margin:0; font-size:15px; color:#64748b; font-weight:600;">
            <?php if ($totalBlogs === 0): ?>
                No articles found matching your criteria
            <?php elseif ($q !== ''): ?>
                Showing results for "<strong><?= e($q) ?></strong>" (<?= $totalBlogs ?> found)
            <?php elseif ($totalPages > 1): ?>
                Showing <strong style="color:var(--n);"><?= $fromIndex ?>–<?= $toIndex ?></strong> of <strong style="color:var(--n);"><?= $totalBlogs ?></strong> articles
            <?php else: ?>
                <strong style="color:var(--n);"><?= $totalBlogs ?></strong> article<?= $totalBlogs === 1 ? '' : 's' ?> available
            <?php endif; ?>
        </p>

        <?php if ($totalPages > 1): ?>
            <span style="font-size:13px; color:#64748b; background:#f1f5f9; padding:4px 10px; border-radius:6px; font-weight:600;">
                Page <?= $page ?> of <?= $totalPages ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- Blogs Listing -->
    <?php if (empty($blogs)): ?>
        <div style="text-align:center; padding:60px 20px; background:#fff; border-radius:16px; border:1px solid #e2e8f0; margin-top:20px;">
            <div style="font-size:48px; margin-bottom:15px;">📰</div>
            <h3 style="color:var(--n); margin-bottom:8px;">No Articles Found</h3>
            <p style="color:#64748b; max-width:500px; margin:0 auto 25px; line-height:1.6;">
                We could not find any published articles matching your criteria. Try adjusting your search query or sorting options.
            </p>
            <a href="blogs.php" class="btn sm">View All Articles</a>
        </div>
    <?php else: ?>
        <div class="grid3">
            <?php foreach ($blogs as $b): ?>
                <article class="card" style="display:flex; flex-direction:column; padding:0; overflow:hidden;">
                    <a href="blog-detail.php?slug=<?= urlencode($b['slug']) ?>" style="display:block; overflow:hidden;">
                        <?php if (!empty($b['image_url'])): ?>
                            <img src="<?= e($b['image_url']) ?>" alt="<?= e($b['title']) ?>" loading="lazy" style="width:100%; height:200px; object-fit:cover; margin-bottom:0; transition:transform 0.3s ease;">
                        <?php else: ?>
                            <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80" alt="Article" loading="lazy" style="width:100%; height:200px; object-fit:cover; margin-bottom:0;">
                        <?php endif; ?>
                    </a>
                    
                    <div style="padding:24px; display:flex; flex-direction:column; flex:1;">
                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px; font-size:13px; color:#64748b;">
                            <span>🗓️ <?= date('M d, Y', strtotime($b['created_at'])) ?></span>
                            <span>&middot;</span>
                            <span>✍️ <?= e($b['author']) ?></span>
                        </div>
                        
                        <h3 style="font-size:19px; line-height:1.4; margin-bottom:10px;">
                            <a href="blog-detail.php?slug=<?= urlencode($b['slug']) ?>" style="color:var(--n); text-decoration:none;">
                                <?= e($b['title']) ?>
                            </a>
                        </h3>
                        
                        <p style="font-size:14px; color:#64748b; line-height:1.6; margin-bottom:20px; flex:1;">
                            <?= e(mb_strimwidth($b['excerpt'] ?: strip_tags($b['content']), 0, 130, '...')) ?>
                        </p>
                        
                        <div style="margin-top:auto;">
                            <a href="blog-detail.php?slug=<?= urlencode($b['slug']) ?>" class="btn sm alt" style="width:100%; text-align:center; display:block;">
                                Read Article ➔
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Responsive Pagination Component -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-wrap">
                <nav aria-label="Blog Articles Pagination">
                    <ul class="pagination">
                        <!-- Prev Button -->
                        <li>
                            <?php if ($page > 1): ?>
                                <a href="<?= e(blog_page_url($page - 1)) ?>" class="page-link" aria-label="Previous Page">
                                    <span>←</span> Prev
                                </a>
                            <?php else: ?>
                                <span class="page-link disabled" aria-disabled="true">
                                    <span>←</span> Prev
                                </span>
                            <?php endif; ?>
                        </li>

                        <!-- Page Numbers with Smart Windowing -->
                        <?php
                        $window = 2;
                        $hasLeftDots = false;
                        $hasRightDots = false;

                        for ($p = 1; $p <= $totalPages; $p++):
                            if ($p === 1 || $p === $totalPages || ($p >= $page - $window && $p <= $page + $window)):
                        ?>
                            <li>
                                <?php if ($p === $page): ?>
                                    <span class="page-link active" aria-current="page"><?= $p ?></span>
                                <?php else: ?>
                                    <a href="<?= e(blog_page_url($p)) ?>" class="page-link"><?= $p ?></a>
                                <?php endif; ?>
                            </li>
                        <?php
                            elseif ($p < $page - $window && !$hasLeftDots):
                                $hasLeftDots = true;
                                echo '<li><span class="page-dots">&hellip;</span></li>';
                            elseif ($p > $page + $window && !$hasRightDots):
                                $hasRightDots = true;
                                echo '<li><span class="page-dots">&hellip;</span></li>';
                            endif;
                        endfor;
                        ?>

                        <!-- Next Button -->
                        <li>
                            <?php if ($page < $totalPages): ?>
                                <a href="<?= e(blog_page_url($page + 1)) ?>" class="page-link" aria-label="Next Page">
                                    Next <span>→</span>
                                </a>
                            <?php else: ?>
                                <span class="page-link disabled" aria-disabled="true">
                                    Next <span>→</span>
                                </span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </nav>
                <div class="pagination-info">
                    Showing <?= $fromIndex ?>–<?= $toIndex ?> of <?= $totalBlogs ?> articles
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div></section>

<?php include 'includes/footer.php'; ?>
