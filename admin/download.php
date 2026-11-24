<?php require_once __DIR__.'/../includes/functions.php'; admin_only();
$s=db()->prepare('SELECT name,resume FROM applications WHERE id=?');$s->execute([(int)($_GET['id']??0)]);$r=$s->fetch();
$p=$r?__DIR__.'/../uploads/resumes/'.basename($r['resume']):'';
if(!$r||!is_file($p)){http_response_code(404);exit('File not found');}
$ext=pathinfo($p,PATHINFO_EXTENSION);header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.preg_replace('/[^A-Za-z0-9_-]/','_',$r['name']).'_resume.'.$ext.'"');header('Content-Length: '.filesize($p));readfile($p);
