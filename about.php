<?php
require_once __DIR__ . '/includes/functions.php';

$settings = get_site_settings();
$title = !empty($settings['about_page_title']) ? $settings['about_page_title'] : 'About Us';
$sub = !empty($settings['about_page_subtitle']) ? $settings['about_page_subtitle'] : 'Your Talent Partner for a Brighter Tomorrow';
$meta_description = 'Learn about ADISOMA Solution: our history, mission, vision, and core values driving premier IT and Non-IT recruitment and staffing.';

include 'includes/header.php';

// Fetch all dynamic customizable sections
$sections = get_about_sections(true);
?>

<?php if (empty($sections)): ?>
<!-- Fallback if no sections configured yet -->
<section class="s"><div class="wrap">
    <div class="about-grid" style="margin-bottom:60px;">
        <div class="about-content">
            <h2 style="color:var(--n); font-size:32px; margin-top:0;">Who We Are</h2>
            <p style="font-size:17px; color:var(--g); line-height:1.8;">ADISOMA Solution is a premier recruitment and staffing partner dedicated to bridging the gap between exceptional talent and industry-leading organizations. With years of experience across IT and Non-IT sectors, we deliver customized hiring strategies designed for long-term success.</p>
        </div>
        <div class="about-img-box">
            <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=800&q=80" class="about-img" alt="Team">
        </div>
    </div>
</div></section>
<?php else: ?>

    <?php foreach ($sections as $sec): 
        $bg = $sec['bg_style'] ?? 'white';
        $isAccent = ($bg === 'accent');
        $isLight  = ($bg === 'light');

        $sectionStyle = 'padding:65px 0;';
        if ($isAccent) {
            $sectionStyle .= ' background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color:#ffffff;';
        } elseif ($isLight) {
            $sectionStyle .= ' background: #f8fafc; border-top:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0;';
        } else {
            $sectionStyle .= ' background: #ffffff;';
        }
    ?>
    <section class="s <?= $isLight ? 'bg' : '' ?>" style="<?= $sectionStyle ?>">
        <div class="wrap">

            <?php if ($sec['section_type'] === 'text_image_right'): ?>
                <!-- Layout: Text on Left, Image on Right -->
                <div class="about-grid">
                    <div class="about-content">
                        <?php if (!empty($sec['subtitle'])): ?>
                            <span class="process-subtitle"><?= e($sec['subtitle']) ?></span>
                        <?php endif; ?>
                        <h2 style="color:<?= $isAccent ? '#fff' : 'var(--n)' ?>; font-size:32px; margin-top:8px; margin-bottom:20px; font-weight:700; line-height:1.3;">
                            <?= e($sec['title']) ?>
                        </h2>
                        <div style="font-size:16px; color:<?= $isAccent ? 'rgba(255,255,255,0.9)' : 'var(--g)' ?>; line-height:1.8; margin-bottom:25px;">
                            <?= format_markdown_content($sec['content']) ?>
                        </div>
                        <?php if (!empty($sec['btn_text'])): ?>
                            <a class="btn" href="<?= e($sec['btn_url'] ?: 'contact.php') ?>" style="margin-top:10px; display:inline-block;">
                                <?= e($sec['btn_text']) ?> ➔
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($sec['image_url'])): ?>
                    <div class="about-img-box">
                        <img src="<?= e($sec['image_url']) ?>" 
                             class="about-img" 
                             alt="<?= e($sec['title']) ?>" 
                             loading="lazy" 
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=800&q=80';">
                    </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($sec['section_type'] === 'text_image_left'): ?>
                <!-- Layout: Image on Left, Text on Right -->
                <div class="about-grid" style="display:flex; flex-direction:row-reverse; flex-wrap:wrap;">
                    <div class="about-content" style="flex:1; min-width:300px;">
                        <?php if (!empty($sec['subtitle'])): ?>
                            <span class="process-subtitle"><?= e($sec['subtitle']) ?></span>
                        <?php endif; ?>
                        <h2 style="color:<?= $isAccent ? '#fff' : 'var(--n)' ?>; font-size:32px; margin-top:8px; margin-bottom:20px; font-weight:700; line-height:1.3;">
                            <?= e($sec['title']) ?>
                        </h2>
                        <div style="font-size:16px; color:<?= $isAccent ? 'rgba(255,255,255,0.9)' : 'var(--g)' ?>; line-height:1.8; margin-bottom:25px;">
                            <?= format_markdown_content($sec['content']) ?>
                        </div>
                        <?php if (!empty($sec['btn_text'])): ?>
                            <a class="btn" href="<?= e($sec['btn_url'] ?: 'contact.php') ?>" style="margin-top:10px; display:inline-block;">
                                <?= e($sec['btn_text']) ?> ➔
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($sec['image_url'])): ?>
                    <div class="about-img-box" style="flex:1; min-width:300px;">
                        <img src="<?= e($sec['image_url']) ?>" 
                             class="about-img" 
                             alt="<?= e($sec['title']) ?>" 
                             loading="lazy" 
                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=800&q=80';">
                    </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($sec['section_type'] === 'cards_grid'): ?>
                <!-- Layout: Multi-Card Grid -->
                <div style="text-align:center; max-width:800px; margin:0 auto 40px;">
                    <?php if (!empty($sec['subtitle'])): ?>
                        <span class="process-subtitle" style="display:inline-block; margin-bottom:6px;"><?= e($sec['subtitle']) ?></span>
                    <?php endif; ?>
                    <h2 class="t" style="margin-bottom:15px; color:<?= $isAccent ? '#fff' : 'var(--n)' ?>;">
                        <?= e($sec['title']) ?>
                    </h2>
                    <?php if (!empty($sec['content'])): ?>
                        <div style="color:<?= $isAccent ? 'rgba(255,255,255,0.9)' : 'var(--g)' ?>; font-size:16px; line-height:1.8;">
                            <?= format_markdown_content($sec['content']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($sec['items'])): ?>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:25px;">
                    <?php foreach ($sec['items'] as $item): ?>
                    <div class="card" style="display:flex; flex-direction:column; justify-content:space-between; text-align:center; padding:30px 24px; transition:transform 0.3s ease, box-shadow 0.3s ease; border-radius:14px; background:<?= $isAccent ? '#0f172a' : '#ffffff' ?>; border:1px solid <?= $isAccent ? '#334155' : '#e2e8f0' ?>;">
                        <div>
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?= e($item['image']) ?>" 
                                     alt="<?= e($item['title'] ?? 'Card') ?>" 
                                     loading="lazy" 
                                     style="width:100%; height:180px; object-fit:cover; border-radius:10px; margin-bottom:18px;" 
                                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=400&q=80';">
                            <?php endif; ?>
                            <h3 style="font-size:20px; font-weight:700; color:<?= $isAccent ? '#fff' : 'var(--n)' ?>; margin:0 0 12px; line-height:1.3;">
                                <?= e($item['title'] ?? '') ?>
                            </h3>
                            <p style="color:<?= $isAccent ? 'rgba(255,255,255,0.85)' : 'var(--g)' ?>; font-size:15px; line-height:1.7; margin:0;">
                                <?= e($item['desc'] ?? '') ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($sec['btn_text'])): ?>
                    <div style="text-align:center; margin-top:35px;">
                        <a class="btn" href="<?= e($sec['btn_url'] ?: 'contact.php') ?>">
                            <?= e($sec['btn_text']) ?> ➔
                        </a>
                    </div>
                <?php endif; ?>

            <?php elseif ($sec['section_type'] === 'full_width'): ?>
                <!-- Layout: Centered Full-Width Narrative -->
                <div style="max-width:860px; margin:0 auto; text-align:center;">
                    <?php if (!empty($sec['subtitle'])): ?>
                        <span class="process-subtitle" style="display:inline-block; margin-bottom:6px;"><?= e($sec['subtitle']) ?></span>
                    <?php endif; ?>
                    <h2 style="color:<?= $isAccent ? '#fff' : 'var(--n)' ?>; font-size:32px; font-weight:800; margin:0 0 20px; line-height:1.3;">
                        <?= e($sec['title']) ?>
                    </h2>
                    <div style="font-size:17px; color:<?= $isAccent ? 'rgba(255,255,255,0.9)' : 'var(--g)' ?>; line-height:1.8; text-align:left; margin-bottom:30px;">
                        <?= format_markdown_content($sec['content']) ?>
                    </div>
                    <?php if (!empty($sec['image_url'])): ?>
                        <div style="margin-bottom:30px;">
                            <img src="<?= e($sec['image_url']) ?>" alt="<?= e($sec['title']) ?>" loading="lazy" style="max-width:100%; height:auto; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.1);">
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($sec['btn_text'])): ?>
                        <a class="btn" href="<?= e($sec['btn_url'] ?: 'contact.php') ?>" style="padding:13px 30px; font-size:16px;">
                            <?= e($sec['btn_text']) ?> ➔
                        </a>
                    <?php endif; ?>
                </div>

            <?php elseif ($sec['section_type'] === 'cta_banner'): ?>
                <!-- Layout: Call to Action Banner -->
                <div style="text-align:center; max-width:800px; margin:0 auto; padding:20px 15px;">
                    <?php if (!empty($sec['subtitle'])): ?>
                        <span style="display:inline-block; font-size:13px; font-weight:700; letter-spacing:1.5px; color:var(--o); text-transform:uppercase; margin-bottom:12px;">
                            <?= e($sec['subtitle']) ?>
                        </span>
                    <?php endif; ?>
                    <h2 style="color:<?= $isAccent ? '#ffffff' : 'var(--n)' ?>; font-size:34px; font-weight:800; margin:0 0 16px; line-height:1.3;">
                        <?= e($sec['title']) ?>
                    </h2>
                    <?php if (!empty($sec['content'])): ?>
                        <div style="font-size:17px; color:<?= $isAccent ? 'rgba(255,255,255,0.9)' : 'var(--g)' ?>; line-height:1.8; margin-bottom:30px;">
                            <?= format_markdown_content($sec['content']) ?>
                        </div>
                    <?php endif; ?>
                    <a class="btn" href="<?= e($sec['btn_url'] ?: 'contact.php') ?>" style="padding:14px 34px; font-size:16px; font-weight:700;">
                        <?= e($sec['btn_text'] ?: 'Get in Touch ➔') ?>
                    </a>
                </div>

            <?php endif; ?>

        </div>
    </section>
    <?php endforeach; ?>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
