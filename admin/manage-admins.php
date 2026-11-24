<?php require_once __DIR__.'/../includes/functions.php'; admin_only();
if($_SERVER['REQUEST_METHOD']==='POST'){ if(!csrf_ok())die('Bad token'); $a=post('action');
 if($a==='save'){
     $u = post('username'); $p = post('password');
     if($u!=='' && strlen($p)>=8){
         try {
             db()->prepare('INSERT INTO admins(username,password) VALUES(?,?)')->execute([$u, password_hash($p, PASSWORD_DEFAULT)]);
             log_action('Created', 'Admin', db()->lastInsertId(), "Username: $u");
             flash('Admin added.');
         } catch(Exception $e) { flash('Username might already exist.', 'err'); }
     } else { flash('Username required and password 8+ chars.', 'err'); }
 }
 if($a==='delete'){
     $id = (int)post('id');
     if($id === $_SESSION['admin']) { flash('Cannot delete yourself.','err'); }
     else {
         $adm = db()->query("SELECT username FROM admins WHERE id=$id")->fetchColumn();
         db()->prepare('DELETE FROM admins WHERE id=?')->execute([$id]);
         log_action('Deleted', 'Admin', $id, "Username: $adm");
         flash('Admin deleted.');
     }
 }
 redirect('manage-admins.php');
}
include '_header.php';
$rows=db()->query('SELECT id, username, created_at FROM admins ORDER BY id')->fetchAll();
?>
<h1>Manage Admins</h1>
<form class="at" method="post"><?=csrf()?><input type="hidden" name="action" value="save">
<label>New Username *</label><input name="username" required>
<label>New Password (min 8 chars) *</label><input type="password" name="password" required>
<button class="btn" style="margin-top:15px; margin-bottom:15px;">Add Admin</button></form>
<table><tr><th>ID</th><th>Username</th><th>Created</th><th>Actions</th></tr>
<?php foreach($rows as $r): ?><tr><td><?=$r['id']?></td><td><?=e($r['username'])?></td><td><?=$r['created_at']?></td>
<td><?php if($r['id']!=$_SESSION['admin']): ?><form class="inl" method="post"><?=csrf()?><input type="hidden" name="id" value="<?=$r['id']?>"><button class="btn sm red" name="action" value="delete" onclick="return confirm('Delete admin?')">Delete</button></form><?php else: ?><span class="tag">You</span><?php endif; ?></td></tr><?php endforeach; ?></table>
<?php include '_footer.php'; ?>