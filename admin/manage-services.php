<?php require_once __DIR__.'/../includes/functions.php'; admin_only();
if($_SERVER['REQUEST_METHOD']==='POST'){ if(!csrf_ok())die('Bad token'); $a=post('action');$id=(int)post('id');
 if($a==='save'&&post('title')!==''&&post('description')!==''){ 
  $image_url = post('image_url');
  if(!empty($_FILES['image_upload']['tmp_name'])) {
      $ext = strtolower(pathinfo($_FILES['image_upload']['name'], PATHINFO_EXTENSION));
      if(in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
          $fname = uniqid().'.'.$ext;
          if(!is_dir(__DIR__.'/../uploads/images/')) mkdir(__DIR__.'/../uploads/images/', 0777, true);
          move_uploaded_file($_FILES['image_upload']['tmp_name'], __DIR__.'/../uploads/images/'.$fname);
          $image_url = BASE_URL . '/uploads/images/' . $fname;
      }
  }
  $d=[post('title'),post('description'),$image_url,(int)post('sort_order'),isset($_POST['active'])?1:0];
  if($id) {
      db()->prepare('UPDATE services SET title=?,description=?,image_url=?,sort_order=?,active=? WHERE id=?')->execute([...$d,$id]);
      log_action('Updated', 'Service', $id, post('title'));
  } else {
      db()->prepare('INSERT INTO services(title,description,image_url,sort_order,active) VALUES(?,?,?,?,?)')->execute($d);
      log_action('Created', 'Service', db()->lastInsertId(), post('title'));
  }
  flash('Service saved.');}
 if($a==='delete'){
     $title = db()->query("SELECT title FROM services WHERE id=$id")->fetchColumn();
     db()->prepare('DELETE FROM services WHERE id=?')->execute([$id]);
     log_action('Deleted', 'Service', $id, $title);
     flash('Service deleted.');
 }
 redirect('manage-services.php');}
include '_header.php'; $edit=['id'=>0,'title'=>'','description'=>'','image_url'=>'','sort_order'=>0,'active'=>1];
if(isset($_GET['edit'])){$s=db()->prepare('SELECT * FROM services WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch()?:$edit;}
$rows=db()->query('SELECT * FROM services ORDER BY sort_order')->fetchAll(); ?>
<h1>Services</h1><form class="at" method="post" enctype="multipart/form-data"><?=csrf()?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=$edit['id']?>">
<label>Title</label><input name="title" required value="<?=e($edit['title'])?>"><label>Description</label><textarea name="description" rows="3" required><?=e($edit['description'])?></textarea>
<label>Image URL (Paste link OR upload below)</label>
<div style="display:flex; gap:10px; align-items:center;">
    <input type="text" name="image_url" id="image_url_field" value="<?=e($edit['image_url'])?>" placeholder="https://...">
    <button type="button" class="btn sm alt" style="margin:0; white-space:nowrap;" onclick="navigator.clipboard.writeText(document.getElementById('image_url_field').value); alert('URL Copied!')">Copy URL</button>
</div>
<label>Upload New Image</label><input type="file" name="image_upload" accept="image/*">
<?php if($edit['image_url']): ?><div style="margin-top:10px;"><img src="<?=e($edit['image_url'])?>" style="max-height:80px; border-radius:6px;"></div><?php endif; ?>
<label>Sort Order</label><input type="number" name="sort_order" value="<?=$edit['sort_order']?>"><label><input type="checkbox" name="active" style="width:auto" <?=$edit['active']?'checked':''?>> Active (shown on website)</label>
<button class="btn" style="margin-top:15px; margin-bottom:15px;"><?=$edit['id']?'Update':'Add'?> Service</button>
<?php if($edit['id']): ?><a class="btn alt" href="manage-services.php">Cancel</a><?php endif; ?>
</form>
<table><tr><th>Image</th><th>Title</th><th>Order</th><th>Active</th><th>Actions</th></tr><?php foreach($rows as $r): ?><tr>
<td><?php if($r['image_url']): ?><img src="<?=e($r['image_url'])?>" style="width:50px; height:50px; object-fit:cover; border-radius:4px;"><?php else: ?><span style="color:#aaa;">None</span><?php endif; ?></td>
<td><?=e($r['title'])?></td><td><?=$r['sort_order']?></td><td><?=$r['active']?'Yes':'No'?></td><td>
<div class="table-actions"><a class="btn sm alt" href="?edit=<?=$r['id']?>" >Edit</a><form class="inl" method="post" ><?=csrf()?><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn sm red" name="action" value="delete"  onclick="return confirm('Delete?')">Delete</button></form></div></td></tr><?php endforeach; ?></table>
<?php include '_footer.php'; ?>