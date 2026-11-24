<?php require_once 'includes/functions.php';
if($_SERVER['REQUEST_METHOD']==='POST'){$err=save_inquiry('contact');if($err==='')flash('Thank you! We will get back to you soon.');else flash($err,'err');redirect(basename($_SERVER['SCRIPT_NAME']));}
$title='Contact Us';$sub='We would love to hear from you';include 'includes/header.php'; ?>
<section class="s"><div class="wrap"><?=flash()?>
<div style="display:flex; justify-content:center; gap:12px; flex-wrap:wrap; margin-bottom:30px; font-weight:600;">
    <span class="tag" style="font-size:14px; padding:9px 18px; display:inline-flex; align-items:center; gap:6px;">📍 <?=e(CO['address'])?></span>
    <a href="tel:<?=e(CO['phone'])?>" style="text-decoration:none;"><span class="tag" style="font-size:14px; padding:9px 18px; display:inline-flex; align-items:center; gap:6px; cursor:pointer; color:var(--n);">📞 <?=e(CO['phone'])?></span></a>
    <?php if (!empty(CO['whatsapp'])): ?>
        <a href="https://wa.me/<?= preg_replace('/[^\d]/', '', CO['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" style="text-decoration:none;"><span class="tag" style="font-size:14px; padding:9px 18px; display:inline-flex; align-items:center; gap:6px; cursor:pointer; background:#dcfce7; color:#15803d; border-color:#86efac;">💬 WhatsApp: <?=e(CO['whatsapp'])?></span></a>
    <?php endif; ?>
    <a href="mailto:<?=e(CO['email'])?>" style="text-decoration:none;"><span class="tag" style="font-size:14px; padding:9px 18px; display:inline-flex; align-items:center; gap:6px; cursor:pointer; color:var(--n);">✉️ <?=e(CO['email'])?></span></a>
    <?php if (!empty(CO['hours'])): ?>
        <span class="tag" style="font-size:14px; padding:9px 18px; display:inline-flex; align-items:center; gap:6px; color:#475569;">🕒 <?=e(CO['hours'])?></span>
    <?php endif; ?>
</div>
<form class="f" method="post" data-validate><?=csrf()?><input class="hp" name="website" tabindex="-1" autocomplete="off">
<label>Name *</label><input name="name" required maxlength="100"><label>Email *</label><input type="email" name="email" required><label>Phone</label><input name="phone">
<label>Subject</label><input name="subject">
<label>Message / Requirements *</label><textarea name="message" rows="5" required></textarea><button class="btn" type="submit">Send Message</button></form></div></section>
<?php include 'includes/footer.php'; ?>
