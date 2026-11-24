<?php require_once 'includes/functions.php';
$id=(int)($_GET['id']??0);$job=null;
if($id){$s=db()->prepare("SELECT * FROM jobs WHERE id=? AND status='published'");$s->execute([$id]);$job=$s->fetch();if(!$job)redirect('jobs.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){$err=save_application($id);if($err==='')flash('Application submitted. Thank you!');else flash($err,'err');redirect($_SERVER['REQUEST_URI']);}
$title='Apply'.($job?': '.$job['title']:'');$sub='Submit your application and resume';include 'includes/header.php'; ?>
<section class="s"><div class="wrap"><?=flash()?><?php if($job): ?><p style="text-align:center"><b><?=e($job['title'])?></b> &middot; <?=e($job['location'])?></p><?php endif; ?>
<form class="f" method="post" enctype="multipart/form-data" data-validate><?=csrf()?>
<label>Full Name *</label><input name="name" required maxlength="100"><label>Email *</label><input type="email" name="email" required>
<label>Phone</label><input name="phone" maxlength="30"><label>Total Experience</label><input name="experience" placeholder="e.g. 3 years">
<label>Resume (PDF/DOC/DOCX, max 2 MB) *</label><input type="file" name="resume" accept=".pdf,.doc,.docx" required>
<label>Message</label><textarea name="message" rows="4"></textarea><button class="btn" type="submit">Submit Application</button></form></div></section>
<?php include 'includes/footer.php'; ?>
