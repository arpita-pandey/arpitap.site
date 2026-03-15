CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'analyst', 'viewer') NOT NULL DEFAULT 'viewer',
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS analyst_sections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    analyst_id INT NOT NULL,
    section_id INT NOT NULL,
    FOREIGN KEY (analyst_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE,
    UNIQUE KEY unique_analyst_section (analyst_id, section_id)
);

CREATE TABLE IF NOT EXISTS report_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    section_id INT NOT NULL,
    icon VARCHAR(100),
    color VARCHAR(7),
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    analyst_id INT NOT NULL,
    content LONGTEXT,
    analyst_comments LONGTEXT,
    is_public BOOLEAN DEFAULT 0,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES report_categories(id),
    FOREIGN KEY (analyst_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS report_viewers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_id INT NOT NULL,
    viewer_id INT NOT NULL,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (viewer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_report_viewer (report_id, viewer_id)
);

CREATE TABLE IF NOT EXISTS exports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_id INT,
    user_id INT NOT NULL,
    export_type ENUM('pdf', 'email') DEFAULT 'pdf',
    file_path VARCHAR(500),
    email_address VARCHAR(255),
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO sections (name, description) VALUES ('Performance', 'Sales and operational performance metrics');
INSERT INTO sections (name, description) VALUES ('Behavioral', 'Customer behavior and engagement analytics');
INSERT INTO sections (name, description) VALUES ('Financial', 'Financial statements and budget analysis');

INSERT INTO report_categories (name, section_id, icon, color) VALUES ('Sales Performance Dashboard', 1, '#1F77B4', '#3498db');
INSERT INTO report_categories (name, section_id, icon, color) VALUES ('Monthly Revenue Trends', 1, '#FF7F0E', '#2ecc71');
INSERT INTO report_categories (name, section_id, icon, color) VALUES ('Customer Engagement Metrics', 2, '#2CA02C', '#9b59b6');
INSERT INTO report_categories (name, section_id, icon, color) VALUES ('User Behavior Analysis', 2, '#D62728', '#e74c3c');
INSERT INTO report_categories (name, section_id, icon, color) VALUES ('Budget vs Actual', 3, '#9467BD', '#f39c12');
INSERT INTO report_categories (name, section_id, icon, color) VALUES ('Financial Health Score', 3, '#8C564B', '#1abc9c');

INSERT INTO users (username, email, password_hash, role) VALUES ('super_admin', 'admin@example.com', '$2y$10$YrB8.m7n0.6SqquB9VfNsuG7NPzWxDLYfBKdZkCqPVlYyxZvEsJDW', 'super_admin');
INSERT INTO users (username, email, password_hash, role) VALUES ('analyst_sam', 'sam@example.com', '$2y$10$YrB8.m7n0.6SqquB9VfNsuG7NPzWxDLYfBKdZkCqPVlYyxZvEsJDW', 'analyst');
INSERT INTO users (username, email, password_hash, role) VALUES ('analyst_sally', 'sally@example.com', '$2y$10$YrB8.m7n0.6SqquB9VfNsuG7NPzWxDLYfBKdZkCqPVlYyxZvEsJDW', 'analyst');
INSERT INTO users (username, email, password_hash, role) VALUES ('viewer_john', 'john@example.com', '$2y$10$YrB8.m7n0.6SqquB9VfNsuG7NPzWxDLYfBKdZkCqPVlYyxZvEsJDW', 'viewer');

INSERT INTO analyst_sections (analyst_id, section_id) VALUES (2, 1);
INSERT INTO analyst_sections (analyst_id, section_id) VALUES (3, 1);
INSERT INTO analyst_sections (analyst_id, section_id) VALUES (3, 2);

CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_analyst_sections_analyst ON analyst_sections(analyst_id);
CREATE INDEX idx_reports_analyst ON reports(analyst_id);
