<?php
require_once 'includes/functions.php';

// Form submission handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        flash('Invalid session token. Please reload and retry.', 'err');
    } elseif (post('website') !== '') {
        // Honeypot spam filter
        flash('Thank you! Your message has been received.');
    } else {
        $err = save_inquiry('contact');
        if ($err === '') {
            flash('Thank you! Your message has been sent successfully. Our team will get back to you shortly.');
        } else {
            flash($err, 'err');
        }
    }
    redirect(basename($_SERVER['SCRIPT_NAME']));
}

$title = 'Contact Us';
$sub = 'We would love to hear from you';
include 'includes/header.php';
?>

<!-- Scoped Styling for Contact Page Only -->
<style>
.cnt-page-wrap {
    background: #f8fafc;
    padding: 70px 0 90px;
}
.cnt-grid {
    display: grid;
    grid-template-columns: 1fr 1.15fr;
    gap: 45px;
    align-items: start;
}
.cnt-badge {
    display: inline-block;
    color: var(--o);
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
    background: rgba(245, 124, 0, 0.08);
    padding: 4px 12px;
    border-radius: 20px;
    border: 1px solid rgba(245, 124, 0, 0.2);
}
.cnt-heading {
    font-size: 34px;
    color: var(--n);
    font-weight: 800;
    line-height: 1.25;
    margin: 0 0 16px;
}
.cnt-intro {
    font-size: 16px;
    color: #64748b;
    line-height: 1.8;
    margin: 0 0 35px;
}
.cnt-cards-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 35px;
}
.cnt-info-card {
    display: flex;
    align-items: flex-start;
    gap: 18px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 18px 22px;
    transition: all 0.25s ease;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
}
.cnt-info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.07);
    border-color: #cbd5e1;
}
.cnt-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
}
.cnt-icon-orange { background: #fff7ed; color: var(--o); border: 1px solid #fed7aa; }
.cnt-icon-blue   { background: #eff6ff; color: #0284c7; border: 1px solid #bfdbfe; }
.cnt-icon-green  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.cnt-icon-slate  { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

.cnt-card-content {
    flex: 1;
}
.cnt-card-label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.cnt-card-val {
    display: block;
    font-size: 16px;
    font-weight: 700;
    color: var(--n);
    line-height: 1.4;
    text-decoration: none;
    transition: color 0.2s;
}
a.cnt-card-val:hover {
    color: var(--o);
}
.cnt-card-sub {
    display: block;
    font-size: 13px;
    color: #94a3b8;
    margin-top: 3px;
}

/* Social links box */
.cnt-social-section {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 18px 22px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
}
.cnt-social-title {
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 12px;
}
.cnt-social-links {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.cnt-social-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: var(--n);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}
.cnt-social-pill:hover {
    background: var(--o);
    color: #ffffff;
    border-color: var(--o);
    transform: translateY(-2px);
}

/* Form Card Container */
.cnt-form-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 42px 38px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 20px 45px rgba(15, 23, 42, 0.06);
    position: relative;
}
.cnt-form-header {
    margin-bottom: 28px;
}
.cnt-form-title {
    font-size: 26px;
    font-weight: 800;
    color: var(--n);
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.cnt-form-sub {
    font-size: 14px;
    color: #64748b;
    margin: 0;
    line-height: 1.6;
}

/* Form Controls */
.cnt-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 18px;
}
.cnt-field {
    margin-bottom: 18px;
}
.cnt-label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 6px;
}
.cnt-input, .cnt-select, .cnt-textarea {
    width: 100%;
    padding: 12px 16px;
    font-size: 14px;
    font-family: inherit;
    color: #0f172a;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    transition: all 0.2s ease;
    box-sizing: border-box;
}
.cnt-input:focus, .cnt-select:focus, .cnt-textarea:focus {
    background: #ffffff;
    border-color: var(--o);
    outline: none;
    box-shadow: 0 0 0 3px rgba(245, 124, 0, 0.15);
}
.cnt-textarea {
    resize: vertical;
    min-height: 120px;
    line-height: 1.6;
}
.cnt-submit-btn {
    width: 100%;
    background: linear-gradient(135deg, var(--o) 0%, #e65100 100%);
    color: #ffffff !important;
    border: none;
    padding: 15px 25px;
    font-size: 16px;
    font-weight: 700;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(245, 124, 0, 0.3);
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 10px;
}
.cnt-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(245, 124, 0, 0.4);
    background: linear-gradient(135deg, #fb8c00 0%, #ef6c00 100%);
}
.cnt-submit-btn:active {
    transform: translateY(0);
}
.cnt-privacy-note {
    text-align: center;
    font-size: 12px;
    color: #94a3b8;
    margin-top: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

/* Pathways banner section */
.cnt-pathways-wrap {
    padding: 60px 0 30px;
    border-top: 1px solid #e2e8f0;
    background: #ffffff;
}
.cnt-pathways-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
}
.cnt-pathway-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 30px 25px;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.3s ease;
}
.cnt-pathway-card:hover {
    background: #ffffff;
    transform: translateY(-4px);
    border-color: #cbd5e1;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
}
.cnt-pathway-icon {
    font-size: 34px;
    margin-bottom: 14px;
    display: inline-block;
}
.cnt-pathway-title {
    font-size: 19px;
    font-weight: 700;
    color: var(--n);
    margin: 0 0 10px;
}
.cnt-pathway-desc {
    font-size: 14px;
    color: #64748b;
    line-height: 1.7;
    margin: 0 0 20px;
    flex: 1;
}
.cnt-pathway-btn {
    display: inline-block;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s;
    background: #ffffff;
    color: var(--n);
    border: 1px solid #cbd5e1;
}
.cnt-pathway-btn:hover {
    background: var(--n);
    color: #ffffff;
    border-color: var(--n);
}
.cnt-pathway-card.highlight {
    background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);
    border-color: #fed7aa;
}
.cnt-pathway-card.highlight .cnt-pathway-btn {
    background: var(--o);
    color: #ffffff;
    border-color: var(--o);
}
.cnt-pathway-card.highlight .cnt-pathway-btn:hover {
    background: #e65100;
    border-color: #e65100;
}

/* Mobile Responsiveness */
@media (max-width: 991px) {
    .cnt-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    .cnt-form-card {
        padding: 32px 24px;
    }
}
@media (max-width: 580px) {
    .cnt-heading {
        font-size: 28px;
    }
    .cnt-form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
    .cnt-info-card {
        padding: 15px 16px;
    }
}
</style>

<section class="cnt-page-wrap">
    <div class="wrap">
        <?= flash() ?>

        <div class="cnt-grid">
            <!-- Left Column: Rich Contact Information & Social Profiles -->
            <div class="cnt-info-col">
                <span class="cnt-badge">📍 Let's Connect</span>
                <h1 class="cnt-heading">We’re Here to Elevate Your Hiring & Career Growth</h1>
                <p class="cnt-intro">
                    Have a critical hiring requirement, seeking specialized IT or Non-IT talent, or exploring your next transformative career move? Reach out through any of our channels or submit the inquiry form.
                </p>

                <div class="cnt-cards-list">
                    <!-- Office Address Card -->
                    <div class="cnt-info-card">
                        <div class="cnt-icon-box cnt-icon-orange">📍</div>
                        <div class="cnt-card-content">
                            <span class="cnt-card-label">Corporate Office</span>
                            <span class="cnt-card-val"><?= e(CO['address']) ?></span>
                            <span class="cnt-card-sub">Open for scheduled meetings & appointments</span>
                        </div>
                    </div>

                    <!-- Phone Number Card -->
                    <div class="cnt-info-card">
                        <div class="cnt-icon-box cnt-icon-blue">📞</div>
                        <div class="cnt-card-content">
                            <span class="cnt-card-label">Direct Phone Line</span>
                            <a href="tel:<?= e(CO['phone']) ?>" class="cnt-card-val"><?= e(CO['phone']) ?></a>
                            <span class="cnt-card-sub">Mon - Sat: 9:00 AM – 6:00 PM IST</span>
                        </div>
                    </div>

                    <!-- WhatsApp Quick Chat (if available) -->
                    <?php if (!empty(CO['whatsapp'])): ?>
                    <div class="cnt-info-card" style="border-color:#bbf7d0; background:#fafffd;">
                        <div class="cnt-icon-box cnt-icon-green">💬</div>
                        <div class="cnt-card-content">
                            <span class="cnt-card-label" style="color:#15803d;">WhatsApp Quick Response</span>
                            <a href="https://wa.me/<?= preg_replace('/[^\d]/', '', CO['whatsapp']) ?>" target="_blank" rel="noopener noreferrer" class="cnt-card-val" style="color:#15803d;">
                                Chat on WhatsApp ➔
                            </a>
                            <span class="cnt-card-sub" style="color:#16a34a;">Fastest channel for active staffing requests</span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Email Address Card -->
                    <div class="cnt-info-card">
                        <div class="cnt-icon-box cnt-icon-slate">✉️</div>
                        <div class="cnt-card-content">
                            <span class="cnt-card-label">Email Inquiries</span>
                            <a href="mailto:<?= e(CO['email']) ?>" class="cnt-card-val"><?= e(CO['email']) ?></a>
                            <span class="cnt-card-sub">Proposals, candidate profiles & partnerships</span>
                        </div>
                    </div>

                    <!-- Business Hours Card -->
                    <?php if (!empty(CO['hours'])): ?>
                    <div class="cnt-info-card">
                        <div class="cnt-icon-box cnt-icon-slate">🕒</div>
                        <div class="cnt-card-content">
                            <span class="cnt-card-label">Operating Hours</span>
                            <span class="cnt-card-val"><?= e(CO['hours']) ?></span>
                            <span class="cnt-card-sub">Sunday: Closed (Emergency inquiries monitored)</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Social Presence Section -->
                <div class="cnt-social-section">
                    <div class="cnt-social-title">Follow & Connect On Social Media</div>
                    <div class="cnt-social-links">
                        <?php if (!empty(CO['linkedin'])): ?>
                            <a href="<?= e(CO['linkedin']) ?>" target="_blank" rel="noopener noreferrer" class="cnt-social-pill">
                                <span>🔗</span> LinkedIn
                            </a>
                        <?php endif; ?>
                        <?php if (!empty(CO['facebook'])): ?>
                            <a href="<?= e(CO['facebook']) ?>" target="_blank" rel="noopener noreferrer" class="cnt-social-pill">
                                <span>🌐</span> Facebook
                            </a>
                        <?php endif; ?>
                        <?php if (!empty(CO['twitter'])): ?>
                            <a href="<?= e(CO['twitter']) ?>" target="_blank" rel="noopener noreferrer" class="cnt-social-pill">
                                <span>🐦</span> Twitter
                            </a>
                        <?php endif; ?>
                        <?php if (!empty(CO['instagram'])): ?>
                            <a href="<?= e(CO['instagram']) ?>" target="_blank" rel="noopener noreferrer" class="cnt-social-pill">
                                <span>📸</span> Instagram
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sleek Elevated Contact Form -->
            <div class="cnt-form-col">
                <div class="cnt-form-card">
                    <div class="cnt-form-header">
                        <h2 class="cnt-form-title">
                            <span>✉️</span> Send Us a Message
                        </h2>
                        <p class="cnt-form-sub">
                            Fill in your details and requirements. Our recruitment consultants will get back to you within 24 business hours.
                        </p>
                    </div>

                    <form method="post" data-validate>
                        <?= csrf() ?>
                        <!-- Honeypot field for bot protection -->
                        <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute; left:-9999px; display:none !important;" aria-hidden="true">

                        <!-- Row 1: Name & Email -->
                        <div class="cnt-form-row">
                            <div class="cnt-field">
                                <label class="cnt-label">Full Name <span style="color:#ef4444;">*</span></label>
                                <input class="cnt-input" name="name" required maxlength="100" placeholder="e.g. Rahul Sharma" autocomplete="name">
                            </div>
                            <div class="cnt-field">
                                <label class="cnt-label">Email Address <span style="color:#ef4444;">*</span></label>
                                <input class="cnt-input" type="email" name="email" required placeholder="e.g. rahul@company.com" autocomplete="email">
                            </div>
                        </div>

                        <!-- Row 2: Phone & Company -->
                        <div class="cnt-form-row">
                            <div class="cnt-field">
                                <label class="cnt-label">Phone Number</label>
                                <input class="cnt-input" type="tel" name="phone" placeholder="e.g. +91 98765 43210" autocomplete="tel">
                            </div>
                            <div class="cnt-field">
                                <label class="cnt-label">Company / Organization</label>
                                <input class="cnt-input" name="company" placeholder="e.g. Acme Enterprises">
                            </div>
                        </div>

                        <!-- Row 3: Inquiry Subject Dropdown -->
                        <div class="cnt-field">
                            <label class="cnt-label">Inquiry Topic</label>
                            <select class="cnt-select" name="subject">
                                <option value="Hiring Talent / Employer Solutions">🏢 Employer Staffing & Talent Acquisition</option>
                                <option value="IT & Tech Recruitment">💻 IT, Cloud & Tech Staffing</option>
                                <option value="Contract & Flexible Staffing">⏱️ Contract Staffing Solutions</option>
                                <option value="Job Seeker / Career Application">💼 Job Seeker / Career Inquiry</option>
                                <option value="Partnership & Consulting">🤝 Consulting & Partnerships</option>
                                <option value="General Inquiry" selected>✉️ General Inquiry</option>
                            </select>
                        </div>

                        <!-- Row 4: Message -->
                        <div class="cnt-field">
                            <label class="cnt-label">Your Message / Requirements <span style="color:#ef4444;">*</span></label>
                            <textarea class="cnt-textarea" name="message" rows="5" required placeholder="Describe your hiring requirements, target roles, or any questions you would like us to answer..."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button class="cnt-submit-btn" type="submit">
                            <span>Submit Message</span>
                            <span>➔</span>
                        </button>

                        <div class="cnt-privacy-note">
                            <span>🔒</span>
                            <span>We respect your privacy. Your information is strictly confidential.</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Additional Pathways Section -->
<section class="cnt-pathways-wrap">
    <div class="wrap">
        <div style="text-align:center; max-width:680px; margin:0 auto 35px;">
            <span class="cnt-badge">QUICK PATHWAYS</span>
            <h2 style="font-size:26px; font-weight:800; color:var(--n); margin:6px 0 10px;">How Can We Assist You Faster?</h2>
            <p style="color:#64748b; font-size:15px; margin:0;">Choose the direct destination tailored specifically for your needs.</p>
        </div>

        <div class="cnt-pathways-grid">
            <!-- Pathway 1: For Employers -->
            <div class="cnt-pathway-card highlight">
                <div>
                    <div class="cnt-pathway-icon">🏢</div>
                    <h3 class="cnt-pathway-title">Hire Elite Talent</h3>
                    <p class="cnt-pathway-desc">
                        Need top developers, recruiters, or specialized executives? Submit your custom staffing requisition for immediate sourcing.
                    </p>
                </div>
                <div>
                    <a href="employers.php" class="cnt-pathway-btn">Submit Hiring Request ➔</a>
                </div>
            </div>

            <!-- Pathway 2: Job Openings -->
            <div class="cnt-pathway-card">
                <div>
                    <div class="cnt-pathway-icon">💼</div>
                    <h3 class="cnt-pathway-title">Browse Active Jobs</h3>
                    <p class="cnt-pathway-desc">
                        Explore verified career opportunities across IT, Non-IT, engineering, finance, and management disciplines.
                    </p>
                </div>
                <div>
                    <a href="jobs.php" class="cnt-pathway-btn">Explore Open Positions ➔</a>
                </div>
            </div>

            <!-- Pathway 3: Submit Resume -->
            <div class="cnt-pathway-card">
                <div>
                    <div class="cnt-pathway-icon">📄</div>
                    <h3 class="cnt-pathway-title">Submit Your Resume</h3>
                    <p class="cnt-pathway-desc">
                        Not seeing the right vacancy today? Upload your CV directly into our candidate talent pool for future matched openings.
                    </p>
                </div>
                <div>
                    <a href="apply.php" class="cnt-pathway-btn">Upload Resume ➔</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
