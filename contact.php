<?php require_once 'includes/functions.php';
if($_SERVER['REQUEST_METHOD']==='POST'){$err=save_inquiry('contact');if($err==='')flash('Thank you! We will get back to you soon.');else flash($err,'err');redirect(basename($_SERVER['SCRIPT_NAME']));}
$title='Contact Us';$sub='We would love to hear from you';include 'includes/header.php'; ?>
<section class="s"><div class="wrap"><?=flash()?>
<div style="display:flex; justify-content:center; gap:12px; flex-wrap:wrap; margin-bottom:30px; font-weight:500;">
    <span class="tag" style="font-size:14px; padding:8px 16px;">📍 <?=e(CO['address'])?></span>
    <span class="tag" style="font-size:14px; padding:8px 16px;">📞 <?=e(CO['phone'])?></span>
    <span class="tag" style="font-size:14px; padding:8px 16px;">✉️ <?=e(CO['email'])?></span>
</div>
<form class="f" method="post" data-validate><?=csrf()?><input class="hp" name="website" tabindex="-1" autocomplete="off">
<label>Name *</label><input name="name" required maxlength="100"><label>Email *</label><input type="email" name="email" required><label>Phone</label><input name="phone">
<label>Subject</label><input name="subject">
<label>Message / Requirements *</label><textarea name="message" rows="5" required></textarea><button class="btn" type="submit">Send Message</button></form></div></section>
<?php include 'includes/footer.php'; ?>
