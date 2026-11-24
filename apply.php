<?php require_once 'includes/functions.php';
$id=(int)($_GET['id']??0);$job=null;
if($id){$s=db()->prepare("SELECT * FROM jobs WHERE id=? AND status='published'");$s->execute([$id]);$job=$s->fetch();if(!$job)redirect('jobs.php');}
if($_SERVER['REQUEST_METHOD']==='POST'){$err=save_application($id);if($err==='')flash('Application submitted. Thank you!');else flash($err,'err');redirect($_SERVER['REQUEST_URI']);}
$title='Apply'.($job?': '.$job['title']:'');$sub='Submit your application and resume';include 'includes/header.php'; ?>
<section class="s"><div class="wrap"><?=flash()?>
<?php if($job): ?>
<div class="job-apply-banner">
    <div style="font-size:12px; text-transform:uppercase; font-weight:600; letter-spacing:0.5px; opacity:0.85;">Applying for position</div>
    <div style="font-size:20px; font-weight:700; color:#c2410c; margin:4px 0;"><?=e($job['title'])?></div>
    <div style="font-size:14px;"><?=e($job['location'])?> &middot; <?=e($job['job_type'])?> &middot; <?=e($job['category'])?></div>
</div>
<?php endif; ?>
<form class="f" method="post" enctype="multipart/form-data" data-validate><?=csrf()?>
<label>Full Name *</label><input name="name" required maxlength="100"><label>Email *</label><input type="email" name="email" required>
<label>Phone</label><input name="phone" maxlength="30"><label>Total Experience</label><input name="experience" placeholder="e.g. 3 years">
<label>Resume (PDF/DOC/DOCX, max 2 MB) *</label><input type="file" name="resume" accept=".pdf,.doc,.docx" required>
<label>Message</label><textarea name="message" rows="4"></textarea><button class="btn" type="submit">Submit Application</button></form></div></section>
<?php include 'includes/footer.php'; ?>
