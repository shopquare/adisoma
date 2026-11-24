<?php require_once __DIR__.'/../includes/functions.php'; admin_only();
if($_SERVER['REQUEST_METHOD']==='POST'){ if(!csrf_ok())die('Bad token'); $a=post('action');$id=(int)post('id');
 if($a==='save'&&post('author_name')!==''&&post('content')!==''){ $d=[post('author_name'),post('content'),(int)post('rating'),isset($_POST['active'])?1:0];
  if($id){ db()->prepare('UPDATE testimonials SET author_name=?,content=?,rating=?,active=? WHERE id=?')->execute([...$d,$id]); log_action('Updated', 'Testimonial', $id, post('author_name')); }
  else { db()->prepare('INSERT INTO testimonials(author_name,content,rating,active) VALUES(?,?,?,?)')->execute($d); log_action('Created', 'Testimonial', db()->lastInsertId(), post('author_name')); }flash('Testimonial saved.');}
 if($a==='delete'){ 
     $st = db()->prepare("SELECT author_name FROM testimonials WHERE id = ?");
     $st->execute([$id]);
     $t = $st->fetchColumn(); 
     db()->prepare('DELETE FROM testimonials WHERE id=?')->execute([$id]); 
     log_action('Deleted', 'Testimonial', $id, (string)$t); 
     flash('Testimonial deleted.'); 
 }
 redirect('manage-testimonials.php');}
include '_header.php'; $edit=['id'=>0,'author_name'=>'','content'=>'','rating'=>5,'active'=>1];
if(isset($_GET['edit'])){$s=db()->prepare('SELECT * FROM testimonials WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch()?:$edit;}
$rows=db()->query('SELECT * FROM testimonials ORDER BY id DESC')->fetchAll(); ?>
<h1>Testimonials</h1><form class="at" method="post"><?=csrf()?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=$edit['id']?>">
<label>Author Name *</label><input name="author_name" required value="<?=e($edit['author_name'])?>">
<label>Testimonial Content *</label><textarea name="content" rows="4" required><?=e($edit['content'])?></textarea>
<label>Rating (1-5)</label><input type="number" name="rating" min="1" max="5" value="<?=$edit['rating']?>">
<label><input type="checkbox" name="active" style="width:auto" <?=$edit['active']?'checked':''?>> Active (shown on website)</label>
<button class="btn" style="margin-top:10px; margin-bottom:20px;"><?=$edit['id']?'Update':'Add'?> Testimonial</button>
<?php if($edit['id']): ?><a class="btn alt" href="manage-testimonials.php">Cancel</a><?php endif; ?>
</form>
<table><tr><th>Author</th><th>Content snippet</th><th>Rating</th><th>Active</th><th>Actions</th></tr>
<?php foreach($rows as $r): ?><tr><td><?=e($r['author_name'])?></td><td><?=e(mb_strimwidth($r['content'],0,60,'...'))?></td><td><?=$r['rating']?> ★</td><td><?=$r['active']?'Yes':'No'?></td><td>
<div class="table-actions"><a class="btn sm alt" href="?edit=<?=$r['id']?>" >Edit</a><form class="inl" method="post" ><?=csrf()?><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn sm red" name="action" value="delete"  onclick="return confirm('Delete testimonial?')">Delete</button></form></div></td></tr><?php endforeach; ?></table>
<?php include '_footer.php'; ?>