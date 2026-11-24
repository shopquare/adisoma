<?php require_once __DIR__.'/../includes/functions.php'; admin_only();
if($_SERVER['REQUEST_METHOD']==='POST'){ if(!csrf_ok())die('Bad token'); $a=post('action');$id=(int)post('id');
 if($a==='save'){ $d=[post('title'),post('category')==='Non-IT'?'Non-IT':'IT',post('location'),post('job_type'),post('experience'),post('salary'),post('description'),post('status')==='published'?'published':'draft'];
  if($d[0]===''||$d[2]===''||$d[6]===''){flash('Title, location and description are required.','err');redirect('manage-jobs.php'.($id?"?edit=$id":'?new=1'));}
  
  if($id) { 
      db()->prepare('UPDATE jobs SET title=?,category=?,location=?,job_type=?,experience=?,salary=?,description=?,status=? WHERE id=?')->execute([...$d,$id]);
      log_action('Updated', 'Job', $id, $d[0]);
  } else { 
      db()->prepare('INSERT INTO jobs(title,category,location,job_type,experience,salary,description,status) VALUES(?,?,?,?,?,?,?,?)')->execute($d);
      log_action('Created', 'Job', db()->lastInsertId(), $d[0]); 
  }
  flash('Job saved.');}
 if($a==='toggle'){ db()->prepare("UPDATE jobs SET status=IF(status='published','draft','published') WHERE id=?")->execute([$id]); log_action('Toggled Status', 'Job', $id); }
 if($a==='delete'){ 
     $st = db()->prepare("SELECT title FROM jobs WHERE id = ?");
     $st->execute([$id]);
     $t = $st->fetchColumn(); 
     db()->prepare('DELETE FROM jobs WHERE id=?')->execute([$id]); 
     log_action('Deleted', 'Job', $id, (string)$t); 
     flash('Job deleted.'); 
 }
 redirect('manage-jobs.php');}
include '_header.php';
if(isset($_GET['new'])||isset($_GET['edit'])){ $j=['id'=>0,'title'=>'','category'=>'IT','location'=>'','job_type'=>'Full-time','experience'=>'','salary'=>'','description'=>'','status'=>'draft'];
 if(isset($_GET['edit'])){$s=db()->prepare('SELECT * FROM jobs WHERE id=?');$s->execute([(int)$_GET['edit']]);$j=$s->fetch()?:$j;} ?>
<h1><?=$j['id']?'Edit':'Add'?> Job</h1><form class="at" method="post"><?=csrf()?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=$j['id']?>">
<label>Title *</label><input name="title" required value="<?=e($j['title'])?>">
<label>Category</label><select name="category"><?php foreach(['IT','Non-IT'] as $c): ?><option <?=$j['category']===$c?'selected':''?>><?=$c?></option><?php endforeach; ?></select>
<label>Location *</label><input name="location" required value="<?=e($j['location'])?>">
<label>Type</label><select name="job_type"><?php foreach(['Full-time','Part-time','Contract','Internship'] as $c): ?><option <?=$j['job_type']===$c?'selected':''?>><?=$c?></option><?php endforeach; ?></select>
<label>Experience</label><input name="experience" value="<?=e($j['experience'])?>"><label>Salary</label><input name="salary" value="<?=e($j['salary'])?>">
<label>Description *</label><textarea name="description" rows="8" required><?=e($j['description'])?></textarea>
<label>Status</label><select name="status"><option value="draft" <?=$j['status']==='draft'?'selected':''?>>Draft</option><option value="published" <?=$j['status']==='published'?'selected':''?>>Published</option></select>
<button class="btn">Save Job</button><a class="btn alt" href="manage-jobs.php">Cancel</a></form>
<?php } else { $jobs=db()->query('SELECT j.*,(SELECT COUNT(*) FROM applications WHERE job_id=j.id) ac FROM jobs j ORDER BY id DESC')->fetchAll(); ?>
<h1>Jobs <a class="btn sm" href="?new=1">+ Add Job</a></h1><table><tr><th>Title</th><th>Category</th><th>Location</th><th>Status</th><th>Apps</th><th>Actions</th></tr>
<?php foreach($jobs as $j): ?><tr><td><?=e($j['title'])?></td><td><?=e($j['category'])?></td><td><?=e($j['location'])?></td><td><?=e($j['status'])?></td><td><?=$j['ac']?></td><td>
<div class="table-actions"><a class="btn sm alt" href="?edit=<?=$j['id']?>" >Edit</a><form class="inl" method="post" ><?=csrf()?><input type="hidden" name="id" value="<?=$j['id']?>"><button class="btn sm" name="action" value="toggle" ><?=$j['status']==='published'?'Unpublish':'Publish'?></button><button class="btn sm red" name="action" value="delete"  onclick="return confirm('Delete this job?')">Delete</button></form></div></td></tr><?php endforeach; ?></table><?php } include '_footer.php'; ?>
