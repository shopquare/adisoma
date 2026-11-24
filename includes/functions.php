<?php
require_once __DIR__.'/../config/database.php';
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function csrf(){return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">';}
function csrf_ok(){return isset($_POST['csrf'])&&hash_equals($_SESSION['csrf'],$_POST['csrf']);}
function redirect($u){header("Location: $u");exit;}
function flash($m=null,$t='ok'){if($m){$_SESSION['f']=[$m,$t];return;}if(!empty($_SESSION['f'])){[$m,$t]=$_SESSION['f'];unset($_SESSION['f']);return "<div class='alert $t'>".e($m)."</div>";}return '';}
function admin_only(){if(empty($_SESSION['admin']))redirect('login.php');}
function post($k){return trim($_POST[$k]??'');}

function log_action($action, $type, $entity_id, $details = '') {
    $admin_id = $_SESSION['admin'] ?? null;
    if ($admin_id) {
        db()->prepare('INSERT INTO admin_logs (admin_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)')
          ->execute([$admin_id, $action, $type, $entity_id, $details]);
    }
}

function save_inquiry(string $type):string{
 if(!csrf_ok())return 'Invalid session. Reload and retry.';
 if(post('website')!=='')return ''; // honeypot
 $n=post('name');$em=post('email');$m=post('message');
 if($n===''||!filter_var($em,FILTER_VALIDATE_EMAIL)||$m==='')return 'Please provide a valid name, email and message.';
 db()->prepare('INSERT INTO inquiries(type,name,email,phone,company,subject,message) VALUES(?,?,?,?,?,?,?)')
  ->execute([$type,$n,$em,post('phone'),post('company'),post('subject'),$m]);
 return '';
}
function save_application(int $jobId):string{
 if(!csrf_ok())return 'Invalid session. Reload and retry.';
 $n=post('name');$em=post('email');
 if($n===''||!filter_var($em,FILTER_VALIDATE_EMAIL))return 'Please enter a valid name and email.';
 $f=$_FILES['resume']??null;
 if(!$f||$f['error']!==UPLOAD_ERR_OK)return 'Please upload your resume.';
 if($f['size']>2*1024*1024)return 'Resume must be under 2 MB.';
 $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION));
 if(!in_array($ext,['pdf','doc','docx']))return 'Resume must be PDF, DOC or DOCX.';
 $name=bin2hex(random_bytes(12)).'.'.$ext;
 if(!is_dir(__DIR__.'/../uploads/resumes/')) mkdir(__DIR__.'/../uploads/resumes/', 0777, true);
 if(!move_uploaded_file($f['tmp_name'],__DIR__.'/../uploads/resumes/'.$name))return 'Upload failed.';
 db()->prepare('INSERT INTO applications(job_id,name,email,phone,experience,message,resume) VALUES(?,?,?,?,?,?,?)')
  ->execute([$jobId?:null,$n,$em,post('phone'),post('experience'),post('message'),$name]);
 return '';
}

function make_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    if (function_exists('iconv')) {
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    }
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'item-' . time());
}

// Auto-create blogs table if missing in production database
function ensure_blogs_table() {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        db()->query("SELECT 1 FROM blogs LIMIT 1");
    } catch (Exception $e) {
        try {
            db()->exec("
                CREATE TABLE IF NOT EXISTS blogs(
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(200) NOT NULL,
                    slug VARCHAR(200) UNIQUE NOT NULL,
                    excerpt TEXT,
                    content MEDIUMTEXT NOT NULL,
                    image_url VARCHAR(255) DEFAULT '',
                    author VARCHAR(100) DEFAULT 'ADISOMA Team',
                    status ENUM('draft','published') NOT NULL DEFAULT 'published',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX(status),
                    INDEX(slug)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            
            $count = (int)db()->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
            if ($count === 0) {
                $stmt = db()->prepare("INSERT INTO blogs(title, slug, excerpt, content, image_url, author, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    'Top Hiring Trends in 2026: Navigating the Modern Talent Landscape',
                    'top-hiring-trends-in-2026',
                    'Discover the key recruitment dynamics reshaping how companies attract, hire, and retain high-performing talent across IT and non-IT sectors.',
                    "The recruitment ecosystem is evolving at an unprecedented speed. In 2026, organizations are shifting from transactional hiring to relationship-driven talent partnerships.\n\n### 1. Skills-First Recruitment\nModern enterprises are valuing demonstrable practical skills, problem-solving capability, and cultural adaptability over traditional pedigree.\n\n### 2. Flexible Workforce Models\nWith companies seeking agility, contract staffing and project-based talent solutions continue to surge.\n\n### 3. Accelerated Candidate Experience\nTop candidates have multiple options. A streamlined, transparent interview process is the single biggest differentiator between landing top talent and losing them to competitors.\n\nAt ADISOMA Solution, we help enterprises align their workforce strategy with market realities, ensuring sustainable organizational growth.",
                    'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=800&q=80',
                    'ADISOMA Team',
                    'published'
                ]);
                $stmt->execute([
                    'How to Build an Agile IT Engineering Team on Demand',
                    'how-to-build-an-agile-it-engineering-team',
                    'Learn strategic staffing approaches to assemble niche software, cloud, and DevOps engineering teams without lengthy hiring bottlenecks.',
                    "Scaling technical teams is one of the most critical challenges facing CTOs and engineering managers today. Niche skill sets in full-stack architecture, cloud engineering, and cybersecurity are in high demand.\n\n### Defining the Core vs. Specialized Roles\nWhen scaling up engineering teams, it is important to clearly delineate foundational full-time positions from specialized, temporary consulting needs.\n\n### Effective Pre-screening is Vital\nScreening developers goes beyond reviewing CV keywords. Evaluating code maintainability, system design familiarity, and communication skills ensures immediate productivity once onboarding starts.\n\nPartnering with a specialized IT recruitment partner like ADISOMA gives companies rapid access to thoroughly screened talent communities.",
                    'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80',
                    'Tech Recruitment Desk',
                    'published'
                ]);
            }
        } catch (Exception $ex) {
            // Silently continue if permissions issue
        }
    }
}
ensure_blogs_table();

function ensure_services_schema() {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $cols = db()->query("SHOW COLUMNS FROM services LIKE 'slug'")->fetchAll();
        if (empty($cols)) {
            db()->exec("ALTER TABLE services ADD COLUMN slug VARCHAR(120) NULL AFTER title");
            try { db()->exec("ALTER TABLE services ADD UNIQUE (slug)"); } catch (Exception $ex) {}
        }
        
        $cols = db()->query("SHOW COLUMNS FROM services LIKE 'content'")->fetchAll();
        if (empty($cols)) {
            db()->exec("ALTER TABLE services ADD COLUMN content MEDIUMTEXT NULL AFTER description");
        }

        db()->exec("UPDATE services SET image_url = 'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=800&q=80' WHERE title LIKE '%Contract Staffing%' AND (image_url LIKE '%1556761175%' OR image_url = '' OR image_url IS NULL)");

        $defaults = [
            'Permanent Recruitment' => [
                'slug' => 'permanent-recruitment',
                'image' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=800&q=80',
                'desc' => 'End-to-end sourcing, screening and placement of full-time talent tailored to your long-term organizational goals.',
                'content' => "### Comprehensive Permanent Hiring Solutions\n\nFinding the right full-time employees is crucial for the continuous growth, cultural synergy, and operational success of your business. At ADISOMA Solution, our permanent staffing practice delivers high-caliber candidates who not only possess verified technical and domain skills but also align with your organization's vision.\n\n### Our Strategic Methodology\n- **Deep Requirement Discovery**: We consult with your hiring managers to define core competencies, cultural fit, and performance expectations.\n- **Multi-Channel Talent Sourcing**: Leveraging proprietary talent databases, verified industry applicant pools, and targeted direct-headhunting.\n- **360-Degree Screening**: Rigorous technical assessments, structured behavioral interviews, and comprehensive reference checks.\n- **Seamless Onboarding & Follow-Up**: Ongoing support through compensation negotiation, offer release, and post-placement integration.\n\n### Key Benefits of Permanent Placement\n- Faster time-to-hire with industry-tested candidate shortlists\n- Dedicated industry recruitment desks (IT, BFSI, Healthcare, Engineering & Non-IT)\n- Replacement warranty for guaranteed peace of mind"
            ],
            'Contract Staffing' => [
                'slug' => 'contract-staffing',
                'image' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Flexible, highly skilled professionals for project-based, seasonal, and short-term workload requirements.',
                'content' => "### Agile & On-Demand Contract Workforce\n\nModern market demands fluctuate rapidly. Whether you are executing a mission-critical technology migration, covering seasonal workload spikes, or managing project deadlines, ADISOMA Solution provides agile contract staffing solutions that give your enterprise dynamic scaling capabilities without increasing permanent payroll liabilities.\n\n### Key Benefits of Contract Staffing\n- **Rapid Deployment**: Pre-vetted specialists ready to contribute from day one.\n- **Cost Efficiency**: Minimize long-term liabilities, benefits administration, and severance overheads.\n- **Specialized Expertise**: Access niche skill sets for specific project milestones without permanent commitments.\n- **Seamless Payroll & Compliance**: We handle statutory benefits, labor compliance, and payroll processing end-to-end.\n\n### Popular Roles Filled via Contract Staffing\n- Software Engineers, Cloud Architects & QA Testers\n- Project Managers & Agile Scrum Masters\n- Seasonal Customer Support & Operations Executives\n- Financial Analysts & Interim Leadership"
            ],
            'IT Recruitment' => [
                'slug' => 'it-recruitment',
                'image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Developers, testers, DevOps, data engineers, cloud architects, and cybersecurity specialists.',
                'content' => "### Connecting High-Growth Companies with Premier Tech Talent\n\nIn a fast-advancing technological landscape, having top-tier technical talent is the ultimate competitive advantage. ADISOMA Solution operates a dedicated IT Recruitment desk led by technical talent scouts who understand modern tech stacks, cloud architectures, and digital transformation.\n\n### Technical Domains We Specialize In\n- **Full-Stack & Software Engineering**: Java, Python, .NET, Node.js, React, Angular, Go, and Mobile (Flutter/React Native/iOS/Android).\n- **Cloud & DevOps**: AWS, Microsoft Azure, GCP, Kubernetes, Docker, CI/CD, and Infrastructure as Code.\n- **Data & Artificial Intelligence**: Data Engineering, Business Intelligence, Machine Learning, and Big Data.\n- **Cybersecurity & Network Engineering**: Information Security, SOC Analysts, Penetration Testing, and Network Architecture.\n\n### Our Rigorous Technical Vetting\nEvery candidate is evaluated through code sample reviews, problem-solving challenges, and architectural aptitude assessments before reaching your interview panel."
            ],
            'Non-IT Recruitment' => [
                'slug' => 'non-it-recruitment',
                'image' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Sales, HR, finance, supply chain, marketing, operations and customer support professionals.',
                'content' => "### Driving Business Performance with Exceptional Non-Tech Professionals\n\nBehind every successful enterprise is a foundation of strong operational, financial, and strategic talent. ADISOMA Solution delivers specialized Non-IT recruitment services to build high-performance functional teams across key corporate verticals.\n\n### Functional Verticals Covered\n- **Sales, BD & Client Relations**: B2B Sales Executives, Key Account Managers, and Regional Heads.\n- **Finance & Accounting**: Chartered Accountants, Financial Controllers, Audit Specialists, and Tax Consultants.\n- **Human Resources & Talent Acquisition**: HR Business Partners, Talent Acquisition Specialists, and L&D Managers.\n- **Operations, Logistics & Supply Chain**: Procurement Managers, Supply Chain Coordinators, and Plant Operations.\n- **Marketing & Brand Communication**: Digital Marketers, Brand Managers, and Content Strategists."
            ],
            'Executive Search' => [
                'slug' => 'executive-search',
                'image' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Confidential, discreet search and placement for C-suite leaders, directors, and strategic executives.',
                'content' => "### Leadership That Defines Tomorrow\n\nExecutive leadership positions dictate the trajectory and culture of an enterprise. Appointing the wrong executive carries monumental financial and cultural costs. ADISOMA Solution offers bespoke, highly confidential Executive Search services to place visionary leaders into C-Suite, VP, and Director-level roles.\n\n### What Sets Our Executive Search Apart\n- **Absolute Discretion & Confidentiality**: Protecting both the client's corporate privacy and the candidate's career interests.\n- **Comprehensive Market Mapping**: Thorough competitive benchmarking and talent landscape assessment.\n- **Leadership Competency Evaluation**: Strategic vision, change management capability, and executive presence analysis.\n- **Offer Structuring & Negotiation**: Equity packages, long-term incentive plans (LTIP), and severance negotiation guidance."
            ],
            'HR Consulting' => [
                'slug' => 'hr-consulting',
                'image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=800&q=80',
                'desc' => 'Hiring strategy, compensation benchmarking, onboarding frameworks and workforce planning support.',
                'content' => "### Strategic People Solutions for Growing Organizations\n\nAs organizations evolve, managing the human capital function requires structure, compliance, and strategic vision. ADISOMA Solution partners with startups, SMEs, and established corporations to deliver bespoke HR advisory and workforce transformation services.\n\n### Consulting Services We Provide\n- **Compensation & Benefits Benchmarking**: Ensure competitive remuneration models aligned with industry trends.\n- **Organizational Design & Workforce Planning**: Structuring reporting lines, role definitions, and capability planning.\n- **HR Policy Formulation & Compliance**: Designing employee handbooks, statutory compliance frameworks, and grievance policies.\n- **Performance Management Systems (PMS)**: Implementing OKRs, KPIs, and transparent appraisal mechanisms.\n- **Employer Branding Strategy**: Positioning your organization as an employer of choice in competitive talent markets."
            ]
        ];

        foreach ($defaults as $title => $data) {
            $stmt = db()->prepare("SELECT id, slug, content, image_url FROM services WHERE title = ?");
            $stmt->execute([$title]);
            $s = $stmt->fetch();
            if ($s) {
                $needsUpdate = empty($s['slug']) || empty($s['content']) || empty($s['image_url']) || strpos((string)$s['image_url'], '1556761175') !== false;
                if ($needsUpdate) {
                    $slugVal = !empty($s['slug']) ? $s['slug'] : $data['slug'];
                    $contentVal = !empty($s['content']) ? $s['content'] : $data['content'];
                    $imgVal = (!empty($s['image_url']) && strpos($s['image_url'], '1556761175') === false) ? $s['image_url'] : $data['image'];
                    db()->prepare("UPDATE services SET slug = ?, content = ?, image_url = ? WHERE id = ?")
                        ->execute([$slugVal, $contentVal, $imgVal, $s['id']]);
                }
            } else {
                db()->prepare("INSERT INTO services(title, slug, description, content, image_url, sort_order, active) VALUES (?, ?, ?, ?, ?, 99, 1)")
                    ->execute([$title, $data['slug'], $data['desc'], $data['content'], $data['image']]);
            }
        }
    } catch (Exception $e) {
        // Silently continue
    }
}
ensure_services_schema();

function asset_v(?string $override = null): string {
    static $v = null;
    if ($override !== null) {
        $v = $override;
        return $v;
    }
    if ($v !== null) return $v;
    $file = __DIR__ . '/../config/cache_version.txt';
    if (file_exists($file)) {
        $content = trim((string)@file_get_contents($file));
        if ($content !== '') {
            $v = $content;
            return $v;
        }
    }
    $v = '2.3';
    return $v;
}

function asset_v_time(): string {
    $v = asset_v();
    if (is_numeric($v) && (int)$v > 1600000000) {
        return date('d M Y, h:i A', (int)$v);
    }
    $file = __DIR__ . '/../config/cache_version.txt';
    if (file_exists($file)) {
        $mtime = @filemtime($file);
        if ($mtime) {
            return date('d M Y, h:i A', $mtime);
        }
    }
    return date('d M Y, h:i A');
}

function clear_all_cache(): string {
    $new_v = (string)time();
    $file = __DIR__ . '/../config/cache_version.txt';
    @file_put_contents($file, $new_v);
    asset_v($new_v);

    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }
    if (function_exists('clearstatcache')) {
        @clearstatcache(true);
    }

    return $new_v;
}
