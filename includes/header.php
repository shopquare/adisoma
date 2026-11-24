<?php 
require_once __DIR__.'/functions.php'; 
$title = $title ?? 'Home'; 
$sub = $sub ?? ''; 
$cur = basename($_SERVER['SCRIPT_NAME']); 

$page_title = ($title === 'Home' || $title === '') ? e(CO['name']) . ' - ' . e(CO['tagline'] ?? 'Connect. Hire. Grow.') : e($title) . ' | ' . e(CO['name']);
$meta_desc = $meta_desc ?? 'ADISOMA Solution is your premier recruitment and workforce partner, connecting skilled talent with growing enterprises across IT and Non-IT sectors.';
$canonical_url = $canonical_url ?? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'adisoma.infy.click') . parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
$og_image = $og_image ?? (BASE_URL . '/assets/images/adisoma-logo.png');
$og_type = $og_type ?? 'website';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <meta name="description" content="<?= e($meta_desc) ?>">
    <link rel="canonical" href="<?= e($canonical_url) ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:site_name" content="<?= e(CO['name']) ?>">
    <meta property="og:type" content="<?= e($og_type) ?>">
    <meta property="og:url" content="<?= e($canonical_url) ?>">
    <meta property="og:title" content="<?= $page_title ?>">
    <meta property="og:description" content="<?= e($meta_desc) ?>">
    <meta property="og:image" content="<?= e($og_image) ?>">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $page_title ?>">
    <meta name="twitter:description" content="<?= e($meta_desc) ?>">
    <meta name="twitter:image" content="<?= e($og_image) ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/adisoma-logo.png">

    <!-- Preconnect to Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Stylesheets with Dynamic Versioning -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= asset_v() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css?v=<?= asset_v() ?>">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "@id": "https://adisoma.infy.click/#organization",
          "name": "<?= e(CO['name']) ?>",
          "url": "https://adisoma.infy.click/",
          "logo": "https://adisoma.infy.click/assets/images/adisoma-logo.png",
          "description": "<?= e(CO['tagline']) ?>",
          "address": {
            "@type": "PostalAddress",
            "streetAddress": "<?= e(CO['address']) ?>",
            "addressLocality": "Mumbai",
            "addressRegion": "Maharashtra",
            "addressCountry": "IN"
          },
          "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "<?= e(CO['phone']) ?>",
            "contactType": "Customer Support",
            "email": "<?= e(CO['email']) ?>"
          }
        },
        {
          "@type": "WebSite",
          "@id": "https://adisoma.infy.click/#website",
          "url": "https://adisoma.infy.click/",
          "name": "<?= e(CO['name']) ?>",
          "publisher": { "@id": "https://adisoma.infy.click/#organization" }
        }
      ]
    }
    </script>
</head>
<body>
<a href="#main-content" class="skip-link">Skip to main content</a>
<?php include __DIR__.'/navbar.php'; if ($sub !== ''): ?>
<section class="page-hero"><div class="wrap"><h1><?= e($title) ?></h1><p><?= e($sub) ?></p></div></section>
<?php endif; ?>
<main id="main-content">
