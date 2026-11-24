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
