<?php require_once __DIR__.'/../includes/functions.php'; admin_only();
if($_SERVER['REQUEST_METHOD']==='POST'){ if(!csrf_ok())die('Bad token'); $id=(int)post('id');
 if(post('action')==='status'&&in_array(post('status'),['new','reviewed','shortlisted','rejected'])) { 
     db()->prepare('UPDATE applications SET status=? WHERE id=?')->execute([post('status'),$id]); 
     log_action('Updated Status to '.post('status'), 'Application', $id); 
 }
 if(post('action')==='delete'){
     $s=db()->prepare('SELECT resume FROM applications WHERE id=?');$s->execute([$id]);if($f=$s->fetchColumn())@unlink(__DIR__.'/../uploads/resumes/'.basename($f));
     db()->prepare('DELETE FROM applications WHERE id=?')->execute([$id]); 
     log_action('Deleted', 'Application', $id); 
     flash('Application deleted.');
 }
 redirect('applications.php');}
include '_header.php'; $rows=db()->query('SELECT a.*,j.title jt FROM applications a LEFT JOIN jobs j ON j.id=a.job_id ORDER BY a.id DESC')->fetchAll(); ?>
<h1>Applications</h1><table><tr><th>Candidate</th><th>Job</th><th>Contact</th><th>Exp.</th><th>Message</th><th>Resume</th><th>Status</th><th></th></tr>
<?php foreach($rows as $r): ?><tr><td><?=e($r['name'])?><br><small><?=e($r['created_at'])?></small></td><td><?=e($r['jt']?:'General')?></td><td><?=e($r['email'])?><br><?=e($r['phone'])?></td><td><?=e($r['experience'])?></td><td><?=e($r['message'])?></td>
<td><a class="btn sm" href="download.php?id=<?=$r['id']?>">Download</a></td>
<td><form class="inl" method="post"><?=csrf()?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?=$r['id']?>"><select name="status" onchange="this.form.submit()"><?php foreach(['new','reviewed','shortlisted','rejected'] as $s): ?><option <?=$r['status']===$s?'selected':''?>><?=$s?></option><?php endforeach; ?></select></form></td>
<td><form class="inl" method="post"><?=csrf()?><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn sm red" name="action" value="delete" onclick="return confirm('Delete application and resume?')">Delete</button></form></td></tr><?php endforeach; ?></table>
<?php include '_footer.php'; ?>
