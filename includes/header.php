<?php require_once __DIR__.'/functions.php'; $title=$title??'Home'; $sub=$sub??''; $cur=basename($_SERVER['SCRIPT_NAME']); ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($title)?> | ADISOMA Solution</title>
<link rel="stylesheet" href="<?=BASE_URL?>/assets/css/style.css?v=<?=asset_v()?>"><link rel="stylesheet" href="<?=BASE_URL?>/assets/css/responsive.css?v=<?=asset_v()?>"></head><body>
<?php include __DIR__.'/navbar.php'; if($sub!==''): ?>
<section class="page-hero"><div class="wrap"><h1><?=e($title)?></h1><p><?=e($sub)?></p></div></section><?php endif; ?>
