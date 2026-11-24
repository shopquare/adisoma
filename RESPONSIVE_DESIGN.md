# ADISOMA Solution — Mobile-First Responsive Design System

## 1. Design Strategy & Viewport Standards

ADISOMA Solution is constructed with a mobile-first, fluid layout architecture. The application maintains visual consistency, brand integrity, and usability across device viewports ranging from small smartphones (320px) to ultra-wide desktop monitors (1920px+).

---

## 2. Breakpoint Specifications

| Viewport Category | Screen Width Range | Layout Behavior & Grid Adjustments |
|---|---|---|
| **Mobile Small / Compact** | 320px – 480px | Single-column stacked cards, full-width buttons, hamburger drawer navigation, horizontal scroll prevention. |
| **Mobile Large / Phablet** | 481px – 600px | Single-column fluid layouts, enhanced margin cushioning, fluid typography scaling. |
| **Tablet Portrait** | 601px – 768px | 2-column service/blog cards, compact header layout, collapsible sidebars. |
| **Tablet Landscape / Small Laptop** | 769px – 991px | 2-column or 3-column grids, inline navigation or mobile drawer threshold. |
| **Desktop Standard** | 992px – 1366px | Full 3-column card layouts, sticky desktop navigation header, expanded tables. |
| **Large Displays & 4K** | 1367px – 1920px+ | Max-width container constraint (`1200px`) centered with auto margins, high-density asset rendering. |

---

## 3. Mobile Navigation Architecture

### 3.1 Hamburger & Off-Canvas Drawer
On screen widths under `992px`, the desktop navigation bar converts into a slide-over mobile drawer:
- **Toggle Button**: Hamburger icon with `aria-label="Toggle Navigation"` and `aria-expanded="false"`.
- **Drawer Container**: Fixed-position off-canvas element sliding in smoothly from the left or right (`transform: translateX()`).
- **Backdrop Overlay**: Darkened translucent backdrop overlay (`rgba(0,0,0,0.5)`); clicking the backdrop or pressing `Escape` dismisses the drawer.
- **Body Scroll Lock**: When the drawer is open, `overflow: hidden` is applied to `<body>` to prevent background content scrolling.

### 3.2 Mobile Admin Drawer
The administration portal (`/admin/`) features a matching dedicated mobile slide-out drawer, allowing full control over job requisitions, candidate resumes, blogs, and settings directly from mobile devices.

---

## 4. Touch Targets & Accessibility Compliance

1. **Touch Target Dimensions**: All interactive elements (navigation links, filter chips, buttons, pagination links) enforce a minimum tap target dimension of **44x44px** (meeting WCAG 2.1 Success Criterion 2.5.5).
2. **Accessible Skip Navigation**: A dedicated `.skip-link` allows keyboard and screen reader users to bypass header navigation:
   ```html
   <a href="#main-content" class="skip-link">Skip to main content</a>
   ```
   Style:
   ```css
   .skip-link {
       position: absolute;
       top: -100px;
       left: 16px;
       background: var(--o);
       color: #fff;
       padding: 10px 18px;
       border-radius: 6px;
       font-weight: 600;
       z-index: 99999;
       transition: top 0.2s ease;
   }
   .skip-link:focus {
       top: 16px;
   }
   ```
3. **Form Input Ergonomics**: Mobile form fields (`input`, `select`, `textarea`) enforce a minimum font size of `16px` to prevent automatic zoom on iOS Safari.

---

## 5. Responsive Data Tables & Grids

- **Admin Tables**: Wrapped in `.table-responsive` containers with smooth horizontal momentum scrolling (`-webkit-overflow-scrolling: touch`), preventing layout overflow on narrow mobile screens.
- **Card Grids**: `.grid3` classes transition effortlessly from `grid-template-columns: repeat(3, 1fr)` on desktop to `repeat(2, 1fr)` on tablets, and `1fr` on mobile devices.
