<?php $title='Job Openings'; $sub='Search and apply for current opportunities'; include 'includes/header.php';
$q=trim($_GET['q']??'');$cat=$_GET['category']??'';$loc=trim($_GET['location']??'');$type=$_GET['type']??'';
$w=["status='published'"];$a=[];
if($q!==''){$w[]='(title LIKE ? OR description LIKE ?)';$a[]="%$q%";$a[]="%$q%";}
if(in_array($cat,['IT','Non-IT'])){$w[]='category=?';$a[]=$cat;}
if($loc!==''){$w[]='location LIKE ?';$a[]="%$loc%";}
if($type!==''){$w[]='job_type=?';$a[]=$type;}
$st=db()->prepare('SELECT * FROM jobs WHERE '.implode(' AND ',$w).' ORDER BY id DESC');$st->execute($a);$jobs=$st->fetchAll(); ?>
<section class="s"><div class="wrap"><form class="filters" method="get"><input name="q" placeholder="Keyword" value="<?=e($q)?>"><input name="location" placeholder="Location" value="<?=e($loc)?>">
<select name="category"><option value="">All Categories</option><?php foreach(['IT','Non-IT'] as $c): ?><option <?=$cat===$c?'selected':''?>><?=$c?></option><?php endforeach; ?></select>
<select name="type"><option value="">All Types</option><?php foreach(['Full-time','Part-time','Contract','Internship'] as $c): ?><option <?=$type===$c?'selected':''?>><?=$c?></option><?php endforeach; ?></select>
<button class="btn" style="margin:0">Search</button></form>
<p><?=count($jobs)?> job(s) found</p>
<?php foreach($jobs as $j): ?><div class="job"><div><h3 style="margin:0"><?=e($j['title'])?></h3><span class="tag"><?=e($j['category'])?></span><span class="tag"><?=e($j['location'])?></span><span class="tag"><?=e($j['job_type'])?></span><span class="tag"><?=e($j['experience'])?></span>
<p style="margin:6px 0 0;color:#5b6475"><?=e(mb_strimwidth($j['description'],0,160,'...'))?></p></div><a class="btn sm" href="apply.php?id=<?=$j['id']?>">Apply Now</a></div><?php endforeach; ?></div></section>
<?php include 'includes/footer.php'; ?>
