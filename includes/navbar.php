<?php $links=['index.php'=>'Home','about.php'=>'About','services.php'=>'Services','industries.php'=>'Industries','jobs.php'=>'Jobs','employers.php'=>'For Employers','job-seekers.php'=>'Job Seekers','contact.php'=>'Contact']; ?>
<header class="nav"><div class="wrap nav-in"><a href="<?=BASE_URL?>/index.php"><img src="<?=BASE_URL?>/assets/images/adisoma-logo.png" alt="ADISOMA Solution" class="logo"></a>
<button class="burger" id="burgerBtn" aria-label="Toggle navigation">&#9776;</button>
<nav class="menu" id="navMenu"><?php foreach($links as $u=>$l): ?><a href="<?=BASE_URL.'/'.$u?>" class="<?=$cur===$u?'active':''?>"><?=$l?></a><?php endforeach; ?></nav></div></header>
<div class="nav-backdrop" id="navBackdrop"></div>
