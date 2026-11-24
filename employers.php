<?php require_once 'includes/functions.php';
if($_SERVER['REQUEST_METHOD']==='POST'){$err=save_inquiry('employer');if($err==='')flash('Thank you! We will get back to you soon.');else flash($err,'err');redirect(basename($_SERVER['SCRIPT_NAME']));}
$title='For Employers';$sub='Tell us who you need and we will start sourcing';include 'includes/header.php'; ?>
<section class="s"><div class="wrap"><?=flash()?>
<form class="f" method="post" data-validate><?=csrf()?><input class="hp" name="website" tabindex="-1" autocomplete="off">
<label>Name *</label><input name="name" required maxlength="100"><label>Email *</label><input type="email" name="email" required><label>Phone</label><input name="phone">
<label>Company</label><input name="company"><label>Position(s) to Hire</label><input name="subject">
<label>Message / Requirements *</label><textarea name="message" rows="5" required></textarea><button class="btn" type="submit">Send Hiring Request</button></form></div></section>
<?php include 'includes/footer.php'; ?>
