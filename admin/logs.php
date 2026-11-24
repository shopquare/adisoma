<?php require_once __DIR__.'/../includes/functions.php'; admin_only(); include '_header.php';
$rows=db()->query('SELECT l.*, a.username FROM admin_logs l LEFT JOIN admins a ON l.admin_id = a.id ORDER BY l.id DESC LIMIT 200')->fetchAll();
?>
<h1>Action Logs</h1>
<table><tr><th>Time</th><th>Admin</th><th>Action</th><th>Type</th><th>Details</th></tr>
<?php foreach($rows as $r): ?>
<tr><td style="white-space:nowrap;"><?=$r['created_at']?></td><td><span class="tag" style="margin:0;"><?=e($r['username']??'Deleted User')?></span></td>
<td><b><?=e($r['action'])?></b></td><td><?=e($r['entity_type'])?> (ID: <?=$r['entity_id']?>)</td><td><?=e($r['details'])?></td></tr>
<?php endforeach; ?></table>
<?php include '_footer.php'; ?>