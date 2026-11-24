<?php
require_once __DIR__.'/../config/database.php';
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function csrf(){return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">';}
function csrf_ok(){return isset($_POST['csrf'])&&hash_equals($_SESSION['csrf'],$_POST['csrf']);}
function redirect($u){header("Location: $u");exit;}
function flash($m=null,$t='ok'){if($m){$_SESSION['f']=[$m,$t];return;}if(!empty($_SESSION['f'])){[$m,$t]=$_SESSION['f'];unset($_SESSION['f']);return "<div class='alert $t'>".e($m)."</div>";}return '';}
function admin_only(){if(empty($_SESSION['admin']))redirect('login.php');}
function post($k){return trim($_POST[$k]??'');}

function log_action($action, $type, $entity_id, $details = '') {
    $admin_id = $_SESSION['admin'] ?? null;
    if ($admin_id) {
        db()->prepare('INSERT INTO admin_logs (admin_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)')
          ->execute([$admin_id, $action, $type, $entity_id, $details]);
    }
}

function save_inquiry(string $type):string{
 if(!csrf_ok())return 'Invalid session. Reload and retry.';
 if(post('website')!=='')return ''; // honeypot
 $n=post('name');$em=post('email');$m=post('message');
 if($n===''||!filter_var($em,FILTER_VALIDATE_EMAIL)||$m==='')return 'Please provide a valid name, email and message.';
 db()->prepare('INSERT INTO inquiries(type,name,email,phone,company,subject,message) VALUES(?,?,?,?,?,?,?)')
  ->execute([$type,$n,$em,post('phone'),post('company'),post('subject'),$m]);
 return '';
}
function save_application(int $jobId):string{
 if(!csrf_ok())return 'Invalid session. Reload and retry.';
 $n=post('name');$em=post('email');
 if($n===''||!filter_var($em,FILTER_VALIDATE_EMAIL))return 'Please enter a valid name and email.';
 $f=$_FILES['resume']??null;
 if(!$f||$f['error']!==UPLOAD_ERR_OK)return 'Please upload your resume.';
 if($f['size']>2*1024*1024)return 'Resume must be under 2 MB.';
 $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
 if(!in_array($ext,['pdf','doc','docx']))return 'Resume must be PDF, DOC or DOCX.';
 $name=bin2hex(random_bytes(12)).'.'.$ext;
 if(!is_dir(__DIR__.'/../uploads/resumes/')) mkdir(__DIR__.'/../uploads/resumes/', 0777, true);
 if(!move_uploaded_file($f['tmp_name'],__DIR__.'/../uploads/resumes/'.$name))return 'Upload failed.';
 db()->prepare('INSERT INTO applications(job_id,name,email,phone,experience,message,resume) VALUES(?,?,?,?,?,?,?)')
  ->execute([$jobId?:null,$n,$em,post('phone'),post('experience'),post('message'),$name]);
 return '';
}
