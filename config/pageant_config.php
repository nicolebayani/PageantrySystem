<?php
require_once 'database.php';
require_once 'settings.php';

// Global function to get pageant settings with cache invalidation support
function getPageantSettings($forceRefresh = false) {
    static $cachedSettings = null;
    static $cacheTimestamp = null;
    
    // Check if we need to refresh the cache
    $shouldRefresh = $forceRefresh || 
                    $cachedSettings === null || 
                    (isset($_SESSION['settings_updated']) && $_SESSION['settings_updated'] > $cacheTimestamp);
    
    if ($shouldRefresh) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            $settings = new Settings($db);
            $cachedSettings = $settings->getAll();
            $cacheTimestamp = time();
            
            // Clear the session flag after refreshing
            if (isset($_SESSION['settings_updated'])) {
                unset($_SESSION['settings_updated']);
            }
        } catch (Exception $e) {
            // Fallback to default settings if database is not available
            $cachedSettings = [
                'pageant_name' => 'Pageantry Competition',
                'primary_color' => '#667eea',
                'secondary_color' => '#764ba2',
                'accent_color' => '#ffd700',
                'logo_text' => '👑',
                'theme_style' => 'gradient',
                'background_style' => 'gradient',
                'card_style' => 'glassmorphism'
            ];
            $cacheTimestamp = time();
        }
    }
    
    return $cachedSettings;
}

// Function to invalidate settings cache across the system
function invalidateSettingsCache() {
    // Set a session flag to indicate settings were updated
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['settings_updated'] = time();
    
    // Also try to clear any file-based cache if implemented
    $cacheFile = __DIR__ . '/../cache/settings_cache.json';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
}

// Function to generate dynamic CSS
function generatePageantCSS() {
    $settings = getPageantSettings();
    
    $css = "
    <style>
    :root {
        --pageant-primary: {$settings['primary_color']};
        --pageant-secondary: {$settings['secondary_color']};
        --pageant-accent: {$settings['accent_color']};
        --pageant-gradient: linear-gradient(135deg, {$settings['primary_color']} 0%, {$settings['secondary_color']} 100%);
        --pageant-accent-gradient: linear-gradient(135deg, {$settings['accent_color']} 0%, " . lightenColor($settings['accent_color'], 20) . " 100%);
    }
    
    /* Dynamic theme styles */
    .navbar-dark.bg-primary {
        background: var(--pageant-gradient) !important;
    }
    
    .btn-primary {
        background: var(--pageant-gradient) !important;
        border-color: var(--pageant-primary) !important;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, " . darkenColor($settings['primary_color'], 10) . " 0%, " . darkenColor($settings['secondary_color'], 10) . " 100%) !important;
    }
    
    .text-primary {
        color: var(--pageant-primary) !important;
    }
    
    .bg-primary {
        background: var(--pageant-gradient) !important;
    }
    
    .hero-section h1 {
        background: linear-gradient(45deg, var(--pageant-accent) 0%, " . lightenColor($settings['accent_color'], 30) . " 25%, #ffffff 50%, " . lightenColor($settings['accent_color'], 30) . " 75%, var(--pageant-accent) 100%);
        background-size: 300% 300%;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: royalShimmer 4s ease-in-out infinite;
    }
    
    .winner-card {
        background: var(--pageant-accent-gradient) !important;
        border: 3px solid var(--pageant-accent) !important;
    }
    
    .progress-bar {
        background: var(--pageant-gradient) !important;
    }
    
    .badge.bg-primary {
        background: var(--pageant-gradient) !important;
    }
    
    /* Background style variations */";
    
    if ($settings['background_style'] === 'gradient') {
        $css .= "
        body {
            background: linear-gradient(135deg, " . darkenColor($settings['primary_color'], 20) . " 0%, {$settings['primary_color']} 25%, {$settings['secondary_color']} 75%, " . darkenColor($settings['secondary_color'], 20) . " 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }";
    } elseif ($settings['background_style'] === 'solid') {
        $css .= "
        body {
            background: {$settings['primary_color']};
        }";
    }
    
    $css .= "
    </style>";
    
    return $css;
}

// Helper function to lighten color
function lightenColor($color, $percent) {
    $color = str_replace('#', '', $color);
    $r = hexdec(substr($color, 0, 2));
    $g = hexdec(substr($color, 2, 2));
    $b = hexdec(substr($color, 4, 2));
    
    $r = min(255, $r + ($percent * 255 / 100));
    $g = min(255, $g + ($percent * 255 / 100));
    $b = min(255, $b + ($percent * 255 / 100));
    
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

// Helper function to darken color
function darkenColor($color, $percent) {
    $color = str_replace('#', '', $color);
    $r = hexdec(substr($color, 0, 2));
    $g = hexdec(substr($color, 2, 2));
    $b = hexdec(substr($color, 4, 2));
    
    $r = max(0, $r - ($percent * 255 / 100));
    $g = max(0, $g - ($percent * 255 / 100));
    $b = max(0, $b - ($percent * 255 / 100));
    
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}
?>
