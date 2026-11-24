<?php require_once __DIR__.'/../includes/functions.php'; admin_only();
if($_SERVER['REQUEST_METHOD']==='POST'){ if(!csrf_ok())die('Bad token'); $id=(int)post('id');
 if(post('action')==='status'&&in_array(post('status'),['new','read','closed'])){ 
     db()->prepare('UPDATE inquiries SET status=? WHERE id=?')->execute([post('status'),$id]); 
     log_action('Updated Status to '.post('status'), 'Inquiry', $id); 
 }
 if(post('action')==='delete'){ 
     db()->prepare('DELETE FROM inquiries WHERE id=?')->execute([$id]); 
     log_action('Deleted', 'Inquiry', $id); 
     flash('Deleted.'); 
 }
 redirect('inquiries.php'.(isset($_GET['type'])&&$_GET['type']!==''?'?type='.urlencode($_GET['type']):''));
}
include '_header.php'; $t=$_GET['type']??''; 

if (in_array($t, ['contact', 'employer'])) {
    $st=db()->prepare('SELECT * FROM inquiries WHERE type=? ORDER BY id DESC');
    $st->execute([$t]);
} else {
    $st=db()->prepare('SELECT * FROM inquiries ORDER BY id DESC');
    $st->execute();
}
$rows=$st->fetchAll(); 
?>
<h1>Inquiries</h1><p><a href="inquiries.php">All</a> | <a href="?type=employer">Employer</a> | <a href="?type=contact">Contact</a></p>
<table><tr><th>Type</th><th>From</th><th>Details</th><th>Message</th><th>Status</th><th></th></tr>
<?php foreach($rows as $r): ?><tr><td><?=e($r['type'])?></td><td><?=e($r['name'])?><br><?=e($r['email'])?><br><?=e($r['phone'])?><br><small><?=e($r['created_at'])?></small></td><td><?=e($r['company'])?><br><?=e($r['subject'])?></td><td><?=nl2br(e($r['message']))?></td>
<td><form class="inl" method="post"><?=csrf()?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?=$r['id']?>"><select name="status" onchange="this.form.submit()"><?php foreach(['new','read','closed'] as $s): ?><option <?=$r['status']===$s?'selected':''?>><?=$s?></option><?php endforeach; ?></select></form></td>
<td><form class="inl" method="post"><?=csrf()?><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn sm red" name="action" value="delete" onclick="return confirm('Delete?')">Delete</button></form></td></tr><?php endforeach; ?></table>
<?php include '_footer.php'; ?>
