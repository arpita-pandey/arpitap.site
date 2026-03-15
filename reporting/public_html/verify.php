<?php
/**
 * System Verification Script
 * Run this to check if all components are properly set up
 */

echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n<title>Analytics System Verification</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f7fa; }\n";
echo ".check { padding: 12px; margin: 10px 0; border-radius: 4px; border-left: 4px solid; }\n";
echo ".success { background: #efe; border-left-color: #3a3; color: #3a3; }\n";
echo ".error { background: #fee; border-left-color: #c33; color: #c33; }\n";
echo ".warning { background: #fef9e7; border-left-color: #f39c12; color: #f39c12; }\n";
echo ".info { background: #eff; border-left-color: #339; color: #339; }\n";
echo "h1 { color: #333; }\n";
echo ".section { margin-top: 30px; }\n";
echo ".summary { background: white; padding: 20px; border-radius: 4px; margin-top: 30px; }\n";
echo "</style>\n</head>\n<body>\n";

echo "<h1>📋 Analytics System Verification</h1>\n";
echo "<p>Checking system configuration and dependencies...</p>\n";

$errors = [];
$warnings = [];
$successes = [];

// Check 1: PHP Version
echo "<div class='section'><h2>PHP & Server</h2>\n";
if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
    echo "<div class='check success'>✓ PHP Version: " . PHP_VERSION . "</div>\n";
    $successes[] = "PHP version";
} else {
    echo "<div class='check error'>✗ PHP Version: " . PHP_VERSION . " (requires 7.0+)</div>\n";
    $errors[] = "PHP version too old";
}

// Check 2: Session Support
if (function_exists('session_start')) {
    echo "<div class='check success'>✓ Session Support: Enabled</div>\n";
    $successes[] = "Session support";
} else {
    echo "<div class='check error'>✗ Session Support: Disabled</div>\n";
    $errors[] = "Session support disabled";
}

// Check 3: PDO MySQL
if (extension_loaded('pdo_mysql')) {
    echo "<div class='check success'>✓ PDO MySQL: Installed</div>\n";
    $successes[] = "PDO MySQL";
} else {
    echo "<div class='check error'>✗ PDO MySQL: Not installed</div>\n";
    $errors[] = "PDO MySQL not available";
}

// Check 4: Required Files
echo "</div>\n<div class='section'><h2>Required Files</h2>\n";

$required_files = [
    '/var/www/reporting.arpitap.site/includes/db.php' => 'Database configuration',
    '/var/www/reporting.arpitap.site/includes/auth.php' => 'Authentication module',
    '/var/www/reporting.arpitap.site/includes/header.php' => 'Header template',
    '/var/www/reporting.arpitap.site/includes/footer.php' => 'Footer template',
    '/var/www/reporting.arpitap.site/public_html/login.php' => 'Login page',
    '/var/www/reporting.arpitap.site/public_html/dashboard.php' => 'Dashboard',
];

foreach ($required_files as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='check success'>✓ $description</div>\n";
        $successes[] = $description;
    } else {
        echo "<div class='check error'>✗ $description not found: $file</div>\n";
        $errors[] = "Missing: " . $description;
    }
}

// Check 5: Directories
echo "</div>\n<div class='section'><h2>Directory Structure</h2>\n";

$required_dirs = [
    '/var/www/reporting.arpitap.site/public_html/admin' => 'Admin pages',
    '/var/www/reporting.arpitap.site/public_html/analyst' => 'Analyst pages',
    '/var/www/reporting.arpitap.site/public_html/viewer' => 'Viewer pages',
    '/var/www/reporting.arpitap.site/public_html/exports' => 'Export directory',
];

foreach ($required_dirs as $dir => $description) {
    if (is_dir($dir)) {
        echo "<div class='check success'>✓ $description</div>\n";
        $successes[] = "$description directory";
    } else {
        echo "<div class='check error'>✗ $description directory not found: $dir</div>\n";
        $errors[] = "Missing directory: " . $description;
    }
}

// Check 6: Database Connection
echo "</div>\n<div class='section'><h2>Database Connection</h2>\n";

try {
    require_once '/var/www/reporting.arpitap.site/includes/db.php';
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<div class='check success'>✓ Database Connected</div>\n";
    echo "<div class='check info'>Found " . $result['count'] . " users in system</div>\n";
    $successes[] = "Database connection";
    
    // Check tables
    $tables = ['users', 'sections', 'analyst_sections', 'report_categories', 'reports', 'report_viewers', 'exports'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->fetch()) {
            echo "<div class='check success'>✓ Table: $table</div>\n";
        } else {
            echo "<div class='check error'>✗ Table missing: $table</div>\n";
            $errors[] = "Missing table: $table";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='check error'>✗ Database Error: " . htmlspecialchars($e->getMessage()) . "</div>\n";
    $errors[] = "Database: " . $e->getMessage();
}

// Check 7: Permissions
echo "</div>\n<div class='section'><h2>File Permissions</h2>\n";

$export_dir = '/var/www/reporting.arpitap.site/public_html/exports';
if (is_writable($export_dir)) {
    echo "<div class='check success'>✓ Export directory is writable</div>\n";
    $successes[] = "Export directory permissions";
} else {
    echo "<div class='check warning'>⚠ Export directory may not be writable</div>\n";
    $warnings[] = "Export directory permissions (may need 755)";
}

// Summary
echo "</div>\n<div class='section summary'>\n";
echo "<h2>Summary</h2>\n";

$total = count($successes) + count($errors) + count($warnings);
$status = empty($errors) ? 'operational' : 'needs-attention';

if (empty($errors)) {
    echo "<div class='check success'>✓ System Status: READY</div>\n";
} else {
    echo "<div class='check error'>✗ System Status: NEEDS ATTENTION</div>\n";
}

echo "<p style='margin: 15px 0;'><strong>Summary:</strong></p>\n";
echo "<ul style='margin-left: 20px;'>\n";
echo "<li><span style='color: #3a3;'>" . count($successes) . " checks passed</span></li>\n";
if (count($warnings) > 0) {
    echo "<li><span style='color: #f39c12;'>" . count($warnings) . " warnings</span></li>\n";
}
if (count($errors) > 0) {
    echo "<li><span style='color: #c33;'>" . count($errors) . " errors found</span></li>\n";
}
echo "</ul>\n";

echo "<h3 style='margin-top: 20px; color: #333;'>Next Steps</h3>\n";
echo "<ol style='margin-left: 20px;'>\n";

if (count($errors) === 0) {
    echo "<li>Visit <a href='login.php' style='color: #667eea; text-decoration: none;'><strong>Login Page</strong></a></li>\n";
    echo "<li>Use demo credentials to test the system</li>\n";
    echo "<li>Change default passwords in production</li>\n";
} else {
    echo "<li>Fix the errors listed above</li>\n";
    echo "<li>Run this verification again</li>\n";
}

echo "</ol>\n";
echo "</div>\n";

echo "</body>\n</html>\n";
?>
