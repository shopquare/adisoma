<?php
require_once __DIR__ . '/../includes/functions.php';
admin_only();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) die('Bad CSRF token');
    $action = post('action');

    if ($action === 'save') {
        $fields = [
            // Company
            'name' => ['val' => trim(post('name')), 'cat' => 'company'],
            'tagline' => ['val' => trim(post('tagline')), 'cat' => 'company'],
            // Contact
            'phone' => ['val' => trim(post('phone')), 'cat' => 'contact'],
            'whatsapp' => ['val' => trim(post('whatsapp')), 'cat' => 'contact'],
            'email' => ['val' => trim(post('email')), 'cat' => 'contact'],
            'address' => ['val' => trim(post('address')), 'cat' => 'contact'],
            'hours' => ['val' => trim(post('hours')), 'cat' => 'contact'],
            'maps_url' => ['val' => trim(post('maps_url')), 'cat' => 'contact'],
            // Social Media
            'facebook' => ['val' => trim(post('facebook')), 'cat' => 'social'],
            'linkedin' => ['val' => trim(post('linkedin')), 'cat' => 'social'],
            'twitter' => ['val' => trim(post('twitter')), 'cat' => 'social'],
            'instagram' => ['val' => trim(post('instagram')), 'cat' => 'social'],
            'youtube' => ['val' => trim(post('youtube')), 'cat' => 'social'],
        ];

        $stmt = db()->prepare("
            INSERT INTO site_settings (setting_key, setting_value, category)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), category = VALUES(category)
        ");

        db()->beginTransaction();
        foreach ($fields as $key => $data) {
            $stmt->execute([$key, $data['val'], $data['cat']]);
        }
        db()->commit();

        clear_all_cache();
        log_action('Updated', 'Settings', 0, 'Updated company contact details and social media URLs');
        flash('Settings updated successfully! Changes are now reflected live across your entire website.');
        redirect('manage-settings.php');
    }

    if ($action === 'reset_defaults') {
        db()->exec("DELETE FROM site_settings");
        ensure_site_settings_table();
        clear_all_cache();
        log_action('Reset', 'Settings', 0, 'Reset website settings to factory defaults');
        flash('Contact info and social media URLs have been reset to defaults.');
        redirect('manage-settings.php');
    }
}

include '_header.php';

$s = get_site_settings(true);
?>

<div class="dash-head">
    <div>
        <h1 style="margin:0 0 6px;">Contact Info & Social Media Manager</h1>
        <span style="color:#64748b; font-size:14px;">Manage contact details, phone numbers, office location, and social profiles reflected across the website</span>
    </div>
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <a href="../contact.php" target="_blank" class="btn sm" style="background:#0284c7; border-color:#0284c7; color:#fff !important; font-weight:600;">
            View Contact Page ➔
        </a>
        <a href="../index.php" target="_blank" class="btn sm alt" style="font-weight:600;">
            View Live Site ➔
        </a>
    </div>
</div>

<!-- Live Preview Box -->
<div style="background:#ffffff; border-radius:12px; border:1px solid #e2e8f0; padding:22px; margin-bottom:30px; box-shadow:0 4px 15px rgba(0,0,0,0.03);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
        <h3 style="margin:0; font-size:16px; color:#1e293b; display:flex; align-items:center; gap:8px;">
            <span>👁️</span> Live Website Footer Preview (How Visitors See Your Details)
        </h3>
        <span style="font-size:12px; color:#15803d; background:#dcfce7; padding:3px 10px; border-radius:12px; font-weight:700;">● Active Site-Wide</span>
    </div>

    <div style="background:#0f172a; color:#f8fafc; border-radius:10px; padding:24px 28px; display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:24px; box-shadow:0 4px 20px rgba(15,23,42,0.15);">
        <div>
            <div style="font-weight:800; font-size:18px; color:#fff; margin-bottom:6px;"><?= e($s['name']) ?></div>
            <p style="color:#94a3b8; font-size:13px; line-height:1.6; margin:0 0 14px;"><?= e($s['tagline']) ?></p>
            <div style="font-size:12px; color:#64748b;">Working Hours: <b style="color:#cbd5e1;"><?= e($s['hours']) ?></b></div>
        </div>

        <div>
            <div style="font-weight:700; font-size:15px; color:#f57c00; margin-bottom:10px; text-transform:uppercase; letter-spacing:0.5px;">Official Contact</div>
            <p style="margin:0 0 6px; font-size:14px; color:#cbd5e1; display:flex; align-items:center; gap:8px;">
                <span>📍</span> <?= e($s['address']) ?>
            </p>
            <p style="margin:0 0 6px; font-size:14px; color:#cbd5e1; display:flex; align-items:center; gap:8px;">
                <span>📞</span> <a href="tel:<?= e($s['phone']) ?>" style="color:#38bdf8; text-decoration:none;"><?= e($s['phone']) ?></a>
            </p>
            <?php if (!empty($s['whatsapp'])): ?>
            <p style="margin:0 0 6px; font-size:14px; color:#cbd5e1; display:flex; align-items:center; gap:8px;">
                <span>💬</span> <span style="color:#4ade80;">WhatsApp:</span> <?= e($s['whatsapp']) ?>
            </p>
            <?php endif; ?>
            <p style="margin:0; font-size:14px; color:#cbd5e1; display:flex; align-items:center; gap:8px;">
                <span>✉️</span> <a href="mailto:<?= e($s['email']) ?>" style="color:#38bdf8; text-decoration:none;"><?= e($s['email']) ?></a>
            </p>
        </div>

        <div>
            <div style="font-weight:700; font-size:15px; color:#f57c00; margin-bottom:10px; text-transform:uppercase; letter-spacing:0.5px;">Social Media Links</div>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                <?php
                $socials = [
                    'facebook' => ['label' => 'Facebook', 'color' => '#1877f2'],
                    'linkedin' => ['label' => 'LinkedIn', 'color' => '#0a66c2'],
                    'twitter' => ['label' => 'Twitter/X', 'color' => '#000000'],
                    'instagram' => ['label' => 'Instagram', 'color' => '#e4405f'],
                    'youtube' => ['label' => 'YouTube', 'color' => '#ff0000'],
                ];
                foreach ($socials as $k => $info):
                    $url = $s[$k] ?? '';
                    if (!empty($url) && $url !== '#'): ?>
                        <a href="<?= e($url) ?>" target="_blank" style="text-decoration:none; font-size:12px; font-weight:700; padding:4px 10px; border-radius:6px; background:#1e293b; color:#fff; border:1px solid #334155; display:inline-flex; align-items:center; gap:4px;">
                            <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:<?= $info['color'] ?>;"></span>
                            <?= $info['label'] ?> ↗
                        </a>
                    <?php endif;
                endforeach; ?>
            </div>
        </div>
    </div>
</div>

<form method="post" class="at" style="background:#ffffff; border-radius:12px; border:1px solid #e2e8f0; padding:28px; box-shadow:0 4px 15px rgba(0,0,0,0.03); margin-bottom:30px;">
    <?= csrf() ?>
    <input type="hidden" name="action" value="save">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #e2e8f0; padding-bottom:14px; flex-wrap:wrap; gap:10px;">
        <div>
            <h2 style="margin:0 0 4px; font-size:20px; color:var(--n);">General Information & Contact Details</h2>
            <span style="color:#64748b; font-size:13px;">This information automatically updates the header, footer, contact page, and service enquiry cards</span>
        </div>
        <button type="submit" class="btn" style="padding:9px 24px; font-weight:700; display:inline-flex; align-items:center; gap:6px;">
            <span>💾</span> Save All Changes
        </button>
    </div>

    <!-- Section 1: Company Profile -->
    <div style="margin-bottom:28px;">
        <h3 style="font-size:16px; color:#1e293b; margin:0 0 16px; display:flex; align-items:center; gap:8px;">
            <span style="color:var(--o);">🏢</span> Company Identity
        </h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:18px;">
            <div>
                <label style="font-weight:700; font-size:14px; margin-bottom:6px; display:block;">Company Display Name</label>
                <input name="name" required value="<?= e($s['name']) ?>" placeholder="e.g. ADISOMA Solution" style="width:100%;">
            </div>
            <div>
                <label style="font-weight:700; font-size:14px; margin-bottom:6px; display:block;">Company Tagline / Slogan</label>
                <input name="tagline" value="<?= e($s['tagline']) ?>" placeholder="e.g. Your Talent Partner for a Brighter Tomorrow." style="width:100%;">
            </div>
        </div>
    </div>

    <!-- Section 2: Contact Numbers & Email -->
    <div style="margin-bottom:28px; border-top:1px dashed #e2e8f0; padding-top:22px;">
        <h3 style="font-size:16px; color:#1e293b; margin:0 0 16px; display:flex; align-items:center; gap:8px;">
            <span style="color:var(--o);">📞</span> Contact Numbers & Email
        </h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:18px;">
            <div>
                <label style="font-weight:700; font-size:14px; margin-bottom:6px; display:block;">Primary Phone Number *</label>
                <input name="phone" required value="<?= e($s['phone']) ?>" placeholder="e.g. +91 98765 43210" style="width:100%;">
                <small style="color:#64748b; font-size:12px; display:block; margin-top:4px;">Appears on footer, contact page, and header contact badges</small>
            </div>
            <div>
                <label style="font-weight:700; font-size:14px; margin-bottom:6px; display:block;">WhatsApp / Secondary Phone</label>
                <input name="whatsapp" value="<?= e($s['whatsapp']) ?>" placeholder="e.g. +91 98765 43210" style="width:100%;">
                <small style="color:#64748b; font-size:12px; display:block; margin-top:4px;">Direct mobile/WhatsApp number for rapid recruitment inquiries</small>
            </div>
            <div>
                <label style="font-weight:700; font-size:14px; margin-bottom:6px; display:block;">Primary Official Email *</label>
                <input type="email" name="email" required value="<?= e($s['email']) ?>" placeholder="e.g. info@adisomasolution.com" style="width:100%;">
                <small style="color:#64748b; font-size:12px; display:block; margin-top:4px;">Used for all client & candidate outreach and footer mailto link</small>
            </div>
            <div>
                <label style="font-weight:700; font-size:14px; margin-bottom:6px; display:block;">Working Hours / Days</label>
                <input name="hours" value="<?= e($s['hours']) ?>" placeholder="e.g. Mon - Sat: 9:00 AM - 6:00 PM" style="width:100%;">
                <small style="color:#64748b; font-size:12px; display:block; margin-top:4px;">Operating hours displayed on Contact page and Footer</small>
            </div>
        </div>
    </div>

    <!-- Section 3: Physical Address -->
    <div style="margin-bottom:28px; border-top:1px dashed #e2e8f0; padding-top:22px;">
        <h3 style="font-size:16px; color:#1e293b; margin:0 0 16px; display:flex; align-items:center; gap:8px;">
            <span style="color:var(--o);">📍</span> Office Address & Location
        </h3>
        <div style="display:grid; grid-template-columns:1fr; gap:18px;">
            <div>
                <label style="font-weight:700; font-size:14px; margin-bottom:6px; display:block;">Physical Office Address *</label>
                <textarea name="address" rows="3" required placeholder="e.g. Suite 402, Business Bay, Andheri East, Mumbai, Maharashtra 400069" style="width:100%;"><?= e($s['address']) ?></textarea>
                <small style="color:#64748b; font-size:12px; display:block; margin-top:4px;">Full office location shown across footer and contact details</small>
            </div>
            <div>
                <label style="font-weight:700; font-size:14px; margin-bottom:6px; display:block;">Google Maps Link (Optional)</label>
                <input name="maps_url" value="<?= e($s['maps_url']) ?>" placeholder="https://maps.google.com/?q=..." style="width:100%;">
                <small style="color:#64748b; font-size:12px; display:block; margin-top:4px;">Link to your business location on Google Maps</small>
            </div>
        </div>
    </div>

    <!-- Section 4: Social Media Links -->
    <div style="margin-bottom:28px; border-top:1px dashed #e2e8f0; padding-top:22px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:8px;">
            <h3 style="font-size:16px; color:#1e293b; margin:0; display:flex; align-items:center; gap:8px;">
                <span style="color:var(--o);">🌐</span> Social Media Profile URLs
            </h3>
            <span style="color:#64748b; font-size:12px;">Leave blank or enter '#' if not currently active</span>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:18px;">
            <div>
                <label style="font-weight:700; font-size:14px; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                    <span style="color:#0a66c2;">💼</span> LinkedIn Company Page URL
                </label>
                <input name="linkedin" value="<?= e($s['linkedin']) ?>" placeholder="https://www.linkedin.com/company/adisoma-solution" style="width:100%;">
            </div>

            <div>
                <label style="font-weight:700; font-size:14px; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                    <span style="color:#1877f2;">📘</span> Facebook Page URL
                </label>
                <input name="facebook" value="<?= e($s['facebook']) ?>" placeholder="https://www.facebook.com/adisomasolution" style="width:100%;">
            </div>

            <div>
                <label style="font-weight:700; font-size:14px; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                    <span style="color:#000;">✖️</span> Twitter / X Profile URL
                </label>
                <input name="twitter" value="<?= e($s['twitter']) ?>" placeholder="https://x.com/adisomasolution" style="width:100%;">
            </div>

            <div>
                <label style="font-weight:700; font-size:14px; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                    <span style="color:#e4405f;">📸</span> Instagram Profile URL
                </label>
                <input name="instagram" value="<?= e($s['instagram']) ?>" placeholder="https://www.instagram.com/adisomasolution" style="width:100%;">
            </div>

            <div>
                <label style="font-weight:700; font-size:14px; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                    <span style="color:#ff0000;">▶️</span> YouTube Channel URL (Optional)
                </label>
                <input name="youtube" value="<?= e($s['youtube']) ?>" placeholder="https://www.youtube.com/@adisomasolution" style="width:100%;">
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; padding-top:20px; border-top:1px solid #e2e8f0;">
        <button type="submit" class="btn" style="padding:12px 32px; font-weight:700; font-size:15px; display:inline-flex; align-items:center; gap:8px;">
            <span>💾</span> Save All Settings
        </button>
    </div>
</form>

<!-- Danger zone / Reset -->
<div style="background:#ffffff; border-radius:12px; border:1px solid #fecaca; padding:20px; box-shadow:0 4px 15px rgba(0,0,0,0.02); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
    <div>
        <h4 style="margin:0 0 4px; color:#b91c1c; font-size:15px;">Reset to Factory Defaults</h4>
        <span style="color:#64748b; font-size:13px;">Restore original company placeholders and default social media links.</span>
    </div>
    <form method="post" onsubmit="return confirm('Are you sure you want to restore factory default contact info & social media URLs?');" style="margin:0;">
        <?= csrf() ?>
        <input type="hidden" name="action" value="reset_defaults">
        <button type="submit" class="btn sm alt" style="color:#b91c1c; border-color:#fca5a5;">
            🔄 Reset to Defaults
        </button>
    </form>
</div>

<?php include '_footer.php'; ?>
