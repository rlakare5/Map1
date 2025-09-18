<?php
require_once 'database.php';

$db = new Database();
$conn = $db->getConnection();

// Create tables
$tables = [
    // Students table
    "CREATE TABLE IF NOT EXISTS students (
        prn VARCHAR(20) PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        middle_name VARCHAR(50),
        last_name VARCHAR(50) NOT NULL,
        dept VARCHAR(100) NOT NULL,
        year INTEGER NOT NULL,
        programme VARCHAR(50) NOT NULL,
        course_duration INTEGER NOT NULL,
        admission_year VARCHAR(9) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Coordinators table
    "CREATE TABLE IF NOT EXISTS coordinators (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL,
        dept VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    
    // HoDs table
    "CREATE TABLE IF NOT EXISTS hods (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL,
        dept VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Admins table
    "CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Categories table
    "CREATE TABLE IF NOT EXISTS categories (
        id CHAR(1) PRIMARY KEY,
        name VARCHAR(100) NOT NULL
    )",
    
    // Activities master table
    "CREATE TABLE IF NOT EXISTS activities_master (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id CHAR(1) NOT NULL,
        activity_name VARCHAR(150) NOT NULL,
        document_evidence VARCHAR(150) NOT NULL,
        points_type VARCHAR(10) CHECK(points_type IN ('Fixed','Level')) NOT NULL,
        min_points INTEGER DEFAULT NULL,
        max_points INTEGER DEFAULT NULL,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )",
    
    // Activity levels table
    "CREATE TABLE IF NOT EXISTS activity_levels (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        activity_id INTEGER NOT NULL,
        level VARCHAR(50) NOT NULL,
        points INTEGER NOT NULL,
        FOREIGN KEY (activity_id) REFERENCES activities_master(id) ON DELETE CASCADE
    )",
    
    // Programme rules table
    "CREATE TABLE IF NOT EXISTS programme_rules (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        admission_year VARCHAR(9) NOT NULL,
        programme VARCHAR(50) NOT NULL,
        duration INTEGER NOT NULL,
        technical INTEGER NOT NULL,
        sports_cultural INTEGER NOT NULL,
        community_outreach INTEGER NOT NULL,
        innovation INTEGER NOT NULL,
        leadership INTEGER NOT NULL,
        total_points INTEGER NOT NULL
    )",
    
    // Activities submitted by students
    "CREATE TABLE IF NOT EXISTS activities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        prn VARCHAR(20) NOT NULL,
        category CHAR(1) NOT NULL,
        activity_type VARCHAR(100) NOT NULL,
        level VARCHAR(20),
        certificate VARCHAR(255),
        date DATE NOT NULL,
        remarks TEXT,
        proof_type VARCHAR(50),
        proof_file VARCHAR(255),
        status VARCHAR(10) CHECK(status IN ('Pending','Approved','Rejected')) DEFAULT 'Pending',
        points INTEGER DEFAULT 0,
        coordinator_remarks TEXT,
        verified_by INTEGER,
        verified_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (prn) REFERENCES students(prn)
    )"
];

// Execute table creation
foreach ($tables as $table_sql) {
    try {
        $conn->exec($table_sql);
    } catch (PDOException $e) {
        echo "Error creating table: " . $e->getMessage() . "\n";
    }
}

// Insert initial data
$initial_data = [
    // Categories
    "INSERT OR IGNORE INTO categories (id, name) VALUES 
        ('A', 'Technical Skills'),
        ('B', 'Sports & Cultural'),
        ('C', 'Community Outreach & Social Initiatives'),
        ('D', 'Innovation / IPR / Entrepreneurship'),
        ('E', 'Leadership / Management')",
    
    // Sample admin user
    "INSERT OR IGNORE INTO admins (id, name, password) VALUES 
        (1, 'System Administrator', '" . password_hash('admin123', PASSWORD_DEFAULT) . "')",
    
    // Programme rules for 2025-2026
    "INSERT OR IGNORE INTO programme_rules 
        (admission_year, programme, duration, technical, sports_cultural, community_outreach, innovation, leadership, total_points)
        VALUES
        ('2025-2026', 'B.Tech', 4, 45, 10, 10, 25, 10, 100),
        ('2025-2026', 'B.Tech (DSY)', 3, 30, 10, 10, 15, 10, 75),
        ('2025-2026', 'Integrated B.Tech', 6, 50, 10, 15, 25, 15, 120),
        ('2025-2026', 'B.Pharm', 4, 45, 10, 15, 20, 10, 100),
        ('2025-2026', 'BCA', 3, 20, 10, 10, 10, 10, 60),
        ('2025-2026', 'MCA', 2, 20, 5, 10, 5, 10, 50),
        ('2025-2026', 'B.Sc', 3, 20, 10, 10, 10, 10, 60),
        ('2025-2026', 'M.Sc', 2, 20, 5, 5, 10, 10, 50),
        ('2025-2026', 'B.Com', 3, 20, 10, 10, 10, 10, 60),
        ('2025-2026', 'M.Com', 2, 20, 5, 5, 10, 10, 50),
        ('2025-2026', 'BBA', 3, 20, 10, 10, 10, 10, 60),
        ('2025-2026', 'MBA', 2, 20, 10, 10, 10, 10, 60)",
    
    // Programme rules for 2024-2025
    "INSERT OR IGNORE INTO programme_rules 
        (admission_year, programme, duration, technical, sports_cultural, community_outreach, innovation, leadership, total_points)
        VALUES
        ('2024-2025', 'B.Tech', 4, 30, 5, 10, 20, 10, 75),
        ('2024-2025', 'B.Tech (DSY)', 3, 20, 5, 5, 15, 5, 50),
        ('2024-2025', 'B.Com', 3, 15, 5, 5, 10, 10, 45),
        ('2024-2025', 'BBA', 3, 20, 5, 5, 5, 10, 45),
        ('2024-2025', 'MBA', 2, 10, 5, 5, 5, 5, 30)"
];

foreach ($initial_data as $data_sql) {
    try {
        $conn->exec($data_sql);
    } catch (PDOException $e) {
        echo "Error inserting data: " . $e->getMessage() . "\n";
    }
}

// Insert activities master data
$activities_data = [
    // Category A - Technical Skills (Level-based)
    "INSERT OR IGNORE INTO activities_master (category_id, activity_name, document_evidence, points_type) VALUES
        ('A', 'Paper Presentation', 'Certificate', 'Level'),
        ('A', 'Project Competition', 'Certificate', 'Level'),
        ('A', 'Hackathons / Ideathons', 'Certificate', 'Level'),
        ('A', 'Poster Competitions', 'Certificate', 'Level'),
        ('A', 'Competitive Programming', 'Certificate', 'Level'),
        ('A', 'Workshop', 'Certificate', 'Level'),
        ('A', 'Industrial Training / Case Studies', 'Certificate', 'Level')",
    
    // Category A - Fixed points
    "INSERT OR IGNORE INTO activities_master (category_id, activity_name, document_evidence, points_type, min_points, max_points) VALUES
        ('A', 'MOOC with Final Assessment', 'Certificate', 'Fixed', 5, 5),
        ('A', 'Internship / Professional Certification', 'Certificate', 'Fixed', 5, 5),
        ('A', 'Industrial / Exhibition Visit', 'Report', 'Fixed', 5, 5),
        ('A', 'Language Proficiency', 'Certificate', 'Fixed', 5, 10)",
    
    // Category B - Sports & Cultural
    "INSERT OR IGNORE INTO activities_master (category_id, activity_name, document_evidence, points_type) VALUES
        ('B', 'Sports Participation', 'Certificate', 'Level'),
        ('B', 'Cultural Participation', 'Certificate', 'Level')",
    
    // Category C - Community Outreach
    "INSERT OR IGNORE INTO activities_master (category_id, activity_name, document_evidence, points_type, min_points, max_points) VALUES
        ('C', 'Community Service (Two Day)', 'Certificate/Letter', 'Fixed', 3, 3),
        ('C', 'Community Service (Up to One Week)', 'Certificate/Letter', 'Fixed', 6, 6),
        ('C', 'Community Service (One Month)', 'Certificate/Letter', 'Fixed', 9, 9),
        ('C', 'Community Service (One Semester/Year)', 'Certificate/Letter', 'Fixed', 12, 12)",
    
    // Category D - Innovation
    "INSERT OR IGNORE INTO activities_master (category_id, activity_name, document_evidence, points_type, min_points, max_points) VALUES
        ('D', 'Entrepreneurship / IPR Workshop', 'Certificate', 'Fixed', 5, 5),
        ('D', 'MSME Programme', 'Certificate', 'Fixed', 5, 5),
        ('D', 'Awards/Recognitions for Products', 'Certificate', 'Fixed', 10, 10),
        ('D', 'Completed Prototype Development', 'Report', 'Fixed', 15, 15),
        ('D', 'Filed a Patent', 'Certificate', 'Fixed', 5, 5),
        ('D', 'Published Patent', 'Certificate', 'Fixed', 10, 10),
        ('D', 'Patent Granted', 'Certificate', 'Fixed', 15, 15)",
    
    // Category E - Leadership
    "INSERT OR IGNORE INTO activities_master (category_id, activity_name, document_evidence, points_type) VALUES
        ('E', 'Club/Association Participation', 'Certificate', 'Level'),
        ('E', 'Club/Association Coordinator', 'Certificate', 'Level')",
    
    "INSERT OR IGNORE INTO activities_master (category_id, activity_name, document_evidence, points_type, min_points, max_points) VALUES
        ('E', 'Professional Society Membership', 'Certificate', 'Fixed', 5, 5),
        ('E', 'Special Initiatives for University', 'Proof', 'Fixed', 5, 5)"
];

foreach ($activities_data as $activity_sql) {
    try {
        $conn->exec($activity_sql);
    } catch (PDOException $e) {
        echo "Error inserting activities: " . $e->getMessage() . "\n";
    }
}

echo "Database initialized successfully!\n";
?>
