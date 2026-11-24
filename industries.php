<?php $title='Industries We Serve'; $sub='Domain expertise across sectors'; include 'includes/header.php';
$ind=['Information Technology','Banking & Finance','Healthcare','Manufacturing','Retail & E-commerce','Telecom','Education','Logistics','Real Estate'];?>
<section class="s"><div class="wrap grid3"><?php foreach($ind as $i): ?><div class="card"><h3><?=e($i)?></h3><p>Talent solutions tailored to the needs of the <?=e(strtolower($i))?> sector.</p></div><?php endforeach; ?></div></section>
<?php include 'includes/footer.php'; ?>
