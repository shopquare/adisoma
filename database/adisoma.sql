CREATE DATABASE IF NOT EXISTS adisoma CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE adisoma;
CREATE TABLE admins(id INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(50) UNIQUE NOT NULL,password VARCHAR(255) NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE services(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(120) NOT NULL,description TEXT NOT NULL,image_url VARCHAR(255) DEFAULT '',sort_order INT DEFAULT 0,active TINYINT(1) DEFAULT 1);
CREATE TABLE jobs(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(150) NOT NULL,category ENUM('IT','Non-IT') NOT NULL DEFAULT 'IT',location VARCHAR(100) NOT NULL,job_type VARCHAR(30) NOT NULL DEFAULT 'Full-time',experience VARCHAR(50),salary VARCHAR(60),description TEXT NOT NULL,status ENUM('draft','published') NOT NULL DEFAULT 'draft',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(status),INDEX(category));
CREATE TABLE applications(id INT AUTO_INCREMENT PRIMARY KEY,job_id INT NULL,name VARCHAR(100) NOT NULL,email VARCHAR(120) NOT NULL,phone VARCHAR(30),experience VARCHAR(50),message TEXT,resume VARCHAR(100) NOT NULL,status ENUM('new','reviewed','shortlisted','rejected') DEFAULT 'new',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(job_id) REFERENCES jobs(id) ON DELETE SET NULL);
CREATE TABLE inquiries(id INT AUTO_INCREMENT PRIMARY KEY,type ENUM('contact','employer') NOT NULL,name VARCHAR(100) NOT NULL,email VARCHAR(120) NOT NULL,phone VARCHAR(30),company VARCHAR(120),subject VARCHAR(150),message TEXT NOT NULL,status ENUM('new','read','closed') DEFAULT 'new',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE testimonials(id INT AUTO_INCREMENT PRIMARY KEY,author_name VARCHAR(100) NOT NULL,content TEXT NOT NULL,rating INT DEFAULT 5,active TINYINT(1) DEFAULT 1,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
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
