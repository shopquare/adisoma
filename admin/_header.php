<?php require_once __DIR__.'/../includes/functions.php'; admin_only(); $cur=basename($_SERVER['SCRIPT_NAME']);
$nav=['dashboard.php'=>'Dashboard','manage-jobs.php'=>'Jobs','applications.php'=>'Applications','inquiries.php'=>'Inquiries','manage-services.php'=>'Services','manage-testimonials.php'=>'Testimonials','manage-admins.php'=>'Admins','logs.php'=>'Logs']; ?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin | ADISOMA</title>
<link rel="stylesheet" href="<?=BASE_URL?>/assets/css/style.css"><link rel="stylesheet" href="<?=BASE_URL?>/assets/css/responsive.css"></head><body>
<div class="ad"><aside class="side"><img src="<?=BASE_URL?>/assets/images/adisoma-logo.png" alt="">
<?php foreach($nav as $u=>$l): ?><a href="<?=$u?>" class="<?=$cur===$u?'active':''?>"><?=$l?></a><?php endforeach; ?>
<a href="<?=BASE_URL?>/index.php" target="_blank">View Site</a><a href="logout.php">Logout</a></aside><main class="main"><?=flash()?>