<?php
class Settings {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
        $this->initializeSettings();
    }
    
    private function initializeSettings() {
        // Create settings table if it doesn't exist
        $createTable = "
            CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(50) UNIQUE NOT NULL,
                setting_value TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        try {
            $this->db->exec($createTable);
            
            // Insert default settings if they don't exist
            $defaultSettings = [
                'pageant_name' => 'Pageantry Competition',
                'primary_color' => '#667eea',
                'secondary_color' => '#764ba2',
                'accent_color' => '#ffd700',
                'logo_text' => '👑',
                'logo_image' => '',
                'logo_type' => 'emoji',
                'theme_style' => 'gradient',
                'background_style' => 'gradient',
                'card_style' => 'glassmorphism'
            ];
            
            foreach ($defaultSettings as $key => $value) {
                $checkQuery = "SELECT COUNT(*) FROM settings WHERE setting_key = ?";
                $checkStmt = $this->db->prepare($checkQuery);
                $checkStmt->execute([$key]);
                
                if ($checkStmt->fetchColumn() == 0) {
                    $insertQuery = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)";
                    $insertStmt = $this->db->prepare($insertQuery);
                    $insertStmt->execute([$key, $value]);
                }
            }
        } catch (PDOException $e) {
            error_log("Settings initialization error: " . $e->getMessage());
        }
    }
    
    public function get($key, $default = null) {
        try {
            $query = "SELECT setting_value FROM settings WHERE setting_key = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$key]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['setting_value'] : $default;
        } catch (PDOException $e) {
            error_log("Settings get error: " . $e->getMessage());
            return $default;
        }
    }
    
    public function set($key, $value) {
        try {
            $query = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$key, $value]);
            
            // Invalidate cache when settings are updated
            if ($result && function_exists('invalidateSettingsCache')) {
                invalidateSettingsCache();
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Settings set error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAll() {
        try {
            $query = "SELECT setting_key, setting_value FROM settings";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $settings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            return $settings;
        } catch (PDOException $e) {
            error_log("Settings getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateMultiple($settings) {
        try {
            $this->db->beginTransaction();
            
            foreach ($settings as $key => $value) {
                // Use direct database update to avoid multiple cache invalidations
                $query = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$key, $value]);
            }
            
            $this->db->commit();
            
            // Invalidate cache once after all updates
            if (function_exists('invalidateSettingsCache')) {
                invalidateSettingsCache();
            }
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Settings updateMultiple error: " . $e->getMessage());
            return false;
        }
    }
    
    public function generateCSS() {
        $settings = $this->getAll();
        
        // Generate background based on style
        $backgroundStyle = '';
        switch ($settings['background_style']) {
            case 'solid':
                $backgroundStyle = "background: {$settings['primary_color']} !important;";
                break;
            case 'pattern':
                $backgroundStyle = "
                    background: {$settings['primary_color']} !important;
                    background-image: 
                        radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 2px, transparent 2px),
                        radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
                    background-size: 60px 60px, 40px 40px;
                ";
                break;
            default: // gradient
                $backgroundStyle = "
                    background: linear-gradient(135deg, {$settings['primary_color']} 0%, {$settings['secondary_color']} 25%, {$settings['accent_color']} 50%, {$settings['secondary_color']} 75%, {$settings['primary_color']} 100%) !important;
                    background-size: 400% 400% !important;
                    animation: gradientShift 15s ease infinite !important;
                ";
                break;
        }
        
        $css = "
        :root {
            --pageant-primary: {$settings['primary_color']};
            --pageant-secondary: {$settings['secondary_color']};
            --pageant-accent: {$settings['accent_color']};
            --pageant-gradient: linear-gradient(135deg, {$settings['primary_color']} 0%, {$settings['secondary_color']} 100%);
            --pageant-accent-gradient: linear-gradient(135deg, {$settings['accent_color']} 0%, " . $this->lightenColor($settings['accent_color'], 20) . " 100%);
        }
        
        body {
            {$backgroundStyle}
        }
        
        .navbar-brand, .hero-section h1, .pageant-primary { color: var(--pageant-primary) !important; }
        .bg-primary, .btn-primary, .navbar-dark.bg-primary { 
            background: " . ($settings['theme_style'] === 'gradient' ? 'var(--pageant-gradient)' : 'var(--pageant-primary)') . " !important; 
            border-color: var(--pageant-primary) !important; 
        }
        .text-primary { color: var(--pageant-primary) !important; }
        .border-primary { border-color: var(--pageant-primary) !important; }
        .pageant-accent { color: var(--pageant-accent) !important; }
        .bg-pageant-accent { background: var(--pageant-accent-gradient) !important; }
        .progress-bar { background: var(--pageant-gradient) !important; }
        .badge.bg-primary { background: var(--pageant-primary) !important; }
        
        /* Card styles based on settings */
        .card, .dashboard-card, .settings-card {
            " . ($settings['card_style'] === 'glassmorphism' ? 
                'background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.95) 100%) !important;
                 backdrop-filter: blur(10px) !important;
                 border: 1px solid rgba(255, 255, 255, 0.2) !important;' :
                ($settings['card_style'] === 'shadow' ?
                 'background: rgba(255, 255, 255, 0.98) !important;
                  box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15) !important;
                  border: 1px solid rgba(0, 0, 0, 0.05) !important;' :
                 'background: rgba(255, 255, 255, 1) !important;
                  box-shadow: none !important;
                  border: none !important;')) . "
        }
        ";
        
        return $css;
    }
    
    private function lightenColor($color, $percent) {
        $color = str_replace('#', '', $color);
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
        
        $r = min(255, $r + ($percent * 255 / 100));
        $g = min(255, $g + ($percent * 255 / 100));
        $b = min(255, $b + ($percent * 255 / 100));
        
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
}
?>
