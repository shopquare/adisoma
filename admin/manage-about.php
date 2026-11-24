<?php
require_once __DIR__ . '/../includes/functions.php';
admin_only();
ensure_about_sections_table();

$uploadDir = __DIR__ . '/../uploads/about/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        die('Invalid session token. Please reload and retry.');
    }

    $action = post('action');

    // 1. Update Page Banner Settings
    if ($action === 'save_banner') {
        $p_title = clean_input(post('about_page_title') ?: 'About Us');
        $p_sub = clean_input(post('about_page_subtitle') ?: 'Your Talent Partner for a Brighter Tomorrow');

        $stmt = db()->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->execute(['about_page_title', $p_title]);
        $stmt->execute(['about_page_subtitle', $p_sub]);

        log_action('Updated', 'About Banner', 0, "Title: $p_title");
        clear_all_cache();
        flash('About page hero banner updated successfully.');
        redirect('manage-about.php');
    }

    // 2. Save Section (Add or Edit)
    if ($action === 'save_section') {
        $id = (int)post('id');
        $title = clean_input(post('title'));
        $subtitle = clean_input(post('subtitle'));
        $section_type = clean_input(post('section_type') ?: 'text_image_right');
        $content = post('content');
        $image_url = clean_input(post('image_url'));
        $btn_text = clean_input(post('btn_text'));
        $btn_url = clean_input(post('btn_url'));
        $bg_style = clean_input(post('bg_style') ?: 'white');
        $sort_order = (int)post('sort_order');
        $active = isset($_POST['active']) ? 1 : 0;

        if ($title === '') {
            flash('Section title is required.', 'err');
            redirect('manage-about.php' . ($id ? "?edit=$id" : '?new=1'));
        }

        // Process File Upload if provided
        if (!empty($_FILES['image_upload']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['image_upload']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['image_upload']['tmp_name']);
                finfo_close($finfo);

                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
                if (in_array($mime, $allowedMimes)) {
                    $newFileName = 'about_' . bin2hex(random_bytes(8)) . '.' . $ext;
                    if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $uploadDir . $newFileName)) {
                        $image_url = BASE_URL . '/uploads/about/' . $newFileName;
                    }
                } else {
                    flash('Invalid image format detected.', 'err');
                }
            } else {
                flash('Please upload a valid JPG, PNG, WEBP, or SVG image.', 'err');
            }
        }

        // Process Cards Grid items if submitted
        $items = [];
        if (isset($_POST['card_title']) && is_array($_POST['card_title'])) {
            $c_titles = $_POST['card_title'];
            $c_descs  = $_POST['card_desc'] ?? [];
            $c_images = $_POST['card_image'] ?? [];

            for ($i = 0; $i < count($c_titles); $i++) {
                $ct = trim($c_titles[$i] ?? '');
                $cd = trim($c_descs[$i] ?? '');
                $ci = trim($c_images[$i] ?? '');

                if ($ct !== '' || $cd !== '') {
                    $items[] = [
                        'title' => $ct,
                        'desc'  => $cd,
                        'image' => $ci
                    ];
                }
            }
        }
        $items_json = !empty($items) ? json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

        if ($id > 0) {
            $stmt = db()->prepare("
                UPDATE about_sections 
                SET title = ?, subtitle = ?, section_type = ?, content = ?, image_url = ?, items_json = ?, btn_text = ?, btn_url = ?, bg_style = ?, sort_order = ?, active = ? 
                WHERE id = ?
            ");
            $stmt->execute([$title, $subtitle, $section_type, $content, $image_url, $items_json, $btn_text, $btn_url, $bg_style, $sort_order, $active, $id]);
            log_action('Updated', 'AboutSection', $id, $title);
            flash('Section updated successfully.');
        } else {
            // Auto calculate sort_order if 0
            if ($sort_order === 0) {
                $maxSort = (int)db()->query("SELECT MAX(sort_order) FROM about_sections")->fetchColumn();
                $sort_order = $maxSort + 1;
            }
            $stmt = db()->prepare("
                INSERT INTO about_sections (title, subtitle, section_type, content, image_url, items_json, btn_text, btn_url, bg_style, sort_order, active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $subtitle, $section_type, $content, $image_url, $items_json, $btn_text, $btn_url, $bg_style, $sort_order, $active]);
            $newId = (int)db()->lastInsertId();
            log_action('Created', 'AboutSection', $newId, $title);
            flash('New section added to About page successfully.');
        }

        clear_all_cache();
        redirect('manage-about.php');
    }

    // 3. Delete Section
    if ($action === 'delete_section') {
        $id = (int)post('id');
        if ($id > 0) {
            $st = db()->prepare("SELECT title FROM about_sections WHERE id = ?");
            $st->execute([$id]);
            $secTitle = $st->fetchColumn();

            db()->prepare("DELETE FROM about_sections WHERE id = ?")->execute([$id]);
            log_action('Deleted', 'AboutSection', $id, (string)$secTitle);
            clear_all_cache();
            flash('Section deleted successfully.');
        }
        redirect('manage-about.php');
    }

    // 4. Toggle Active
    if ($action === 'toggle_active') {
        $id = (int)post('id');
        if ($id > 0) {
            db()->prepare("UPDATE about_sections SET active = 1 - active WHERE id = ?")->execute([$id]);
            clear_all_cache();
            flash('Section visibility status updated.');
        }
        redirect('manage-about.php');
    }

    // 5. Quick Reorder: Move Up / Down
    if ($action === 'move_order') {
        $id = (int)post('id');
        $dir = post('direction'); // 'up' or 'down'

        $curr = db()->prepare("SELECT id, sort_order FROM about_sections WHERE id = ?");
        $curr->execute([$id]);
        $row = $curr->fetch();

        if ($row) {
            $cSort = (int)$row['sort_order'];
            if ($dir === 'up') {
                $target = db()->prepare("SELECT id, sort_order FROM about_sections WHERE sort_order < ? ORDER BY sort_order DESC LIMIT 1");
                $target->execute([$cSort]);
            } else {
                $target = db()->prepare("SELECT id, sort_order FROM about_sections WHERE sort_order > ? ORDER BY sort_order ASC LIMIT 1");
                $target->execute([$cSort]);
            }
            $tRow = $target->fetch();
            if ($tRow) {
                db()->prepare("UPDATE about_sections SET sort_order = ? WHERE id = ?")->execute([$tRow['sort_order'], $row['id']]);
                db()->prepare("UPDATE about_sections SET sort_order = ? WHERE id = ?")->execute([$row['sort_order'], $tRow['id']]);
                clear_all_cache();
                flash('Section display order updated.');
            }
        }
        redirect('manage-about.php');
    }
}

include '_header.php';

$settings = get_site_settings();
$banner_title = $settings['about_page_title'] ?? 'About Us';
$banner_sub   = $settings['about_page_subtitle'] ?? 'Your Talent Partner for a Brighter Tomorrow';

// Fetch existing sections
$sections = get_about_sections(false);

// Edit Section State
$edit = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($sections as $s) {
        if ((int)$s['id'] === $editId) {
            $edit = $s;
            break;
        }
    }
}

$isNew = isset($_GET['new']) || ($edit === null && isset($_GET['add']));

// Human readable layout designations
$layoutNames = [
    'text_image_right' => '📸 Text Left + Image Right',
    'text_image_left'  => '🖼️ Image Left + Text Right',
    'cards_grid'       => '🗂️ Cards Grid (2-4 Cards)',
    'full_width'       => '📜 Full-Width Narrative',
    'cta_banner'       => '🚀 Call-to-Action Banner'
];
?>

<div class="dash-head">
    <div>
        <h1 style="margin:0 0 6px;">About Us Page Customizer</h1>
        <span style="color:#64748b; font-size:14px;">Add, customize, reorder, or delete any section on your public About page</span>
    </div>
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/about.php" target="_blank" class="btn sm" style="background:#0284c7; border-color:#0284c7; color:#fff !important; font-weight:600;">
            View Live About Page ➔
        </a>
        <?php if (!$isNew && !$edit): ?>
            <a href="manage-about.php?new=1#sectionForm" class="btn sm" style="font-weight:600;">
                + Add New Section
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Page Hero Banner Customizer Card -->
<div class="card" style="margin-bottom:30px; border-left:4px solid var(--o);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
        <div>
            <h3 style="margin:0 0 4px; font-size:18px; color:var(--n);">Top Hero Banner Settings</h3>
            <span style="font-size:13px; color:#64748b;">Controls the headline and subtitle banner at the very top of <a href="<?= BASE_URL ?>/about.php" target="_blank" style="color:var(--o); font-weight:600;">about.php</a></span>
        </div>
        <button type="button" class="btn sm alt" onclick="var el=document.getElementById('bannerFormWrap'); el.style.display=(el.style.display==='none'?'block':'none');">
            ✏️ Edit Banner
        </button>
    </div>

    <!-- Live Preview Pill -->
    <div style="background:#1e293b; color:#fff; border-radius:10px; padding:20px 25px; margin-bottom:15px; text-align:center;">
        <h2 style="margin:0 0 6px; font-size:26px; color:#fff; font-weight:800;"><?= e($banner_title) ?></h2>
        <p style="margin:0; font-size:15px; color:rgba(255,255,255,0.85);"><?= e($banner_sub) ?></p>
    </div>

    <div id="bannerFormWrap" style="display:none; padding-top:15px; border-top:1px solid #e2e8f0;">
        <form method="post">
            <?= csrf() ?>
            <input type="hidden" name="action" value="save_banner">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:15px; margin-bottom:15px;">
                <div>
                    <label style="font-weight:600; font-size:13px; color:#1e293b; display:block; margin-bottom:6px;">Page Hero Title</label>
                    <input type="text" name="about_page_title" value="<?= e($banner_title) ?>" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px;">
                </div>
                <div>
                    <label style="font-weight:600; font-size:13px; color:#1e293b; display:block; margin-bottom:6px;">Page Hero Subtitle</label>
                    <input type="text" name="about_page_subtitle" value="<?= e($banner_sub) ?>" required style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px;">
                </div>
            </div>
            <button class="btn sm" type="submit" style="font-weight:600;">Save Banner Settings</button>
        </form>
    </div>
</div>

<!-- Section Manager Table / List -->
<div class="card" style="margin-bottom:35px; padding:0; overflow:hidden;">
    <div style="padding:20px 24px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; background:#fafafa;">
        <div>
            <h3 style="margin:0 0 4px; font-size:18px; color:var(--n);">Current About Page Sections (<?= count($sections) ?>)</h3>
            <span style="font-size:13px; color:#64748b;">Drag or use Up/Down arrows to reorder. Toggle visibility or delete anytime.</span>
        </div>
        <a href="manage-about.php?new=1#sectionForm" class="btn sm" style="font-weight:600;">
            + Add New Section
        </a>
    </div>

    <?php if (empty($sections)): ?>
        <div style="padding:40px; text-align:center; color:#64748b;">
            <p style="font-size:16px; margin-bottom:15px;">No sections found on the About page.</p>
            <a href="manage-about.php?new=1" class="btn">Create Your First Section</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table style="width:100%; border-collapse:collapse; text-align:left;">
                <thead>
                    <tr style="background:#f1f5f9; color:#475569; font-size:13px; text-transform:uppercase; letter-spacing:0.5px; border-bottom:1px solid #cbd5e1;">
                        <th style="padding:14px 18px; width:70px;">Order</th>
                        <th style="padding:14px 18px;">Section Details</th>
                        <th style="padding:14px 18px; width:190px;">Layout Style</th>
                        <th style="padding:14px 18px; width:100px;">Background</th>
                        <th style="padding:14px 18px; width:100px; text-align:center;">Status</th>
                        <th style="padding:14px 18px; width:190px; text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalSec = count($sections);
                    foreach ($sections as $idx => $sec): 
                        $isFirst = ($idx === 0);
                        $isLast  = ($idx === $totalSec - 1);
                    ?>
                    <tr style="border-bottom:1px solid #e2e8f0; background:<?= ($edit && $edit['id'] == $sec['id']) ? '#fff7ed' : '#ffffff' ?>; transition:background 0.2s;">
                        <td style="padding:16px 18px; vertical-align:middle;">
                            <div style="display:flex; align-items:center; gap:4px;">
                                <span style="display:inline-block; font-weight:700; color:var(--n); background:#f1f5f9; border:1px solid #cbd5e1; border-radius:6px; padding:3px 7px; font-size:12px;">
                                    #<?= e($sec['sort_order']) ?>
                                </span>
                                <div style="display:flex; flex-direction:column; gap:2px;">
                                    <?php if (!$isFirst): ?>
                                    <form method="post" style="margin:0;">
                                        <?= csrf() ?>
                                        <input type="hidden" name="action" value="move_order">
                                        <input type="hidden" name="id" value="<?= $sec['id'] ?>">
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" title="Move Up" style="background:none; border:none; cursor:pointer; font-size:11px; padding:0; line-height:1;">⬆️</button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if (!$isLast): ?>
                                    <form method="post" style="margin:0;">
                                        <?= csrf() ?>
                                        <input type="hidden" name="action" value="move_order">
                                        <input type="hidden" name="id" value="<?= $sec['id'] ?>">
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" title="Move Down" style="background:none; border:none; cursor:pointer; font-size:11px; padding:0; line-height:1;">⬇️</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="padding:16px 18px; vertical-align:middle;">
                            <?php if (!empty($sec['subtitle'])): ?>
                                <span style="display:block; font-size:11px; font-weight:700; color:var(--o); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">
                                    <?= e($sec['subtitle']) ?>
                                </span>
                            <?php endif; ?>
                            <strong style="font-size:16px; color:#0f172a; display:block; margin-bottom:4px;">
                                <?= e($sec['title']) ?>
                            </strong>
                            <?php if (!empty($sec['image_url'])): ?>
                                <div style="display:flex; align-items:center; gap:8px; margin-top:4px;">
                                    <img src="<?= e($sec['image_url']) ?>" alt="Thumbnail" style="width:38px; height:26px; object-fit:cover; border-radius:4px; border:1px solid #cbd5e1;">
                                    <span style="font-size:12px; color:#64748b;">Has featured media</span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($sec['items'])): ?>
                                <span style="display:inline-block; font-size:12px; color:#0284c7; background:#e0f2fe; padding:2px 8px; border-radius:4px; margin-top:4px;">
                                    🗂️ <?= count($sec['items']) ?> card items configured
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:16px 18px; vertical-align:middle;">
                            <span style="font-size:13px; font-weight:600; color:#334155;">
                                <?= $layoutNames[$sec['section_type']] ?? e($sec['section_type']) ?>
                            </span>
                        </td>
                        <td style="padding:16px 18px; vertical-align:middle;">
                            <?php 
                            $bgColors = ['white' => 'White', 'light' => 'Light Gray', 'accent' => 'Accent Gradient'];
                            ?>
                            <span style="font-size:12px; font-weight:500; color:#475569; background:#f8fafc; border:1px solid #e2e8f0; padding:3px 8px; border-radius:4px;">
                                <?= $bgColors[$sec['bg_style']] ?? e($sec['bg_style']) ?>
                            </span>
                        </td>
                        <td style="padding:16px 18px; vertical-align:middle; text-align:center;">
                            <form method="post" style="margin:0; display:inline;">
                                <?= csrf() ?>
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="id" value="<?= $sec['id'] ?>">
                                <button type="submit" style="background:none; border:none; cursor:pointer;" title="Click to toggle visibility">
                                    <?php if ($sec['active']): ?>
                                        <span style="background:#dcfce7; color:#15803d; font-weight:700; font-size:12px; padding:3px 10px; border-radius:20px; border:1px solid #bbf7d0;">Active</span>
                                    <?php else: ?>
                                        <span style="background:#fee2e2; color:#b91c1c; font-weight:700; font-size:12px; padding:3px 10px; border-radius:20px; border:1px solid #fecaca;">Hidden</span>
                                    <?php endif; ?>
                                </button>
                            </form>
                        </td>
                        <td style="padding:16px 18px; vertical-align:middle; text-align:right;">
                            <div style="display:flex; justify-content:flex-end; gap:8px;">
                                <a href="manage-about.php?edit=<?= $sec['id'] ?>#sectionForm" class="btn sm alt" style="padding:5px 12px; font-size:12px;">
                                    ✏️ Edit
                                </a>
                                <form method="post" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete section &quot;<?= addslashes(e($sec['title'])) ?>&quot;?');">
                                    <?= csrf() ?>
                                    <input type="hidden" name="action" value="delete_section">
                                    <input type="hidden" name="id" value="<?= $sec['id'] ?>">
                                    <button class="btn sm" type="submit" style="background:#ef4444; border-color:#ef4444; color:#fff !important; padding:5px 10px; font-size:12px;">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Section Form (Add New or Edit) -->
<div id="sectionForm" class="card" style="border-top:4px solid var(--o); margin-bottom:50px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #e2e8f0; padding-bottom:15px; flex-wrap:wrap; gap:10px;">
        <div>
            <h2 style="margin:0 0 4px; font-size:22px; color:var(--n);">
                <?= $edit ? '✏️ Edit Section: ' . e($edit['title']) : '➕ Add New Section to About Page' ?>
            </h2>
            <span style="color:#64748b; font-size:14px;">Configure the content, layout, styling, and child cards for this section</span>
        </div>
        <?php if ($edit || $isNew): ?>
            <a href="manage-about.php" class="btn sm alt" style="font-weight:600;">Cancel / Back to List</a>
        <?php endif; ?>
    </div>

    <form method="post" enctype="multipart/form-data">
        <?= csrf() ?>
        <input type="hidden" name="action" value="save_section">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? 0 ?>">

        <!-- Row 1: Title, Subtitle, Section Type -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:20px; margin-bottom:20px;">
            <div>
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#1e293b;">Section Title *</label>
                <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>" placeholder="e.g. Who We Are, Our Values, Why Choose Us" style="width:100%; padding:11px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:15px;">
            </div>
            <div>
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#1e293b;">Subtitle / Eyebrow Badge (Optional)</label>
                <input type="text" name="subtitle" value="<?= e($edit['subtitle'] ?? '') ?>" placeholder="e.g. ABOUT ADISOMA, WHAT WE STAND FOR" style="width:100%; padding:11px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:15px;">
            </div>
            <div>
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#1e293b;">Section Layout Style *</label>
                <select name="section_type" id="sectionTypeSelect" onchange="toggleSectionOptions(this.value);" style="width:100%; padding:11px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:15px; background:#fff; font-weight:600;">
                    <?php 
                    $curType = $edit['section_type'] ?? 'text_image_right';
                    foreach ($layoutNames as $val => $name): ?>
                        <option value="<?= $val ?>" <?= $curType === $val ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Row 2: Content (Markdown Supported with Guide) -->
        <div style="margin-bottom:22px;">
            <label style="display:block; font-weight:700; margin-bottom:6px; color:#1e293b;">
                Section Content / Story Narrative (Markdown Supported)
            </label>
            <span style="display:block; font-size:13px; color:#64748b; margin-bottom:8px;">
                Supports headings, bold text, bullet points (- Point), and paragraphs.
            </span>
            
            <?= markdown_guide_html('aboutContentField') ?>
            
            <textarea name="content" id="aboutContentField" rows="7" placeholder="Enter paragraph narrative, key bullet points, or section description..." style="width:100%; padding:14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; line-height:1.7; font-family:inherit;"><?= e($edit['content'] ?? '') ?></textarea>
        </div>

        <!-- Row 3: Image Controls (Visible for layouts with image) -->
        <div id="imageControlsWrap" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:20px; margin-bottom:25px;">
            <h4 style="margin:0 0 12px; font-size:15px; color:#1e293b; display:flex; align-items:center; gap:8px;">
                <span>🖼️</span> Featured Image Settings
            </h4>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:20px;">
                <div>
                    <label style="display:block; font-weight:600; font-size:13px; margin-bottom:6px; color:#334155;">Image Web URL</label>
                    <input type="text" name="image_url" id="aboutImageUrl" value="<?= e($edit['image_url'] ?? '') ?>" placeholder="https://images.unsplash.com/photo-..." style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;">
                </div>
                <div>
                    <label style="display:block; font-weight:600; font-size:13px; margin-bottom:6px; color:#334155;">Or Upload From Computer</label>
                    <input type="file" name="image_upload" accept="image/*" style="width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:8px; background:#fff; font-size:13px;">
                </div>
            </div>
            <?php if (!empty($edit['image_url'])): ?>
                <div style="margin-top:12px; display:flex; align-items:center; gap:12px;">
                    <span style="font-size:12px; color:#64748b;">Current image preview:</span>
                    <img src="<?= e($edit['image_url']) ?>" alt="Preview" style="max-height:60px; border-radius:6px; border:1px solid #cbd5e1;">
                </div>
            <?php endif; ?>
        </div>

        <!-- Row 4: Cards Grid Builder (Visible when section_type === 'cards_grid') -->
        <div id="cardsGridBuilder" style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:12px; padding:22px; margin-bottom:25px; display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                <div>
                    <h4 style="margin:0 0 4px; font-size:16px; color:#0369a1; display:flex; align-items:center; gap:8px;">
                        <span>🗂️</span> Cards Grid Builder (Mission, Vision, Values, Highlights)
                    </h4>
                    <span style="font-size:13px; color:#0284c7;">Configure individual cards shown inside this grid section</span>
                </div>
                <button type="button" class="btn sm" onclick="addCardRow();" style="background:#0284c7; border-color:#0284c7; color:#fff !important; font-weight:600;">
                    + Add Another Card
                </button>
            </div>

            <div id="cardsContainer" style="display:flex; flex-direction:column; gap:14px;">
                <?php 
                $existingItems = $edit['items'] ?? [];
                if (empty($existingItems)) {
                    // Seed with at least 2 empty cards for new
                    $existingItems = [
                        ['title' => '', 'desc' => '', 'image' => ''],
                        ['title' => '', 'desc' => '', 'image' => '']
                    ];
                }
                foreach ($existingItems as $cIdx => $c): ?>
                    <div class="card-item-row" style="background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; padding:15px; display:grid; grid-template-columns: 2fr 3fr 2fr auto; gap:12px; align-items:center;">
                        <div>
                            <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Card Title</label>
                            <input type="text" name="card_title[]" value="<?= e($c['title'] ?? '') ?>" placeholder="e.g. Our Mission" style="width:100%; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Card Description</label>
                            <input type="text" name="card_desc[]" value="<?= e($c['desc'] ?? '') ?>" placeholder="Short card narrative..." style="width:100%; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Image URL / Icon (Optional)</label>
                            <input type="text" name="card_image[]" value="<?= e($c['image'] ?? '') ?>" placeholder="https://..." style="width:100%; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
                        </div>
                        <div style="padding-top:16px;">
                            <button type="button" onclick="this.closest('.card-item-row').remove();" style="background:#fee2e2; border:1px solid #fecaca; color:#b91c1c; border-radius:6px; width:30px; height:30px; cursor:pointer; font-weight:bold;" title="Remove this card">&times;</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Row 5: Action Button (CTA) & Background & Order -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom:25px;">
            <div>
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#1e293b;">Button Text (Optional)</label>
                <input type="text" name="btn_text" value="<?= e($edit['btn_text'] ?? '') ?>" placeholder="e.g. Contact Us ➔" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;">
            </div>
            <div>
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#1e293b;">Button URL / Destination</label>
                <input type="text" name="btn_url" value="<?= e($edit['btn_url'] ?? '') ?>" placeholder="e.g. contact.php or jobs.php" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;">
            </div>
            <div>
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#1e293b;">Section Background</label>
                <select name="bg_style" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:#fff;">
                    <?php 
                    $curBg = $edit['bg_style'] ?? 'white';
                    ?>
                    <option value="white" <?= $curBg === 'white' ? 'selected' : '' ?>>Clean White</option>
                    <option value="light" <?= $curBg === 'light' ? 'selected' : '' ?>>Soft Gray (#f8fafc)</option>
                    <option value="accent" <?= $curBg === 'accent' ? 'selected' : '' ?>>Navy / Brand Accent</option>
                </select>
            </div>
            <div>
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#1e293b;">Sort Order (Position)</label>
                <input type="number" name="sort_order" value="<?= e($edit['sort_order'] ?? 0) ?>" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px;">
            </div>
        </div>

        <div style="margin-bottom:25px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; color:#1e293b;">
                <input type="checkbox" name="active" value="1" <?= (!isset($edit['active']) || $edit['active']) ? 'checked' : '' ?> style="width:18px; height:18px; accent-color:var(--o);">
                <span>Display this section actively on the public About page</span>
            </label>
        </div>

        <div style="display:flex; gap:12px; align-items:center;">
            <button class="btn" type="submit" style="padding:13px 28px; font-size:16px; font-weight:700;">
                <?= $edit ? 'Save Section Changes' : 'Publish Section to About Page ➔' ?>
            </button>
            <?php if ($edit || $isNew): ?>
                <a href="manage-about.php" class="btn alt" style="padding:13px 20px;">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
function toggleSectionOptions(val) {
    var cardsWrap = document.getElementById('cardsGridBuilder');
    var imgWrap = document.getElementById('imageControlsWrap');
    
    if (val === 'cards_grid') {
        cardsWrap.style.display = 'block';
    } else {
        cardsWrap.style.display = 'none';
    }

    if (val === 'full_width' || val === 'cta_banner') {
        imgWrap.style.opacity = '0.7';
    } else {
        imgWrap.style.opacity = '1';
    }
}

function addCardRow() {
    var container = document.getElementById('cardsContainer');
    var div = document.createElement('div');
    div.className = 'card-item-row';
    div.style.cssText = 'background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; padding:15px; display:grid; grid-template-columns: 2fr 3fr 2fr auto; gap:12px; align-items:center;';
    div.innerHTML = `
        <div>
            <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Card Title</label>
            <input type="text" name="card_title[]" placeholder="e.g. Innovation" style="width:100%; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
        </div>
        <div>
            <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Card Description</label>
            <input type="text" name="card_desc[]" placeholder="Short card narrative..." style="width:100%; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
        </div>
        <div>
            <label style="font-size:11px; font-weight:700; color:#475569; display:block; margin-bottom:3px;">Image URL / Icon (Optional)</label>
            <input type="text" name="card_image[]" placeholder="https://..." style="width:100%; padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
        </div>
        <div style="padding-top:16px;">
            <button type="button" onclick="this.closest('.card-item-row').remove();" style="background:#fee2e2; border:1px solid #fecaca; color:#b91c1c; border-radius:6px; width:30px; height:30px; cursor:pointer; font-weight:bold;" title="Remove this card">&times;</button>
        </div>
    `;
    container.appendChild(div);
}

// Initial trigger
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('sectionTypeSelect');
    if (sel) {
        toggleSectionOptions(sel.value);
    }
});
</script>

<?php include '_footer.php'; ?>
