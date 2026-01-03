<?php
// Global header with dynamic settings
require_once __DIR__ . '/../config/pageant_config.php';
// Force refresh if settings were recently updated
$pageantSettings = getPageantSettings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo htmlspecialchars($pageantSettings['pageant_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo isset($cssPath) ? $cssPath : '../assets/css/style.css'; ?>">
    
    <!-- Dynamic Theme CSS -->
    <?php echo generatePageantCSS(); ?>
    
    <script>
        // Auto-refresh page if settings were updated in another tab
        if (sessionStorage.getItem('settingsUpdated')) {
            sessionStorage.removeItem('settingsUpdated');
            // Optional: Show a brief notification that settings were updated
        }
    </script>
    
    <style>
        .navbar-brand-logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            margin-right: 10px;
            border-radius: 8px;
        }
        
        .navbar-brand-emoji {
            font-size: 1.8rem;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo isset($homeUrl) ? $homeUrl : '../index.php'; ?>">
                <?php if (!empty($pageantSettings['logo_image']) && file_exists(__DIR__ . '/../uploads/logos/' . $pageantSettings['logo_image'])): ?>
                    <img src="<?php echo isset($uploadsPath) ? $uploadsPath : '../uploads/logos/'; ?><?php echo $pageantSettings['logo_image']; ?>" 
                         alt="Logo" class="navbar-brand-logo">
                <?php else: ?>
                    <span class="navbar-brand-emoji"><?php echo htmlspecialchars($pageantSettings['logo_text']); ?></span>
                <?php endif; ?>
                <?php echo htmlspecialchars($pageantSettings['pageant_name']); ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php echo isset($navContent) ? $navContent : ''; ?>
            </div>
        </div>
    </nav>
