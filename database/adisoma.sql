CREATE DATABASE IF NOT EXISTS adisoma CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE adisoma;
CREATE TABLE admins(id INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(50) UNIQUE NOT NULL,password VARCHAR(255) NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE services(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(120) NOT NULL,description TEXT NOT NULL,image_url VARCHAR(255) DEFAULT '',sort_order INT DEFAULT 0,active TINYINT(1) DEFAULT 1);
CREATE TABLE jobs(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(150) NOT NULL,category ENUM('IT','Non-IT') NOT NULL DEFAULT 'IT',location VARCHAR(100) NOT NULL,job_type VARCHAR(30) NOT NULL DEFAULT 'Full-time',experience VARCHAR(50),salary VARCHAR(60),description TEXT NOT NULL,status ENUM('draft','published') NOT NULL DEFAULT 'draft',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(status),INDEX(category));
CREATE TABLE applications(id INT AUTO_INCREMENT PRIMARY KEY,job_id INT NULL,name VARCHAR(100) NOT NULL,email VARCHAR(120) NOT NULL,phone VARCHAR(30),experience VARCHAR(50),message TEXT,resume VARCHAR(100) NOT NULL,status ENUM('new','reviewed','shortlisted','rejected') DEFAULT 'new',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(job_id) REFERENCES jobs(id) ON DELETE SET NULL);
CREATE TABLE inquiries(id INT AUTO_INCREMENT PRIMARY KEY,type ENUM('contact','employer') NOT NULL,name VARCHAR(100) NOT NULL,email VARCHAR(120) NOT NULL,phone VARCHAR(30),company VARCHAR(120),subject VARCHAR(150),message TEXT NOT NULL,status ENUM('new','read','closed') DEFAULT 'new',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE testimonials(id INT AUTO_INCREMENT PRIMARY KEY,author_name VARCHAR(100) NOT NULL,content TEXT NOT NULL,rating INT DEFAULT 5,active TINYINT(1) DEFAULT 1,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE blogs(
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
);
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(admin_id) REFERENCES admins(id) ON DELETE SET NULL
);
CREATE TABLE site_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metric VARCHAR(50) NOT NULL,
    label VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0,
    active TINYINT(1) DEFAULT 1
);
CREATE TABLE nav_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    menu_type ENUM('header', 'admin') NOT NULL DEFAULT 'header',
    sort_order INT DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    is_custom TINYINT(1) DEFAULT 0,
    INDEX(menu_type),
    INDEX(sort_order)
);
CREATE TABLE site_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    category VARCHAR(30) DEFAULT 'general',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO services(title,description,image_url,sort_order) VALUES
('Permanent Recruitment','End-to-end sourcing, screening and placement of full-time talent.','https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=400&q=80',1),
('Contract Staffing','Flexible, skilled professionals for project-based and short-term needs.','https://images.unsplash.com/photo-1556761175-5973dc0f32b7?auto=format&fit=crop&w=400&q=80',2),
('IT Recruitment','Developers, testers, DevOps, data and cloud specialists.','https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=400&q=80',3),
('Non-IT Recruitment','Sales, HR, finance, operations and support professionals.','https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=400&q=80',4),
('Executive Search','Confidential search for leadership and niche roles.','https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&w=400&q=80',5),
('HR Consulting','Hiring strategy, onboarding and workforce planning support.','https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=400&q=80',6);

INSERT INTO jobs(title,category,location,job_type,experience,salary,description,status) VALUES
('PHP Developer','IT','Sample City','Full-time','2-4 years','As per industry standards','Sample job. Build and maintain PHP/MySQL applications.','published'),
('HR Executive','Non-IT','Sample City','Full-time','1-3 years','As per industry standards','Sample job. Manage recruitment and employee relations.','published');

INSERT INTO testimonials(author_name,content,rating) VALUES
('Rahul Desai','ADISOMA is a strategic thinker and one of the wisest teams to work with. I started working with them a couple of years ago and now it\'s a more over friendly relation over businesses. I highly recommend working with them to get the best out of everything.', 5),
('Priya Sharma','The recruitment process was incredibly smooth. They understood our specific requirements perfectly and delivered top-tier candidates within days. Our engineering team scaled rapidly without compromising on quality.', 5),
('Amit Patel','Outstanding service! Their IT staffing solutions are unmatched. We struggled for months to fill a niche developer role, and ADISOMA found the perfect match in less than a week.', 5);

INSERT INTO site_stats(metric,label,sort_order,active) VALUES
('500+','Placements',1,1),
('100+','Clients',2,1),
('10+','Industries',3,1),
('24h','Response',4,1);

INSERT INTO nav_items(title,url,menu_type,sort_order,active,is_custom) VALUES
('Home','index.php','header',1,1,0),
('About','about.php','header',2,1,0),
('Services','services.php','header',3,1,0),
('Jobs','jobs.php','header',4,1,0),
('Blog','blogs.php','header',5,1,0),
('For Employers','employers.php','header',6,1,0),
('Job Seekers','job-seekers.php','header',7,1,0),
('Contact','contact.php','header',8,1,0),
('Dashboard','dashboard.php','admin',1,1,0),
('Jobs','manage-jobs.php','admin',2,1,0),
('Blogs','manage-blogs.php','admin',3,1,0),
('Applications','applications.php','admin',4,1,0),
('Inquiries','inquiries.php','admin',5,1,0),
('Services','manage-services.php','admin',6,1,0),
('Testimonials','manage-testimonials.php','admin',7,1,0),
('Site Stats','manage-stats.php','admin',8,1,0),
('Menu Manager','manage-menu.php','admin',9,1,0),
('Site Settings','manage-settings.php','admin',10,1,0),
('Admins','manage-admins.php','admin',11,1,0),
('Logs','logs.php','admin',12,1,0);

INSERT INTO site_settings (setting_key, setting_value, category) VALUES
('name', 'ADISOMA Solution', 'company'),
('tagline', 'Your Talent Partner for a Brighter Tomorrow.', 'company'),
('address', 'Mumbai, Maharashtra', 'contact'),
('phone', '+91 98765 43210', 'contact'),
('whatsapp', '+91 98765 43210', 'contact'),
('email', 'info@adisomasolution.com', 'contact'),
('hours', 'Mon - Sat: 9:00 AM - 6:00 PM', 'contact'),
('maps_url', '', 'contact'),
('facebook', 'https://facebook.com', 'social'),
('linkedin', 'https://linkedin.com', 'social'),
('twitter', 'https://x.com', 'social'),
('instagram', 'https://instagram.com', 'social'),
('youtube', '', 'social');

INSERT INTO blogs(title, slug, excerpt, content, image_url, author, status) VALUES
('Top Hiring Trends in 2026: Navigating the Modern Talent Landscape', 'top-hiring-trends-in-2026', 'Discover the key recruitment dynamics reshaping how companies attract, hire, and retain high-performing talent across IT and non-IT sectors.', 'The recruitment ecosystem is evolving at an unprecedented speed. In 2026, organizations are shifting from transactional hiring to relationship-driven talent partnerships.\n\n### 1. Skills-First Recruitment\nModern enterprises are valuing demonstrable practical skills, problem-solving capability, and cultural adaptability over traditional pedigree. Sourcing techniques now leverage behavioral assessments and project simulations to identify candidates who will thrive from day one.\n\n### 2. Flexible Workforce Models\nWith companies seeking agility, contract staffing and project-based talent solutions continue to surge. Organizations that leverage hybrid talent pipelines can quickly scale capacity without inflating fixed overheads.\n\n### 3. Accelerated Candidate Experience\nTop candidates have multiple options. A streamlined, transparent interview process is the single biggest differentiator between landing top talent and losing them to competitors.\n\nAt ADISOMA Solution, we help enterprises align their workforce strategy with market realities, ensuring sustainable organizational growth.', 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=800&q=80', 'ADISOMA Team', 'published'),
('How to Build an Agile IT Engineering Team on Demand', 'how-to-build-an-agile-it-engineering-team', 'Learn strategic staffing approaches to assemble niche software, cloud, and DevOps engineering teams without lengthy hiring bottlenecks.', 'Scaling technical teams is one of the most critical challenges facing CTOs and engineering managers today. Niche skill sets in full-stack architecture, cloud engineering, and cybersecurity are in high demand.\n\n### Defining the Core vs. Specialized Roles\nWhen scaling up engineering teams, it is important to clearly delineate foundational full-time positions from specialized, temporary consulting needs. By blending permanent core engineers with agile contract specialists, businesses can accelerate release cycles while controlling costs.\n\n### Effective Pre-screening is Vital\nScreening developers goes beyond reviewing CV keywords. Evaluating code maintainability, system design familiarity, and communication skills ensures immediate productivity once onboarding starts.\n\nPartnering with a specialized IT recruitment partner like ADISOMA gives companies rapid access to thoroughly screened talent communities.', 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80', 'Tech Recruitment Desk', 'published');

