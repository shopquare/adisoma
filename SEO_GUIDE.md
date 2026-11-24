# ADISOMA Solution — Technical SEO & Search Optimization Guide

## 1. Technical SEO Architecture Overview

ADISOMA Solution is engineered with a modern technical SEO foundation designed to maximize search engine discoverability, click-through rates, and rich result eligibility across Google, Bing, and major search crawlers.

---

## 2. On-Page SEO Architecture

### 2.1 Dynamic Metadata Injection
Every page defines contextual `$title` and `$meta_description` variables prior to importing `includes/header.php`.

```php
// Example on services.php:
$title = 'Our Services';
$meta_description = 'Explore ADISOMA Solution recruitment services: Permanent Hiring, Contract Staffing, IT Recruitment, Executive Search, and Talent Consulting.';
include 'includes/header.php';
```

### 2.2 Dynamic Canonical Tag
To eliminate duplicate content issues (such as parameter variations `?ref=social` or session variables), `includes/header.php` dynamically computes the canonical URL:

```php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$current_url = $protocol . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
```
Output:
```html
<link rel="canonical" href="https://adisoma.infy.click/services.php">
```

### 2.3 Social Graph Metadata (Open Graph & Twitter Cards)
All public pages emit complete Open Graph and Twitter Card tags:
```html
<meta property="og:type" content="website">
<meta property="og:site_name" content="ADISOMA Solution">
<meta property="og:title" content="Our Services | ADISOMA Solution">
<meta property="og:description" content="Explore ADISOMA Solution recruitment services...">
<meta property="og:url" content="https://adisoma.infy.click/services.php">
<meta property="og:image" content="https://adisoma.infy.click/assets/images/adisoma-logo.png">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Our Services | ADISOMA Solution">
<meta name="twitter:description" content="Explore ADISOMA Solution recruitment services...">
<meta name="twitter:image" content="https://adisoma.infy.click/assets/images/adisoma-logo.png">
```

---

## 3. Schema.org JSON-LD Structured Data

Search engines parse the structured data rendered in `includes/header.php` to construct Google Knowledge Graph entities and rich snippets:

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "@id": "https://adisoma.infy.click/#organization",
      "name": "ADISOMA Solution",
      "url": "https://adisoma.infy.click",
      "logo": "https://adisoma.infy.click/assets/images/adisoma-logo.png",
      "description": "Your premier recruitment partner for IT and Non-IT staffing solutions.",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+91 99999 99999",
        "contactType": "customer service"
      }
    },
    {
      "@type": "WebSite",
      "@id": "https://adisoma.infy.click/#website",
      "url": "https://adisoma.infy.click",
      "name": "ADISOMA Solution",
      "publisher": {
        "@id": "https://adisoma.infy.click/#organization"
      }
    }
  ]
}
</script>
```

---

## 4. Robots Directives (`robots.txt`)

The `robots.txt` file permits crawling of all public routes while protecting admin endpoints, resume uploads, and internal configuration:

```
User-agent: *
Disallow: /admin/
Disallow: /uploads/resumes/
Disallow: /config/
Disallow: /database/

Sitemap: https://adisoma.infy.click/sitemap.xml
```

---

## 5. XML Sitemap (`sitemap.xml`)

Located at the domain root, `sitemap.xml` lists all primary static and dynamic pages with indexing priorities and change frequencies:
- `https://adisoma.infy.click/` (`priority: 1.0`, `daily`)
- `https://adisoma.infy.click/services.php` (`priority: 0.9`, `weekly`)
- `https://adisoma.infy.click/jobs.php` (`priority: 0.9`, `daily`)
- `https://adisoma.infy.click/blogs.php` (`priority: 0.9`, `weekly`)
- `https://adisoma.infy.click/about.php` (`priority: 0.8`, `monthly`)
- `https://adisoma.infy.click/employers.php` (`priority: 0.8`, `monthly`)
- `https://adisoma.infy.click/job-seekers.php` (`priority: 0.8`, `monthly`)
- `https://adisoma.infy.click/contact.php` (`priority: 0.8`, `monthly`)
- Individual dynamic service pages (`service-detail.php?slug=...`) and blog articles (`blog-detail.php?slug=...`).

---

## 6. Performance & Crawl Budget Optimization

- **Resource Preconnect**: Preconnect links for Google Fonts reduce DNS lookup and TLS handshakes:
  ```html
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  ```
- **Asset Compression**: Apache `mod_deflate` compresses text, HTML, CSS, and JS by up to 70%.
- **Browser Caching**: Apache `mod_expires` caches images, fonts, and stylesheets for 1 month to 1 year.
- **Native Lazy Loading**: Non-critical imagery includes `loading="lazy"` to minimize initial DOM payload and speed up First Contentful Paint (FCP).
