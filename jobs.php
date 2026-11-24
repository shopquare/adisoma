<?php
$title = 'Job Openings';
$sub = 'Search and apply for current opportunities';
include 'includes/header.php';

// Sanitize filter inputs
$q    = trim($_GET['q'] ?? '');
$cat  = trim($_GET['category'] ?? '');
$loc  = trim($_GET['location'] ?? '');
$type = trim($_GET['type'] ?? '');

$w = ["status = 'published'"];
$a = [];

if ($q !== '') {
    $w[] = '(title LIKE ? OR description LIKE ?)';
    $a[] = "%$q%";
    $a[] = "%$q%";
}
if (in_array($cat, ['IT', 'Non-IT'])) {
    $w[] = 'category = ?';
    $a[] = $cat;
}
if ($loc !== '') {
    $w[] = 'location LIKE ?';
    $a[] = "%$loc%";
}
if ($type !== '') {
    $w[] = 'job_type = ?';
    $a[] = $type;
}

$whereClause = implode(' AND ', $w);

// Pagination parameters (10 jobs per page)
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));

// Count total matching jobs
$countStmt = db()->prepare("SELECT COUNT(*) FROM jobs WHERE $whereClause");
$countStmt->execute($a);
$totalJobs = (int)$countStmt->fetchColumn();

$totalPages = max(1, (int)ceil($totalJobs / $perPage));
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

// Fetch page slice using integer cast for safe LIMIT/OFFSET
$sql = "SELECT * FROM jobs WHERE $whereClause ORDER BY id DESC LIMIT $perPage OFFSET $offset";
$st = db()->prepare($sql);
$st->execute($a);
$jobs = $st->fetchAll();

// URL builder preserving current filters
if (!function_exists('job_page_url')) {
    function job_page_url(int $targetPage): string {
        $params = $_GET;
        $params['page'] = $targetPage;
        return 'jobs.php?' . http_build_query($params);
    }
}

$fromIndex = $totalJobs > 0 ? $offset + 1 : 0;
$toIndex   = min($offset + $perPage, $totalJobs);
?>

<section class="s"><div class="wrap">
    <!-- Filter Bar -->
    <form class="filters" method="get">
        <input name="q" placeholder="Keyword (e.g. Developer, HR)" value="<?= e($q) ?>">
        <input name="location" placeholder="Location (e.g. Mumbai, Remote)" value="<?= e($loc) ?>">
        
        <select name="category">
            <option value="">All Categories</option>
            <?php foreach (['IT', 'Non-IT'] as $c): ?>
                <option value="<?= $c ?>" <?= $cat === $c ? 'selected' : '' ?>><?= $c ?></option>
            <?php endforeach; ?>
        </select>
        
        <select name="type">
            <option value="">All Types</option>
            <?php foreach (['Full-time', 'Part-time', 'Contract', 'Internship'] as $t): ?>
                <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
        </select>
        
        <button class="btn" style="margin:0;">Search</button>
        <?php if ($q !== '' || $cat !== '' || $loc !== '' || $type !== ''): ?>
            <a href="jobs.php" class="btn alt" style="margin:0; text-decoration:none;">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Results Meta / Summary Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
        <p style="margin:0; font-size:15px; color:#64748b; font-weight:600;">
            <?php if ($totalJobs === 0): ?>
                No jobs found matching your criteria
            <?php elseif ($totalPages > 1): ?>
                Showing <strong style="color:var(--n);"><?= $fromIndex ?>–<?= $toIndex ?></strong> of <strong style="color:var(--n);"><?= $totalJobs ?></strong> job openings
            <?php else: ?>
                <strong style="color:var(--n);"><?= $totalJobs ?></strong> job opening<?= $totalJobs === 1 ? '' : 's' ?> available
            <?php endif; ?>
        </p>
        
        <?php if ($totalPages > 1): ?>
            <span style="font-size:13px; color:#64748b; background:#f1f5f9; padding:4px 10px; border-radius:6px; font-weight:600;">
                Page <?= $page ?> of <?= $totalPages ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- Jobs Listing -->
    <?php if (empty($jobs)): ?>
        <div style="text-align:center; padding:60px 20px; background:#fff; border-radius:16px; border:1px solid #e2e8f0; margin-top:20px;">
            <div style="font-size:48px; margin-bottom:15px;">💼</div>
            <h3 style="color:var(--n); margin-bottom:8px;">No Matching Jobs Found</h3>
            <p style="color:#64748b; max-width:500px; margin:0 auto 25px; line-height:1.6;">
                We could not find any active job openings matching your search criteria. Try modifying your search filters or send us your resume.
            </p>
            <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <a href="jobs.php" class="btn sm">View All Openings</a>
                <a href="apply.php" class="btn sm alt">Submit General Application</a>
            </div>
        </div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:18px;">
            <?php foreach ($jobs as $j): ?>
                <div class="job" style="margin-bottom:0;">
                    <div>
                        <h3 style="margin:0 0 8px; font-size:20px; color:var(--n);">
                            <?= e($j['title']) ?>
                        </h3>
                        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px;">
                            <?php if (!empty($j['category'])): ?>
                                <span class="tag" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd;"><?= e($j['category']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($j['location'])): ?>
                                <span class="tag">📍 <?= e($j['location']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($j['job_type'])): ?>
                                <span class="tag">⏱️ <?= e($j['job_type']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($j['experience'])): ?>
                                <span class="tag">💼 <?= e($j['experience']) ?></span>
                            <?php endif; ?>
                        </div>
                        <p style="margin:0; color:#5b6475; font-size:14px; line-height:1.6;">
                            <?= e(mb_strimwidth(strip_tags($j['description']), 0, 180, '...')) ?>
                        </p>
                    </div>
                    <div style="flex-shrink:0;">
                        <a class="btn sm" href="apply.php?id=<?= $j['id'] ?>" style="white-space:nowrap;">Apply Now ➔</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Responsive Pagination (Rendered strictly if more than 10 jobs) -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-wrap">
                <nav aria-label="Job Openings Pagination">
                    <ul class="pagination">
                        <!-- Prev Page Button -->
                        <li>
                            <?php if ($page > 1): ?>
                                <a href="<?= e(job_page_url($page - 1)) ?>" class="page-link" aria-label="Previous Page">
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
                                    <a href="<?= e(job_page_url($p)) ?>" class="page-link"><?= $p ?></a>
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

                        <!-- Next Page Button -->
                        <li>
                            <?php if ($page < $totalPages): ?>
                                <a href="<?= e(job_page_url($page + 1)) ?>" class="page-link" aria-label="Next Page">
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
                    Showing <?= $fromIndex ?>–<?= $toIndex ?> of <?= $totalJobs ?> total openings
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div></section>

<?php include 'includes/footer.php'; ?>
