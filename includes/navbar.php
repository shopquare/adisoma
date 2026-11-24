<?php $links = get_nav_links('header'); ?>
<header class="nav"><div class="wrap nav-in"><a href="<?=BASE_URL?>/index.php"><img src="<?=BASE_URL?>/assets/images/adisoma-logo.png" alt="ADISOMA Solution" class="logo"></a>
<button class="burger" id="burgerBtn" aria-label="Toggle navigation">&#9776;</button>
<nav class="menu" id="navMenu"><?php foreach($links as $u=>$l): 
    $href = (str_starts_with($u, 'http://') || str_starts_with($u, 'https://')) ? $u : BASE_URL.'/'.$u;
?><a href="<?=$href?>" class="<?=$cur===basename($u)?'active':''?>"><?=$l?></a><?php endforeach; ?></nav></div></header>
<div class="nav-backdrop" id="navBackdrop"></div>
