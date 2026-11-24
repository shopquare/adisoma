<?php require_once __DIR__.'/../includes/functions.php';
$setup=(int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn()===0; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!csrf_ok())$err='Invalid session.';
 else{$u=post('username');$p=$_POST['password']??'';
  if($setup){ if($u===''||strlen($p)<8)$err='Username required and password must be 8+ characters.';
   else{db()->prepare('INSERT INTO admins(username,password) VALUES(?,?)')->execute([$u,password_hash($p,PASSWORD_DEFAULT)]);flash('Admin created. Please log in.');redirect('login.php');}}
  else{$s=db()->prepare('SELECT * FROM admins WHERE username=?');$s->execute([$u]);$a=$s->fetch();
   usleep(300000);
   if($a&&password_verify($p,$a['password'])){session_regenerate_id(true);$_SESSION['admin']=$a['id'];redirect('dashboard.php');}
   $err='Invalid username or password.';}}}
?><!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Login</title>
<link rel="stylesheet" href="<?=BASE_URL?>/assets/css/style.css"></head><body style="background:var(--bg)"><div class="wrap" style="padding-top:60px">
<form class="f" method="post" style="max-width:400px"><p style="text-align:center"><img src="<?=BASE_URL?>/assets/images/adisoma-logo.png" style="height:80px" alt=""></p>
<h2 style="color:var(--n);text-align:center"><?=$setup?'Create Admin Account':'Admin Login'?></h2><?=flash()?><?php if($err)echo '<div class="alert err">'.e($err).'</div>'; ?>
<?=csrf()?><label>Username</label><input name="username" required><label>Password</label><input type="password" name="password" required>
<button class="btn" style="width:100%"><?=$setup?'Create Account':'Login'?></button></form></div></body></html>
