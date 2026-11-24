<?php $title='Non-IT Recruitment'; $sub='Sales, HR, finance, operations and customer support talent'; include 'includes/header.php';
$st=db()->prepare("SELECT * FROM jobs WHERE status='published' AND category=? ORDER BY id DESC"); $st->execute(['Non-IT']); ?>
<section class="s"><div class="wrap"><p>We source and screen professionals for Non-IT Recruitment roles. Browse current openings or <a href="employers.php">send us a hiring request</a>.</p>
<?php foreach($st as $j): ?><div class="job"><div><h3 style="margin:0"><?=e($j['title'])?></h3><span class="tag"><?=e($j['location'])?></span><span class="tag"><?=e($j['experience'])?></span></div><a class="btn sm" href="apply.php?id=<?=$j['id']?>">Apply</a></div><?php endforeach; ?></div></section>
<?php include 'includes/footer.php'; ?>
