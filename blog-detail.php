<?php
require_once 'includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$id = (int)($_GET['id'] ?? 0);

$blog = null;
if ($slug !== '') {
    $stmt = db()->prepare("SELECT * FROM blogs WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    $blog = $stmt->fetch();
} elseif ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM blogs WHERE id = ? AND status = 'published'");
    $stmt->execute([$id]);
    $blog = $stmt->fetch();
}

if (!$blog) {
    redirect('blogs.php');
}

$title = $blog['title'];
$sub = 'Published on ' . date('F d, Y', strtotime($blog['created_at'])) . ' by ' . $blog['author'];
include 'includes/header.php';

// Format content helper (handles markdown style headings, bold text, and paragraphs)
function render_article_content($text) {
    $lines = explode("\n", $text);
    $html = '';
    $inPara = false;

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') {
            if ($inPara) {
                $html .= "</p>\n";
                $inPara = false;
            }
            continue;
        }

        // Headings
        if (str_starts_with($trimmed, '### ')) {
            if ($inPara) { $html .= "</p>\n"; $inPara = false; }
            $html .= '<h3 style="color:var(--n); margin: 30px 0 12px; font-size:22px;">' . e(substr($trimmed, 4)) . "</h3>\n";
        } elseif (str_starts_with($trimmed, '## ')) {
            if ($inPara) { $html .= "</p>\n"; $inPara = false; }
            $html .= '<h2 style="color:var(--n); margin: 35px 0 15px; font-size:26px;">' . e(substr($trimmed, 3)) . "</h2>\n";
        } elseif (str_starts_with($trimmed, '- ') || str_starts_with($trimmed, '* ')) {
            if ($inPara) { $html .= "</p>\n"; $inPara = false; }
            $html .= '<li style="margin-left: 20px; margin-bottom: 8px; color:#475569;">' . e(substr($trimmed, 2)) . "</li>\n";
        } else {
            if (!$inPara) {
                $html .= '<p style="margin-bottom: 20px; line-height: 1.85; font-size: 16px; color:#334155;">';
                $inPara = true;
            } else {
                $html .= "<br>";
            }
            $html .= e($trimmed);
        }
    }

    if ($inPara) {
        $html .= "</p>\n";
    }

    return $html;
}

// Fetch related/recent blogs
$stmt = db()->prepare("SELECT * FROM blogs WHERE status = 'published' AND id != ? ORDER BY id DESC LIMIT 3");
$stmt->execute([$blog['id']]);
$related = $stmt->fetchAll();
?>

<article class="s"><div class="wrap" style="max-width: 860px;">
    <!-- Breadcrumb & Back button -->
    <div style="margin-bottom: 25px;">
        <a href="blogs.php" class="btn sm alt">← Back to All Articles</a>
    </div>

    <!-- Featured Cover Image -->
    <?php if (!empty($blog['image_url'])): ?>
        <div style="margin-bottom: 35px; border-radius: 18px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
            <img src="<?= e($blog['image_url']) ?>" alt="<?= e($blog['title']) ?>" style="width:100%; max-height:450px; object-fit:cover; display:block;">
        </div>
    <?php endif; ?>

    <!-- Article Content Card -->
    <div style="background:#fff; padding: 45px; border-radius: 20px; box-shadow: 0 4px 25px rgba(0,0,0,0.04); margin-bottom: 50px;">
        <!-- Metadata -->
        <div style="display:flex; align-items:center; gap:15px; flex-wrap:wrap; margin-bottom:25px; padding-bottom:15px; border-bottom:1px solid #f1f5f9; font-size:14px; color:#64748b;">
            <span>✍️ <b>Author:</b> <?= e($blog['author']) ?></span>
            <span>&middot;</span>
            <span>🗓️ <b>Date:</b> <?= date('F d, Y', strtotime($blog['created_at'])) ?></span>
        </div>

        <?php if (!empty($blog['excerpt'])): ?>
            <div style="font-size: 18px; font-weight: 500; color: #1e293b; line-height: 1.7; margin-bottom: 30px; padding: 18px 22px; background: #fff8f0; border-left: 4px solid var(--o); border-radius: 0 10px 10px 0;">
                <?= nl2br(e($blog['excerpt'])) ?>
            </div>
        <?php endif; ?>

        <!-- Body Content -->
        <div class="article-body">
            <?= render_article_content($blog['content']) ?>
        </div>

        <!-- Hiring Callout Box -->
        <div style="margin-top: 45px; padding: 30px; background: #f8fafc; border-radius: 14px; border: 1px solid #e2e8f0; text-align: center;">
            <h3 style="color:var(--n); margin-top:0; margin-bottom:8px;">Ready to Elevate Your Hiring Strategy?</h3>
            <p style="color:#64748b; margin-bottom:20px; font-size:15px;">Connect with ADISOMA Solution today to find top IT and Non-IT talent tailored to your company's growth.</p>
            <div class="btn-group">
                <a href="employers.php" class="btn">Hire Talent</a>
                <a href="contact.php" class="btn alt">Contact Us</a>
            </div>
        </div>
    </div>

    <!-- Related Articles -->
    <?php if (!empty($related)): ?>
        <div style="margin-top: 60px;">
            <h2 style="color:var(--n); font-size:26px; margin-bottom:25px; text-align:center;">More Insights</h2>
            <div class="grid3">
                <?php foreach ($related as $r): ?>
                    <div class="card" style="padding:0; overflow:hidden;">
                        <?php if (!empty($r['image_url'])): ?>
                            <img src="<?= e($r['image_url']) ?>" alt="" style="width:100%; height:150px; object-fit:cover; margin-bottom:0;">
                        <?php else: ?>
                            <div style="width:100%; height:150px; background:#e2e8f0; display:flex; align-items:center; justify-content:center;">📰</div>
                        <?php endif; ?>
                        <div style="padding:18px;">
                            <h4 style="margin:0 0 8px; font-size:16px;">
                                <a href="blog-detail.php?slug=<?= urlencode($r['slug']) ?>" style="color:var(--n);">
                                    <?= e(mb_strimwidth($r['title'], 0, 55, '...')) ?>
                                </a>
                            </h4>
                            <a href="blog-detail.php?slug=<?= urlencode($r['slug']) ?>" style="font-size:13px; font-weight:600;">Read More ➔</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div></article>

<?php include 'includes/footer.php'; ?>
