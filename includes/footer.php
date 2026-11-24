</main>
<footer class="footer"><div class="wrap grid4">
<div><img src="<?=BASE_URL?>/assets/images/adisoma-logo.png" class="logo-f" alt=""><p><?=e(CO['tagline']??'Your Talent Partner for a Brighter Tomorrow.')?></p></div>
<div><h4>Company</h4><a href="<?=BASE_URL?>/about.php">About</a><a href="<?=BASE_URL?>/services.php">Services</a><a href="<?=BASE_URL?>/blogs.php">Insights & Blogs</a><a href="<?=BASE_URL?>/contact.php">Contact</a></div>
<div><h4>Hiring</h4><a href="<?=BASE_URL?>/jobs.php">Job Openings</a><a href="<?=BASE_URL?>/it-recruitment.php">IT Recruitment</a><a href="<?=BASE_URL?>/non-it-recruitment.php">Non-IT Recruitment</a><a href="<?=BASE_URL?>/employers.php">Hire Talent</a></div>
<div><h4>Contact</h4>
<div class="footer-contact-line" style="display:flex; align-items:flex-start; gap:10px; margin:8px 0; color:#94a3b8; font-size:14px; line-height:1.5;">
    <span class="footer-contact-icon" style="font-size:16px; flex-shrink:0; line-height:1;">📍</span>
    <span><?=e(CO['address'])?></span>
</div>
<a href="tel:<?=e(CO['phone'])?>" class="footer-contact-link" style="display:flex; align-items:center; gap:10px; margin:8px 0; color:#94a3b8; text-decoration:none; font-size:14px; line-height:1.5;">
    <span class="footer-contact-icon" style="font-size:16px; flex-shrink:0; line-height:1;">📞</span>
    <span><?=e(CO['phone'])?></span>
</a>
<?php if (!empty(CO['whatsapp'])): ?>
<a href="https://wa.me/<?= preg_replace('/[^\d]/', '', CO['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" class="footer-contact-link" style="display:flex; align-items:center; gap:10px; margin:8px 0; color:#94a3b8; text-decoration:none; font-size:14px; line-height:1.5;">
    <span class="footer-contact-icon" style="font-size:16px; flex-shrink:0; line-height:1;">💬</span>
    <span><?=e(CO['whatsapp'])?></span>
</a>
<?php endif; ?>
<a href="mailto:<?=e(CO['email'])?>" class="footer-contact-link" style="display:flex; align-items:center; gap:10px; margin:8px 0; color:#94a3b8; text-decoration:none; font-size:14px; line-height:1.5;">
    <span class="footer-contact-icon" style="font-size:16px; flex-shrink:0; line-height:1;">✉️</span>
    <span><?=e(CO['email'])?></span>
</a>
<p class="soc"><?php
$socs = [
    'linkedin' => 'LinkedIn',
    'facebook' => 'Facebook',
    'twitter' => 'Twitter',
    'instagram' => 'Instagram',
    'youtube' => 'YouTube'
];
foreach($socs as $sk => $sl):
    $surl = CO[$sk] ?? '';
    if(!empty($surl) && $surl !== ''):
?><a href="<?=e($surl)?>" target="<?=$surl==='#'?'_self':'_blank'?>" rel="noopener noreferrer"><?=$sl?></a> <?php
    endif;
endforeach;
?></p></div>
</div><div class="copy">&copy; <?=date('Y')?> <?=e(CO['name']??'ADISOMA Solution')?>. All rights reserved. <a href="<?=BASE_URL?>/admin/login.php">Admin</a></div>
<script src="<?=BASE_URL?>/assets/js/main.js?v=<?=asset_v()?>"></script></body></html>
