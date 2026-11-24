<?php $title='Services'; $sub='Complete hiring solutions for every need'; include 'includes/header.php';
$svc=db()->query('SELECT * FROM services WHERE active=1 ORDER BY sort_order')->fetchAll(); ?>
<section class="s"><div class="wrap grid3">
<?php foreach($svc as $s): 
    $s_url = BASE_URL . '/service-detail.php?' . (!empty($s['slug']) ? 'slug=' . urlencode($s['slug']) : 'id=' . $s['id']);
?>
<div class="card" style="display:flex; flex-direction:column; justify-content:space-between; transition:all 0.3s ease; padding:0; overflow:hidden;">
    <div>
        <a href="<?= $s_url ?>" style="display:block; overflow:hidden;">
            <img src="<?= e($s['image_url'] ?: 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=600&q=80') ?>" 
                 alt="<?= e($s['title']) ?>" 
                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=600&q=80';"
                 style="width:100%; height:200px; object-fit:cover; display:block; transition:transform 0.3s ease;">
        </a>
        <div style="padding:22px 22px 10px;">
            <h3 style="margin:0 0 10px; font-size:20px; font-weight:700;">
                <a href="<?= $s_url ?>" style="color:var(--n); text-decoration:none; transition:color 0.2s;"><?= e($s['title']) ?></a>
            </h3>
            <p style="color:#64748b; font-size:14px; line-height:1.6; margin:0 0 15px;"><?= e($s['description']) ?></p>
        </div>
    </div>
    <div style="padding:0 22px 22px;">
        <a href="<?= $s_url ?>" class="btn sm" style="width:100%; text-align:center; display:flex; align-items:center; justify-content:center; gap:6px; font-weight:600;">
            Explore Service ➔
        </a>
    </div>
</div>
<?php endforeach; ?>
</div></section>
<?php include 'includes/footer.php'; ?>
