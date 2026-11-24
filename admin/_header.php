<?php require_once __DIR__.'/../includes/functions.php'; admin_only(); $cur=basename($_SERVER['SCRIPT_NAME']);
$nav=['dashboard.php'=>'Dashboard','manage-jobs.php'=>'Jobs','manage-blogs.php'=>'Blogs','applications.php'=>'Applications','inquiries.php'=>'Inquiries','manage-services.php'=>'Services','manage-testimonials.php'=>'Testimonials','manage-admins.php'=>'Admins','logs.php'=>'Logs']; ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin | ADISOMA</title>
<link rel="stylesheet" href="<?=BASE_URL?>/assets/css/style.css?v=2.2">
<link rel="stylesheet" href="<?=BASE_URL?>/assets/css/responsive.css?v=2.2">
</head><body>
<div class="admin-mobile-bar">
    <a href="dashboard.php" class="admin-mobile-logo">
        <img src="<?=BASE_URL?>/assets/images/adisoma-logo.png" alt="ADISOMA">
    </a>
    <button class="burger" id="adminBurgerBtn" aria-label="Toggle Admin Navigation">&#9776;</button>
</div>
<div class="admin-drawer-backdrop" id="adminBackdrop"></div>
<div class="ad">
    <aside class="side" id="adminSidebar">
        <div class="side-logo-wrap">
            <a href="dashboard.php">
                <img src="<?=BASE_URL?>/assets/images/adisoma-logo.png" alt="ADISOMA">
            </a>
            <button class="side-close-btn" id="adminCloseBtn" aria-label="Close Sidebar">&times;</button>
        </div>
        <nav class="side-nav">
            <?php foreach($nav as $u=>$l): ?>
                <a href="<?=$u?>" class="<?=$cur===$u?'active':''?>"><?=$l?></a>
            <?php endforeach; ?>
        </nav>
        <div class="side-bottom">
            <a href="<?=BASE_URL?>/index.php" target="_blank" class="side-link-alt">🌐 View Live Site</a>
            <a href="logout.php" class="side-link-red">🚪 Logout</a>
        </div>
    </aside>
    <main class="main">
        <?=flash()?>
