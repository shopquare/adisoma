<?php
require_once __DIR__ . '/../includes/functions.php';
admin_only();

$tab = isset($_GET['tab']) && $_GET['tab'] === 'admin' ? 'admin' : 'header';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) die('Bad CSRF token');
    $action = post('action');
    $id = (int)post('id');
    $postTab = post('menu_type', $tab);
    if (!in_array($postTab, ['header', 'admin'], true)) {
        $postTab = 'header';
    }

    // Single item up/down reorder
    if ($action === 'move') {
        $dir = post('dir'); // 'up' or 'down'
        $item = db()->prepare("SELECT id, sort_order FROM nav_items WHERE id = ? AND menu_type = ?");
        $item->execute([$id, $postTab]);
        $cur = $item->fetch(PDO::FETCH_ASSOC);

        if ($cur) {
            $curOrder = (int)$cur['sort_order'];
            if ($dir === 'up') {
                $adjStmt = db()->prepare("SELECT id, sort_order FROM nav_items WHERE menu_type = ? AND sort_order <= ? AND id != ? ORDER BY sort_order DESC, id DESC LIMIT 1");
            } else {
                $adjStmt = db()->prepare("SELECT id, sort_order FROM nav_items WHERE menu_type = ? AND sort_order >= ? AND id != ? ORDER BY sort_order ASC, id ASC LIMIT 1");
            }
            $adjStmt->execute([$postTab, $curOrder, $id]);
            $adj = $adjStmt->fetch(PDO::FETCH_ASSOC);

            if ($adj) {
                $adjOrder = (int)$adj['sort_order'];
                // If orders are identical, re-sequence first
                if ($curOrder === $adjOrder) {
                    $all = db()->prepare("SELECT id FROM nav_items WHERE menu_type = ? ORDER BY sort_order ASC, id ASC");
                    $all->execute([$postTab]);
                    $ids = $all->fetchAll(PDO::FETCH_COLUMN);
                    $seq = 1;
                    $upd = db()->prepare("UPDATE nav_items SET sort_order = ? WHERE id = ?");
                    foreach ($ids as $itemId) {
                        $upd->execute([$seq * 10, $itemId]);
                        $seq++;
                    }
                    // Re-fetch orders
                    $stCur = db()->prepare("SELECT sort_order FROM nav_items WHERE id = ?");
                    $stCur->execute([$id]);
                    $curOrder = (int)$stCur->fetchColumn();
                    if ($dir === 'up') {
                        $adjStmt = db()->prepare("SELECT id, sort_order FROM nav_items WHERE menu_type = ? AND sort_order < ? ORDER BY sort_order DESC LIMIT 1");
                    } else {
                        $adjStmt = db()->prepare("SELECT id, sort_order FROM nav_items WHERE menu_type = ? AND sort_order > ? ORDER BY sort_order ASC LIMIT 1");
                    }
                    $adjStmt->execute([$postTab, $curOrder]);
                    $adj = $adjStmt->fetch(PDO::FETCH_ASSOC);
                    if ($adj) {
                        $adjOrder = (int)$adj['sort_order'];
                    }
                }

                if ($adj) {
                    db()->beginTransaction();
                    $s1 = db()->prepare("UPDATE nav_items SET sort_order = ? WHERE id = ?");
                    $s1->execute([$adjOrder, $id]);
                    $s1->execute([$curOrder, $adj['id']]);
                    db()->commit();
                    clear_all_cache();
                    log_action('Reordered', 'Menu Item', $id, "Swapped order with #{$adj['id']}");
                    flash('Menu order updated successfully.');
                }
            } else {
                flash('Item is already at the extreme position.', 'warn');
            }
        }
        redirect("manage-menu.php?tab=$postTab");
    }

    // Bulk save orders
    if ($action === 'save_orders') {
        $orders = $_POST['order'] ?? [];
        if (is_array($orders)) {
            db()->beginTransaction();
            $upd = db()->prepare("UPDATE nav_items SET sort_order = ? WHERE id = ? AND menu_type = ?");
            foreach ($orders as $itemId => $ord) {
                $upd->execute([(int)$ord, (int)$itemId, $postTab]);
            }
            db()->commit();
            clear_all_cache();
            log_action('Reordered', 'Menu Items', 0, "Bulk updated sort orders for $postTab menu");
            flash('All menu sort orders have been saved.');
        }
        redirect("manage-menu.php?tab=$postTab");
    }

    // Toggle active status
    if ($action === 'toggle_active') {
        $st = db()->prepare("UPDATE nav_items SET active = 1 - active WHERE id = ? AND menu_type = ?");
        $st->execute([$id, $postTab]);
        clear_all_cache();
        log_action('Toggled', 'Menu Item', $id, "Visibility changed");
        flash('Menu visibility updated.');
        redirect("manage-menu.php?tab=$postTab");
    }

    // Save (Add or Edit)
    if ($action === 'save') {
        $title = trim(post('title'));
        $url = trim(post('url'));
        $sort = (int)post('sort_order');
        $active = isset($_POST['active']) ? 1 : 0;

        if ($title === '' || $url === '') {
            flash('Title and URL are required.', 'err');
        } else {
            if ($id > 0) {
                $st = db()->prepare("UPDATE nav_items SET title = ?, url = ?, sort_order = ?, active = ? WHERE id = ? AND menu_type = ?");
                $st->execute([$title, $url, $sort, $active, $id, $postTab]);
                log_action('Updated', 'Menu Item', $id, "$title ($url)");
                clear_all_cache();
                flash('Menu item updated successfully.');
            } else {
                if ($sort <= 0) {
                    $stMax = db()->prepare("SELECT COALESCE(MAX(sort_order), 0) FROM nav_items WHERE menu_type = ?");
                    $stMax->execute([$postTab]);
                    $max = (int)$stMax->fetchColumn();
                    $sort = $max + 1;
                }
                $st = db()->prepare("INSERT INTO nav_items (title, url, menu_type, sort_order, active, is_custom) VALUES (?, ?, ?, ?, ?, 1)");
                $st->execute([$title, $url, $postTab, $sort, $active]);
                $newId = (int)db()->lastInsertId();
                log_action('Created', 'Menu Item', $newId, "$title ($url)");
                clear_all_cache();
                flash('New menu item added successfully.');
            }
        }
        redirect("manage-menu.php?tab=$postTab");
    }

    // Delete item
    if ($action === 'delete' && $id > 0) {
        $st = db()->prepare("SELECT title FROM nav_items WHERE id = ? AND menu_type = ?");
        $st->execute([$id, $postTab]);
        $title = $st->fetchColumn();
        db()->prepare("DELETE FROM nav_items WHERE id = ? AND menu_type = ?")->execute([$id, $postTab]);
        log_action('Deleted', 'Menu Item', $id, (string)$title);
        clear_all_cache();
        flash('Menu item deleted.');
        redirect("manage-menu.php?tab=$postTab");
    }

    // Reset defaults
    if ($action === 'reset_defaults') {
        db()->prepare("DELETE FROM nav_items WHERE menu_type = ?")->execute([$postTab]);
        if ($postTab === 'header') {
            $defaults = [
                ['Home', 'index.php', 1],
                ['About', 'about.php', 2],
                ['Services', 'services.php', 3],
                ['Jobs', 'jobs.php', 4],
                ['Blog', 'blogs.php', 5],
                ['For Employers', 'employers.php', 6],
                ['Job Seekers', 'job-seekers.php', 7],
                ['Contact', 'contact.php', 8],
            ];
        } else {
            $defaults = [
                ['Dashboard', 'dashboard.php', 1],
                ['Jobs', 'manage-jobs.php', 2],
                ['Blogs', 'manage-blogs.php', 3],
                ['Applications', 'applications.php', 4],
                ['Inquiries', 'inquiries.php', 5],
                ['Services', 'manage-services.php', 6],
                ['Testimonials', 'manage-testimonials.php', 7],
                ['Site Stats', 'manage-stats.php', 8],
                ['Menu Manager', 'manage-menu.php', 9],
                ['Site Settings', 'manage-settings.php', 10],
                ['Admins', 'manage-admins.php', 11],
                ['Logs', 'logs.php', 12],
            ];
        }
        $stmt = db()->prepare("INSERT INTO nav_items (title, url, menu_type, sort_order, active, is_custom) VALUES (?, ?, ?, ?, 1, 0)");
        foreach ($defaults as $d) {
            $stmt->execute([$d[0], $d[1], $postTab, $d[2]]);
        }
        clear_all_cache();
        log_action('Reset', 'Menu Items', 0, "Reset $postTab menu to defaults");
        flash('Navigation menu reset to original defaults.');
        redirect("manage-menu.php?tab=$postTab");
    }

    redirect("manage-menu.php?tab=$postTab");
}

include '_header.php';

// Prepare edit item if requested
$edit = ['id' => 0, 'title' => '', 'url' => '', 'sort_order' => 0, 'active' => 1];
if (isset($_GET['edit'])) {
    $st = db()->prepare("SELECT * FROM nav_items WHERE id = ? AND menu_type = ?");
    $st->execute([(int)$_GET['edit'], $tab]);
    $edit = $st->fetch(PDO::FETCH_ASSOC) ?: $edit;
}

// Fetch all items for current tab
$st = db()->prepare("SELECT * FROM nav_items WHERE menu_type = ? ORDER BY sort_order ASC, id ASC");
$st->execute([$tab]);
$items = $st->fetchAll(PDO::FETCH_ASSOC);

// Counters
$headerCount = (int)db()->query("SELECT COUNT(*) FROM nav_items WHERE menu_type = 'header'")->fetchColumn();
$adminCount = (int)db()->query("SELECT COUNT(*) FROM nav_items WHERE menu_type = 'admin'")->fetchColumn();
?>

<div class="dash-head">
    <div>
        <h1 style="margin:0 0 6px;">Menu & Navigation Manager</h1>
        <span style="color:#64748b; font-size:14px;">Reorder, customize, show or hide links in your website navigation and admin sidebar</span>
    </div>
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <a href="../index.php" target="_blank" class="btn sm" style="background:#0284c7; border-color:#0284c7; color:#fff !important; font-weight:600;">
            View Live Site ➔
        </a>
    </div>
</div>

<!-- Tab Navigation -->
<div style="display:flex; gap:10px; margin-bottom:25px; border-bottom:2px solid #e2e8f0; padding-bottom:12px; flex-wrap:wrap;">
    <a href="?tab=header" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding:10px 20px; border-radius:8px; font-weight:700; font-size:15px; transition:all 0.2s; <?= $tab === 'header' ? 'background:var(--o); color:#fff; box-shadow:0 4px 12px rgba(245, 124, 0, 0.3);' : 'background:#f1f5f9; color:#475569;' ?>">
        <span>🌐</span> Website Header Menu
        <span style="font-size:12px; padding:2px 8px; border-radius:12px; background:<?= $tab === 'header' ? 'rgba(255,255,255,0.25)' : '#e2e8f0' ?>; color:<?= $tab === 'header' ? '#fff' : '#475569' ?>;"><?= $headerCount ?></span>
    </a>
    <a href="?tab=admin" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding:10px 20px; border-radius:8px; font-weight:700; font-size:15px; transition:all 0.2s; <?= $tab === 'admin' ? 'background:var(--o); color:#fff; box-shadow:0 4px 12px rgba(245, 124, 0, 0.3);' : 'background:#f1f5f9; color:#475569;' ?>">
        <span>⚙️</span> Admin Sidebar Navigation
        <span style="font-size:12px; padding:2px 8px; border-radius:12px; background:<?= $tab === 'admin' ? 'rgba(255,255,255,0.25)' : '#e2e8f0' ?>; color:<?= $tab === 'admin' ? '#fff' : '#475569' ?>;"><?= $adminCount ?></span>
    </a>
</div>

<?php if ($tab === 'header'): ?>
<!-- Live Preview Mockup of Website Header -->
<div style="background:#ffffff; border-radius:12px; border:1px solid #e2e8f0; padding:20px; margin-bottom:30px; box-shadow:0 4px 15px rgba(0,0,0,0.03);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
        <h3 style="margin:0; font-size:16px; color:#334155; display:flex; align-items:center; gap:8px;">
            <span>👁️</span> Live Header Menu Preview (Current Ordering)
        </h3>
        <span style="font-size:13px; color:#64748b;">Active items shown in orange; hidden items appear dimmed</span>
    </div>
    <div style="background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; padding:14px 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:15px; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
        <div style="display:flex; align-items:center; gap:10px;">
            <img src="<?= BASE_URL ?>/assets/images/adisoma-logo.png" alt="Logo" style="height:36px; object-fit:contain;">
        </div>
        <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <?php foreach ($items as $idx => $m): ?>
                <?php if ($m['active']): ?>
                    <span style="padding:6px 12px; border-radius:6px; font-weight:700; font-size:14px; color:#1e293b; background:#f8fafc; border:1px solid #e2e8f0; display:inline-flex; align-items:center; gap:5px;">
                        <span style="font-size:11px; color:#f57c00; font-weight:800;"><?= $idx + 1 ?>.</span>
                        <?= e($m['title']) ?>
                        <?php if (str_starts_with($m['url'], 'http://') || str_starts_with($m['url'], 'https://')): ?>
                            <span style="font-size:11px; opacity:0.6;">↗</span>
                        <?php endif; ?>
                    </span>
                <?php else: ?>
                    <span style="padding:6px 12px; border-radius:6px; font-weight:600; font-size:14px; color:#94a3b8; background:#f1f5f9; border:1px dashed #cbd5e1; text-decoration:line-through;" title="Hidden from visitors">
                        <?= e($m['title']) ?> (hidden)
                    </span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Add or Edit Menu Item Form -->
<form class="at" method="post" style="margin-bottom:35px; background:#ffffff; border-radius:12px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 4px 15px rgba(0,0,0,0.03);">
    <?= csrf() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="menu_type" value="<?= $tab ?>">
    <input type="hidden" name="id" value="<?= $edit['id'] ?>">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
        <h3 style="margin:0; font-size:18px; color:var(--n);">
            <?= $edit['id'] ? '✏️ Edit Menu Item' : '➕ Add New Menu Link to ' . ($tab === 'header' ? 'Website Header' : 'Admin Sidebar') ?>
        </h3>
        <?php if ($edit['id']): ?>
            <a href="manage-menu.php?tab=<?= $tab ?>" class="btn sm alt">Cancel Edit</a>
        <?php endif; ?>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:18px;">
        <div>
            <label style="font-weight:600; font-size:14px; margin-bottom:6px; display:block;">Navigation Label / Title</label>
            <input name="title" required value="<?= e($edit['title']) ?>" placeholder="e.g. Careers, Team, Blog, Contact" style="width:100%;">
        </div>
        <div>
            <label style="font-weight:600; font-size:14px; margin-bottom:6px; display:block;">Target URL / Destination</label>
            <input name="url" required value="<?= e($edit['url']) ?>" placeholder="e.g. blogs.php, about.php, or https://..." style="width:100%;">
            <small style="color:#64748b; font-size:12px; display:block; margin-top:4px;">Relative file path or full external URL (https://)</small>
        </div>
        <div>
            <label style="font-weight:600; font-size:14px; margin-bottom:6px; display:block;">Sort Sequence Number</label>
            <input type="number" name="sort_order" value="<?= $edit['sort_order'] ?>" placeholder="1, 2, 3..." style="width:100%;">
            <small style="color:#64748b; font-size:12px; display:block; margin-top:4px;">Lower numbers appear first on the left</small>
        </div>
    </div>

    <div style="margin-top:16px;">
        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
            <input type="checkbox" name="active" style="width:auto; margin:0;" <?= $edit['active'] ? 'checked' : '' ?>>
            <span style="font-weight:600; color:#334155;">Active & Visible (Show this link in navigation)</span>
        </label>
    </div>

    <div style="margin-top:20px; display:flex; gap:10px; align-items:center;">
        <button class="btn" style="padding:10px 24px; font-weight:700;">
            <?= $edit['id'] ? 'Save Changes' : 'Add Menu Item' ?>
        </button>
        <?php if ($edit['id']): ?>
            <a class="btn alt" href="manage-menu.php?tab=<?= $tab ?>">Cancel</a>
        <?php endif; ?>
    </div>
</form>

<!-- Reordering & Item Management Table -->
<div style="background:#ffffff; border-radius:12px; border:1px solid #e2e8f0; padding:24px; box-shadow:0 4px 15px rgba(0,0,0,0.03);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:12px;">
        <div>
            <h3 style="margin:0 0 4px; font-size:18px; color:var(--n);">
                <?= $tab === 'header' ? 'Website Header Menu Order' : 'Admin Sidebar Menu Order' ?>
            </h3>
            <span style="color:#64748b; font-size:13px;">Use the ⬆️ Up and ⬇️ Down buttons to instantly move items, or adjust sequence numbers and click Save Orders</span>
        </div>
        <form method="post" onsubmit="return confirm('Reset this menu to standard default configuration? Any custom links will be removed.');" style="margin:0;">
            <?= csrf() ?>
            <input type="hidden" name="action" value="reset_defaults">
            <input type="hidden" name="menu_type" value="<?= $tab ?>">
            <button type="submit" class="btn sm alt" style="color:#b91c1c; border-color:#fca5a5;">
                🔄 Reset to Defaults
            </button>
        </form>
    </div>

    <form method="post">
        <?= csrf() ?>
        <input type="hidden" name="action" value="save_orders">
        <input type="hidden" name="menu_type" value="<?= $tab ?>">

        <table style="width:100%; margin-bottom:20px;">
            <thead>
                <tr>
                    <th style="width:160px; text-align:center;">Quick Reorder</th>
                    <th style="width:90px; text-align:center;">Seq #</th>
                    <th>Menu Title</th>
                    <th>Link Destination (URL)</th>
                    <th style="width:120px; text-align:center;">Status</th>
                    <th style="width:180px; text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:30px; color:#64748b;">
                            No menu items configured. Click "Reset to Defaults" or add a new link above.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $tot = count($items); foreach ($items as $idx => $r): ?>
                        <tr style="border-bottom:1px solid #f1f5f9; <?= !$r['active'] ? 'background:#fcfcfc;' : '' ?>">
                            <td style="text-align:center; white-space:nowrap;">
                                <div style="display:inline-flex; gap:6px;">
                                    <!-- Move UP -->
                                    <form method="post" style="display:inline; margin:0;">
                                        <?= csrf() ?>
                                        <input type="hidden" name="action" value="move">
                                        <input type="hidden" name="dir" value="up">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <input type="hidden" name="menu_type" value="<?= $tab ?>">
                                        <button type="submit" class="btn sm alt" style="padding:4px 10px; font-size:14px; line-height:1; <?= $idx === 0 ? 'opacity:0.35; cursor:not-allowed;' : '' ?>" <?= $idx === 0 ? 'disabled' : '' ?> title="Move Up">
                                            ⬆️
                                        </button>
                                    </form>
                                    <!-- Move DOWN -->
                                    <form method="post" style="display:inline; margin:0;">
                                        <?= csrf() ?>
                                        <input type="hidden" name="action" value="move">
                                        <input type="hidden" name="dir" value="down">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <input type="hidden" name="menu_type" value="<?= $tab ?>">
                                        <button type="submit" class="btn sm alt" style="padding:4px 10px; font-size:14px; line-height:1; <?= $idx === $tot - 1 ? 'opacity:0.35; cursor:not-allowed;' : '' ?>" <?= $idx === $tot - 1 ? 'disabled' : '' ?> title="Move Down">
                                            ⬇️
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <input type="number" name="order[<?= $r['id'] ?>]" value="<?= (int)$r['sort_order'] ?>" style="width:60px; text-align:center; padding:4px 6px; border:1px solid #cbd5e1; border-radius:6px; font-weight:700;">
                            </td>
                            <td>
                                <strong style="color:var(--n); font-size:15px;"><?= e($r['title']) ?></strong>
                                <?php if ($r['is_custom']): ?>
                                    <span style="font-size:11px; padding:2px 6px; border-radius:10px; background:#eff6ff; color:#1d4ed8; font-weight:600; margin-left:6px;">Custom</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code style="background:#f1f5f9; padding:3px 8px; border-radius:4px; font-size:13px; color:#475569;"><?= e($r['url']) ?></code>
                            </td>
                            <td style="text-align:center;">
                                <form method="post" style="display:inline; margin:0;">
                                    <?= csrf() ?>
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="menu_type" value="<?= $tab ?>">
                                    <button type="submit" style="background:none; border:none; cursor:pointer; padding:0;" title="Click to toggle status">
                                        <span style="display:inline-block; padding:4px 12px; border-radius:12px; font-size:12px; font-weight:700; transition:all 0.2s; background:<?= $r['active'] ? '#dcfce7; color:#15803d; border:1px solid #bbf7d0;' : '#f1f5f9; color:#64748b; border:1px solid #cbd5e1;' ?>">
                                            <?= $r['active'] ? '● Active' : '○ Hidden' ?>
                                        </span>
                                    </button>
                                </form>
                            </td>
                            <td style="text-align:center;">
                                <div class="table-actions" style="justify-content:center;">
                                    <a class="btn sm alt" href="?tab=<?= $tab ?>&edit=<?= $r['id'] ?>" style="padding:5px 12px;">Edit</a>
                                    <form class="inl" method="post" onsubmit="return confirm('Remove &quot;<?= addslashes(e($r['title'])) ?>&quot; from navigation?');" style="margin:0;">
                                        <?= csrf() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <input type="hidden" name="menu_type" value="<?= $tab ?>">
                                        <button class="btn sm red" style="padding:5px 12px;">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (!empty($items)): ?>
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-top:16px;">
                <span style="font-size:13px; color:#64748b;">
                    💡 Tip: Changes take effect across desktop and mobile immediately. Browser cache is automatically cleared.
                </span>
                <button type="submit" class="btn" style="padding:9px 22px; font-weight:700; display:inline-flex; align-items:center; gap:8px;">
                    <span>💾</span> Save All Sequence Numbers
                </button>
            </div>
        <?php endif; ?>
    </form>
</div>

<?php include '_footer.php'; ?>
