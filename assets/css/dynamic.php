<?php
// Dynamic CSS Generator based on Settings
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/models/Pengaturan.php';

$database = new Database();
$db = $database->getConnection();
$pengaturan = new Pengaturan($db);

// Get color settings with defaults
$warna_primary = $pengaturan->get('warna_primary') ?? '#667eea';
$warna_secondary = $pengaturan->get('warna_secondary') ?? '#764ba2';
$warna_sidebar = $pengaturan->get('warna_sidebar') ?? '#2c3e50';
$warna_sidebar_header = $pengaturan->get('warna_sidebar_header') ?? '#34495e';
$warna_success = $pengaturan->get('warna_success') ?? '#27ae60';
$warna_danger = $pengaturan->get('warna_danger') ?? '#e74c3c';
$warna_warning = $pengaturan->get('warna_warning') ?? '#f39c12';
$warna_info = $pengaturan->get('warna_info') ?? '#3498db';

// Function to darken color
function darkenColor($hex, $percent = 20) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r - ($r * $percent / 100)));
    $g = max(0, min(255, $g - ($g * $percent / 100)));
    $b = max(0, min(255, $b - ($b * $percent / 100)));
    
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . 
           str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . 
           str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}

// Function to lighten color
function lightenColor($hex, $percent = 20) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = min(255, $r + (255 - $r) * $percent / 100);
    $g = min(255, $g + (255 - $g) * $percent / 100);
    $b = min(255, $b + (255 - $b) * $percent / 100);
    
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . 
           str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . 
           str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}

// Calculate variations
$warna_primary_dark = darkenColor($warna_primary, 15);
$warna_primary_light = lightenColor($warna_primary, 10);
$warna_sidebar_dark = darkenColor($warna_sidebar, 10);

// Status color variations (for alerts)
$warna_success_light = lightenColor($warna_success, 80);
$warna_success_dark = darkenColor($warna_success, 50);

$warna_danger_light = lightenColor($warna_danger, 80);
$warna_danger_dark = darkenColor($warna_danger, 50);

$warna_warning_light = lightenColor($warna_warning, 80);
$warna_warning_dark = darkenColor($warna_warning, 50);

$warna_info_light = lightenColor($warna_info, 80);
$warna_info_dark = darkenColor($warna_info, 50);

// Set content type
header('Content-Type: text/css');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
?>
/* Dynamic CSS - Generated from Settings */
:root {
    /* Primary Colors */
    --color-primary: <?php echo $warna_primary; ?>;
    --color-primary-dark: <?php echo $warna_primary_dark; ?>;
    --color-primary-light: <?php echo $warna_primary_light; ?>;
    --color-secondary: <?php echo $warna_secondary; ?>;
    
    /* Sidebar Colors */
    --color-sidebar: <?php echo $warna_sidebar; ?>;
    --color-sidebar-header: <?php echo $warna_sidebar_header; ?>;
    --color-sidebar-dark: <?php echo $warna_sidebar_dark; ?>;
    
    /* Status Colors */
    --color-success: <?php echo $warna_success; ?>;
    --color-success-light: <?php echo $warna_success_light; ?>;
    --color-success-dark: <?php echo $warna_success_dark; ?>;
    
    --color-danger: <?php echo $warna_danger; ?>;
    --color-danger-light: <?php echo $warna_danger_light; ?>;
    --color-danger-dark: <?php echo $warna_danger_dark; ?>;
    
    --color-warning: <?php echo $warna_warning; ?>;
    --color-warning-light: <?php echo $warna_warning_light; ?>;
    --color-warning-dark: <?php echo $warna_warning_dark; ?>;
    
    --color-info: <?php echo $warna_info; ?>;
    --color-info-light: <?php echo $warna_info_light; ?>;
    --color-info-dark: <?php echo $warna_info_dark; ?>;
}

