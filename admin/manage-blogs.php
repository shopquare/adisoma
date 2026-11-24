<?php
require_once __DIR__ . '/../includes/functions.php';
admin_only();

// Helper to sanitize/generate slug
function make_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'post-' . time());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) die('Bad token');
    $a = post('action');
    $id = (int)post('id');

    if ($a === 'save') {
        $title = post('title');
        $slug = post('slug') !== '' ? make_slug(post('slug')) : make_slug($title);
        $excerpt = post('excerpt');
        $content = post('content');
        $author = post('author') ?: 'ADISOMA Team';
        $status = post('status') === 'published' ? 'published' : 'draft';
        $image_url = post('image_url');

        if ($title === '' || $content === '') {
            flash('Title and content are required.', 'err');
            redirect('manage-blogs.php' . ($id ? "?edit=$id" : '?new=1'));
        }

        // Image upload handling
        if (!empty($_FILES['image_upload']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['image_upload']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $fname = 'blog_' . uniqid() . '.' . $ext;
                $targetDir = __DIR__ . '/../uploads/images/';
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $targetDir . $fname)) {
                    $image_url = BASE_URL . '/uploads/images/' . $fname;
                }
            }
        }

        // Check unique slug (except current id)
        $chk = db()->prepare('SELECT id FROM blogs WHERE slug = ? AND id != ?');
        $chk->execute([$slug, $id]);
        if ($chk->fetch()) {
            $slug .= '-' . time();
        }

        if ($id) {
            $stmt = db()->prepare('UPDATE blogs SET title=?, slug=?, excerpt=?, content=?, image_url=?, author=?, status=? WHERE id=?');
            $stmt->execute([$title, $slug, $excerpt, $content, $image_url, $author, $status, $id]);
            log_action('Updated', 'Blog', $id, $title);
            flash('Blog post updated.');
        } else {
            $stmt = db()->prepare('INSERT INTO blogs(title, slug, excerpt, content, image_url, author, status) VALUES(?,?,?,?,?,?,?)');
            $stmt->execute([$title, $slug, $excerpt, $content, $image_url, $author, $status]);
            $newId = db()->lastInsertId();
            log_action('Created', 'Blog', $newId, $title);
            flash('Blog post created.');
        }
    }

    if ($a === 'toggle') {
        db()->prepare("UPDATE blogs SET status=IF(status='published','draft','published') WHERE id=?")->execute([$id]);
        log_action('Toggled Status', 'Blog', $id);
        flash('Blog status updated.');
    }

    if ($a === 'delete') {
        $stmt = db()->prepare('SELECT title FROM blogs WHERE id=?');
        $stmt->execute([$id]);
        $title = $stmt->fetchColumn();

        db()->prepare('DELETE FROM blogs WHERE id=?')->execute([$id]);
        log_action('Deleted', 'Blog', $id, $title ?: "ID $id");
        flash('Blog post deleted.');
    }

    redirect('manage-blogs.php');
}

include '_header.php';

if (isset($_GET['new']) || isset($_GET['edit'])) {
    $b = [
        'id' => 0,
        'title' => '',
        'slug' => '',
        'excerpt' => '',
        'content' => '',
        'image_url' => '',
        'author' => 'ADISOMA Team',
        'status' => 'published'
    ];

    if (isset($_GET['edit'])) {
        $stmt = db()->prepare('SELECT * FROM blogs WHERE id=?');
        $stmt->execute([(int)$_GET['edit']]);
        $b = $stmt->fetch() ?: $b;
    }
?>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="margin:0;"><?= $b['id'] ? 'Edit Blog Post' : 'Add New Blog Post' ?></h1>
        <a href="manage-blogs.php" class="btn sm alt">← Back to Blogs</a>
    </div>

    <form class="at" method="post" enctype="multipart/form-data">
        <?= csrf() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $b['id'] ?>">

        <label>Title *</label>
        <input name="title" required value="<?= e($b['title']) ?>" placeholder="e.g. Navigating the Future of Tech Hiring">

        <label>Slug (URL Identifier - optional, auto-generated)</label>
        <input name="slug" value="<?= e($b['slug']) ?>" placeholder="e.g. navigating-future-of-tech-hiring">

        <label>Author</label>
        <input name="author" value="<?= e($b['author']) ?>" placeholder="e.g. ADISOMA Editorial Team">

        <label>Short Excerpt (Summary for blog cards)</label>
        <textarea name="excerpt" rows="3" placeholder="Brief summary to display in blog listings..."><?= e($b['excerpt']) ?></textarea>

        <label>Featured Image URL (Paste link or upload below)</label>
        <div style="display:flex; gap:10px; align-items:center;">
            <input type="text" name="image_url" id="blog_image_field" value="<?= e($b['image_url']) ?>" placeholder="https://images.unsplash.com/...">
            <button type="button" class="btn sm alt" style="margin:0; white-space:nowrap;" onclick="navigator.clipboard.writeText(document.getElementById('blog_image_field').value); alert('URL Copied!')">Copy</button>
        </div>

        <label style="margin-top:12px;">Upload New Image</label>
        <input type="file" name="image_upload" accept="image/*">
        <?php if (!empty($b['image_url'])): ?>
            <div style="margin-top:10px;">
                <img src="<?= e($b['image_url']) ?>" style="max-height:100px; border-radius:8px; border:1px solid #ddd;" alt="Preview">
            </div>
        <?php endif; ?>

        <label>Full Content * (Supports standard text & line breaks)</label>
        <textarea name="content" rows="12" required placeholder="Write the full blog article content here..."><?= e($b['content']) ?></textarea>

        <label>Status</label>
        <select name="status">
            <option value="published" <?= $b['status'] === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $b['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
        </select>

        <div class="btn-group btn-group-left" style="margin-top:25px;">
            <button class="btn" type="submit"><?= $b['id'] ? 'Update Blog Post' : 'Save & Publish' ?></button>
            <a class="btn alt" href="manage-blogs.php">Cancel</a>
        </div>
    </form>

<?php
} else {
    $blogs = db()->query('SELECT * FROM blogs ORDER BY id DESC')->fetchAll();
?>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
        <h1 style="margin:0;">Manage Blogs (<?= count($blogs) ?>)</h1>
        <a class="btn sm" href="?new=1">+ Add New Blog</a>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:70px;">Image</th>
                <th>Title</th>
                <th>Author</th>
                <th>Status</th>
                <th>Created</th>
                <th style="width:220px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($blogs)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:30px; color:#64748b;">No blog articles yet. Click "+ Add New Blog" to create your first post!</td>
                </tr>
            <?php else: ?>
                <?php foreach ($blogs as $r): ?>
                    <tr>
                        <td>
                            <?php if (!empty($r['image_url'])): ?>
                                <img src="<?= e($r['image_url']) ?>" style="width:55px; height:45px; object-fit:cover; border-radius:6px;" alt="">
                            <?php else: ?>
                                <div style="width:55px; height:45px; background:#e2e8f0; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:18px;">📰</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <b><?= e($r['title']) ?></b>
                            <div style="font-size:12px; color:#64748b; margin-top:3px;">
                                Slug: <code style="background:#f1f5f9; padding:2px 6px; border-radius:4px;"><?= e($r['slug']) ?></code>
                            </div>
                        </td>
                        <td><?= e($r['author']) ?></td>
                        <td>
                            <?php if ($r['status'] === 'published'): ?>
                                <span class="tag" style="background:#ecfdf5; color:#059669; margin:0;">Published</span>
                            <?php else: ?>
                                <span class="tag" style="background:#fffbeb; color:#d97706; margin:0;">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap;"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn sm alt" href="?edit=<?= $r['id'] ?>">Edit</a>
                                <?php if ($r['status'] === 'published'): ?>
                                    <a class="btn sm alt" href="<?= BASE_URL ?>/blog-detail.php?slug=<?= urlencode($r['slug']) ?>" target="_blank" title="View live">View</a>
                                <?php endif; ?>
                                <form class="inl" method="post">
                                    <?= csrf() ?>
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <button class="btn sm" name="action" value="toggle">
                                        <?= $r['status'] === 'published' ? 'Draft' : 'Publish' ?>
                                    </button>
                                    <button class="btn sm red" name="action" value="delete" onclick="return confirm('Are you sure you want to delete this blog post?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
<?php
}
include '_footer.php';
?>
