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

<section class="s" style="padding-top:40px; padding-bottom:80px;"><div class="wrap">
    <!-- Modern Inline Search & Sort Toolbar -->
    <div class="blog-search-bar-wrap">
        <form class="blog-search-bar" method="get">
            <div class="search-input-group">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" placeholder="Search insights & articles..." value="<?= e($q) ?>" autocomplete="off">
                <?php if ($q !== ''): ?>
                    <a href="blogs.php<?= $sort !== 'newest' ? '?sort=' . urlencode($sort) : '' ?>" class="search-clear-btn" title="Clear search">&times;</a>
                <?php endif; ?>
            </div>

            <div class="search-divider"></div>

            <div class="sort-select-group">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="4" y1="6" x2="20" y2="6"></line>
                    <line x1="7" y1="12" x2="17" y2="12"></line>
                    <line x1="10" y1="18" x2="14" y2="18"></line>
                </svg>
                <select name="sort" onchange="this.form.submit()" aria-label="Sort articles">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Title (A-Z)</option>
                </select>
            </div>

            <button type="submit" class="search-submit-btn">Search</button>

            <?php if ($q !== '' || $sort !== 'newest'): ?>
                <a href="blogs.php" class="search-reset-btn" title="Reset all filters">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Results Header / Summary -->
    <div class="blog-meta-bar">
        <div class="blog-meta-count">
            <?php if ($totalBlogs === 0): ?>
                No articles found matching your criteria
            <?php elseif ($q !== ''): ?>
                Showing results for "<strong><?= e($q) ?></strong>" &bull; <span><?= $totalBlogs ?> article<?= $totalBlogs === 1 ? '' : 's' ?> found</span>
            <?php elseif ($totalPages > 1): ?>
                Showing <strong class="highlight"><?= $fromIndex ?>–<?= $toIndex ?></strong> of <strong class="highlight"><?= $totalBlogs ?></strong> articles
            <?php else: ?>
                <strong class="highlight"><?= $totalBlogs ?></strong> article<?= $totalBlogs === 1 ? '' : 's' ?> available
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="blog-meta-badge">
                Page <?= $page ?> of <?= $totalPages ?>
            </div>
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
                <article class="card blog-card">
                    <a href="blog-detail.php?slug=<?= urlencode($b['slug']) ?>" class="blog-card-media">
                        <?php if (!empty($b['image_url'])): ?>
                            <img src="<?= e($b['image_url']) ?>" alt="<?= e($b['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80" alt="Article" loading="lazy">
                        <?php endif; ?>
                    </a>
                    
                    <div class="blog-card-body">
                        <div class="blog-card-meta">
                            <span>🗓️ <?= date('M d, Y', strtotime($b['created_at'])) ?></span>
                            <span>&middot;</span>
                            <span>✍️ <?= e($b['author']) ?></span>
                        </div>
                        
                        <h3 class="blog-card-title">
                            <a href="blog-detail.php?slug=<?= urlencode($b['slug']) ?>">
                                <?= e($b['title']) ?>
                            </a>
                        </h3>
                        
                        <p class="blog-card-excerpt">
                            <?= e(mb_strimwidth($b['excerpt'] ?: strip_tags($b['content']), 0, 130, '...')) ?>
                        </p>
                        
                        <div style="margin-top:auto;">
                            <a href="blog-detail.php?slug=<?= urlencode($b['slug']) ?>" class="blog-read-btn">
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
