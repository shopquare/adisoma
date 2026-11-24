<?php
require_once __DIR__ . '/../includes/functions.php';
admin_only();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) die('Bad token');
    $a = post('action');
    $id = (int)post('id');

    if ($a === 'save') {
        $metric = post('metric');
        $label = post('label');
        $sort = (int)post('sort_order');
        $active = isset($_POST['active']) ? 1 : 0;

        if ($metric === '' || $label === '') {
            flash('Both metric value and label are required.', 'err');
        } else {
            if ($id > 0) {
                db()->prepare("UPDATE site_stats SET metric = ?, label = ?, sort_order = ?, active = ? WHERE id = ?")
                    ->execute([$metric, $label, $sort, $active, $id]);
                log_action('Updated', 'Stat', $id, "$metric $label");
                clear_all_cache();
                flash('Statistic counter updated successfully.');
            } else {
                db()->prepare("INSERT INTO site_stats (metric, label, sort_order, active) VALUES (?, ?, ?, ?)")
                    ->execute([$metric, $label, $sort, $active]);
                $newId = (int)db()->lastInsertId();
                log_action('Created', 'Stat', $newId, "$metric $label");
                clear_all_cache();
                flash('New statistic counter created successfully.');
            }
        }
    }

    if ($a === 'delete' && $id > 0) {
        $st = db()->prepare("SELECT label FROM site_stats WHERE id = ?");
        $st->execute([$id]);
        $lbl = $st->fetchColumn();
        db()->prepare("DELETE FROM site_stats WHERE id = ?")->execute([$id]);
        log_action('Deleted', 'Stat', $id, (string)$lbl);
        clear_all_cache();
        flash('Statistic counter removed.');
    }

    redirect('manage-stats.php');
}

include '_header.php';

$edit = ['id' => 0, 'metric' => '', 'label' => '', 'sort_order' => 0, 'active' => 1];
if (isset($_GET['edit'])) {
    $st = db()->prepare("SELECT * FROM site_stats WHERE id = ?");
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: $edit;
}

$rows = db()->query("SELECT * FROM site_stats ORDER BY sort_order ASC, id ASC")->fetchAll();
?>

<div class="dash-head">
    <div>
        <h1 style="margin:0 0 6px;">Homepage Stats & Counters</h1>
        <span style="color:#64748b; font-size:14px;">Manage the highlighted statistics banner displayed across your website</span>
    </div>
    <a href="../index.php#stats" target="_blank" class="btn sm" style="background:#0284c7; border-color:#0284c7; color:#fff !important; font-weight:600;">
        View Live on Site ➔
    </a>
</div>

<!-- Live Preview Banner of the Statistics Section -->
<div style="margin-bottom:30px;">
    <h3 style="margin:0 0 12px; font-size:16px; color:#475569; display:flex; align-items:center; gap:8px;">
        <span>👁️</span> Live Banner Preview (How it appears on website)
    </h3>
    <div style="background: linear-gradient(rgba(245, 124, 0, 0.94), rgba(217, 104, 0, 0.94)), url('https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1920&q=80') center/cover; border-radius:12px; padding:35px 20px; color:#ffffff; text-align:center; box-shadow:0 8px 24px rgba(245, 124, 0, 0.25);">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap:20px; max-width:960px; margin:0 auto;">
            <?php 
            $active_rows = array_filter($rows, fn($r) => $r['active'] == 1);
            if (empty($active_rows)): ?>
                <div style="grid-column: 1/-1; color:rgba(255,255,255,0.85); font-style:italic;">No active statistics to display. Add one below!</div>
            <?php else: ?>
                <?php foreach($active_rows as $item): ?>
                <div>
                    <b style="display:block; font-size:38px; font-weight:800; line-height:1.1; margin-bottom:6px; color:#fff; text-shadow:0 2px 8px rgba(0,0,0,0.2);">
                        <?= e($item['metric']) ?>
                    </b>
                    <span style="font-size:15px; font-weight:600; color:#fff; letter-spacing:0.5px; opacity:0.95;">
                        <?= e($item['label']) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add / Edit Form -->
<form class="at" method="post" style="margin-bottom:35px;">
    <?= csrf() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $edit['id'] ?>">

    <h3 style="margin:0 0 18px; font-size:18px; color:var(--n);">
        <?= $edit['id'] ? '✏️ Edit Statistic Counter' : '➕ Add New Statistic Counter' ?>
    </h3>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:18px;">
        <div>
            <label>Metric Value (Number / Text)</label>
            <input name="metric" required value="<?= e($edit['metric']) ?>" placeholder="e.g. 500+, 100+, 98%, 24h">
        </div>
        <div>
            <label>Label / Title</label>
            <input name="label" required value="<?= e($edit['label']) ?>" placeholder="e.g. Placements, Happy Clients, Industries">
        </div>
        <div>
            <label>Sort Order (Display Sequence)</label>
            <input type="number" name="sort_order" value="<?= $edit['sort_order'] ?>">
        </div>
    </div>

    <div style="margin-top:14px;">
        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
            <input type="checkbox" name="active" style="width:auto; margin:0;" <?= $edit['active'] ? 'checked' : '' ?>>
            <span style="font-weight:600; color:#334155;">Active (Display on website homepage)</span>
        </label>
    </div>

    <div style="margin-top:20px; display:flex; gap:10px; align-items:center;">
        <button class="btn" style="padding:10px 24px; font-weight:700;">
            <?= $edit['id'] ? 'Save Changes' : 'Add Statistic Counter' ?>
        </button>
        <?php if ($edit['id']): ?>
            <a class="btn alt" href="manage-stats.php">Cancel Edit</a>
        <?php endif; ?>
    </div>
</form>

<!-- All Stats Table -->
<h3 style="margin:0 0 15px; font-size:18px; color:var(--n);">Existing Statistics List</h3>
<table>
    <tr>
        <th>Display Order</th>
        <th>Metric Value</th>
        <th>Label / Description</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php if (empty($rows)): ?>
        <tr><td colspan="5" style="text-align:center; padding:25px; color:#64748b;">No statistics found.</td></tr>
    <?php else: ?>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td style="font-weight:700; color:#64748b;"><?= $r['sort_order'] ?></td>
            <td>
                <span style="font-size:22px; font-weight:800; color:var(--o); display:inline-block; padding:2px 10px; background:#fff7ed; border-radius:6px; border:1px solid #fed7aa;">
                    <?= e($r['metric']) ?>
                </span>
            </td>
            <td style="font-weight:600; color:var(--n);"><?= e($r['label']) ?></td>
            <td>
                <span style="display:inline-block; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600; background:<?= $r['active'] ? '#dcfce7; color:#15803d' : '#fee2e2; color:#b91c1c' ?>;">
                    <?= $r['active'] ? 'Active' : 'Disabled' ?>
                </span>
            </td>
            <td>
                <div class="table-actions">
                    <a class="btn sm alt" href="?edit=<?= $r['id'] ?>">Edit</a>
                    <form class="inl" method="post">
                        <?= csrf() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button class="btn sm red" onclick="return confirm('Remove this statistic counter?')">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<?php include '_footer.php'; ?>
