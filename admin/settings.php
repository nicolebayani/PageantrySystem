<?php
session_start();
require_once '../config/database.php';
require_once '../config/settings.php';
require_once '../config/pageant_config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();
$settings = new Settings($db);

$message = '';

// Handle form submission
if ($_POST) {
    $logoImage = '';
    $logoType = $_POST['logo_type'];
    
    // Handle logo image upload
    if ($logoType === 'image' && isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/logos/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileInfo = pathinfo($_FILES['logo_image']['name']);
        $extension = strtolower($fileInfo['extension']);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        
        if (in_array($extension, $allowedExtensions)) {
            $fileName = 'logo_' . time() . '.' . $extension;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $targetPath)) {
                $logoImage = 'uploads/logos/' . $fileName;
                
                // Delete old logo image if exists
                $oldLogoImage = $settings->get('logo_image');
                if ($oldLogoImage && file_exists('../' . $oldLogoImage)) {
                    unlink('../' . $oldLogoImage);
                }
            }
        }
    } else if ($logoType === 'image') {
        // Keep existing image if no new upload
        $logoImage = $settings->get('logo_image', '');
    }
    
    $newSettings = [
        'pageant_name' => trim($_POST['pageant_name']),
        'primary_color' => $_POST['primary_color'],
        'secondary_color' => $_POST['secondary_color'],
        'accent_color' => $_POST['accent_color'],
        'logo_text' => trim($_POST['logo_text']),
        'logo_image' => $logoImage,
        'logo_type' => $logoType,
        'theme_style' => $_POST['theme_style'],
        'background_style' => $_POST['background_style'],
        'card_style' => $_POST['card_style']
    ];
    
    if ($settings->updateMultiple($newSettings)) {
        // Force refresh of cached settings to show changes immediately
        $currentSettings = getPageantSettings(true);
        $message = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Settings updated successfully! Changes are now active across the entire system.</div>';
    } else {
        $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error updating settings.</div>';
    }
}

// Get current settings
$currentSettings = $settings->getAll();

// Predefined color themes
$colorThemes = [
    'Royal Purple' => ['primary' => '#667eea', 'secondary' => '#764ba2', 'accent' => '#ffd700'],
    'Ocean Blue' => ['primary' => '#2196F3', 'secondary' => '#1976D2', 'accent' => '#FF9800'],
    'Rose Gold' => ['primary' => '#E91E63', 'secondary' => '#C2185B', 'accent' => '#FFD700'],
    'Emerald Green' => ['primary' => '#4CAF50', 'secondary' => '#388E3C', 'accent' => '#FFC107'],
    'Sunset Orange' => ['primary' => '#FF5722', 'secondary' => '#D84315', 'accent' => '#FFEB3B'],
    'Midnight Black' => ['primary' => '#212121', 'secondary' => '#424242', 'accent' => '#FF6B6B'],
    'Lavender Dream' => ['primary' => '#9C27B0', 'secondary' => '#7B1FA2', 'accent' => '#E1BEE7'],
    'Coral Reef' => ['primary' => '#FF7043', 'secondary' => '#FF5722', 'accent' => '#4DD0E1']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pageant Settings - <?php echo htmlspecialchars($currentSettings['pageant_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        <?php echo $settings->generateCSS(); ?>
        
        .settings-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-medium);
        }
        
        .color-picker-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .color-preview {
            width: 50px;
            height: 40px;
            border-radius: 8px;
            border: 2px solid #ddd;
            cursor: pointer;
            display: inline-block;
            margin-left: 10px;
            vertical-align: middle;
        }
        
        .theme-preset {
            border: 2px solid #ddd;
            border-radius: 15px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .theme-preset:hover {
            border-color: var(--pageant-primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .theme-preview {
            display: flex;
            gap: 10px;
            margin-bottom: 0.5rem;
        }
        
        .theme-color {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .preview-section {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--pageant-primary);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="../index.php" style="font-size: 1.4rem; font-weight: 700; text-decoration: none;">
                <?php if ($currentSettings['logo_type'] === 'image' && $currentSettings['logo_image']): ?>
                    <img src="../<?php echo htmlspecialchars($currentSettings['logo_image']); ?>" 
                         alt="Logo" style="height: 35px; margin-right: 0.75rem;">
                <?php else: ?>
                    <span style="font-size: 1.8rem; margin-right: 0.75rem;"><?php echo htmlspecialchars($currentSettings['logo_text']); ?></span>
                <?php endif; ?>
                <span style="color: #ffffff; text-shadow: 0 1px 3px rgba(0,0,0,0.3); letter-spacing: 0.5px;">
                    <?php echo htmlspecialchars($currentSettings['pageant_name']); ?>
                </span>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="candidates.php">Candidates</a>
                <a class="nav-link" href="criteria.php">Criteria</a>
                <a class="nav-link" href="judges.php">Judges</a>
                <a class="nav-link" href="results.php">Results</a>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="settings-card p-4">
                    <h3 class="mb-4">
                        <i class="fas fa-cog text-primary"></i>
                        Pageant Customization
                    </h3>
                    
                    <?php echo $message; ?>
                    
                    <form method="POST" id="settingsForm" enctype="multipart/form-data">
                        <!-- Basic Settings -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label for="pageant_name" class="form-label fw-bold">
                                    <i class="fas fa-crown"></i> Pageant Name
                                </label>
                                <input type="text" class="form-control" id="pageant_name" name="pageant_name" 
                                       value="<?php echo htmlspecialchars($currentSettings['pageant_name']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-star"></i> Logo Type
                                </label>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="logo_type" id="logo_type_emoji" 
                                               value="emoji" <?php echo $currentSettings['logo_type'] === 'emoji' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="logo_type_emoji">
                                            Text/Emoji
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="logo_type" id="logo_type_image" 
                                               value="image" <?php echo $currentSettings['logo_type'] === 'image' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="logo_type_image">
                                            Image Upload
                                        </label>
                                    </div>
                                </div>
                                
                                <div id="text_logo_section" style="display: <?php echo $currentSettings['logo_type'] === 'emoji' ? 'block' : 'none'; ?>;">
                                    <label for="logo_text" class="form-label">Logo Text/Emoji</label>
                                    <input type="text" class="form-control" id="logo_text" name="logo_text" 
                                           value="<?php echo htmlspecialchars($currentSettings['logo_text']); ?>" 
                                           placeholder="👑 or text">
                                </div>
                                
                                <div id="image_logo_section" style="display: <?php echo $currentSettings['logo_type'] === 'image' ? 'block' : 'none'; ?>;">
                                    <label for="logo_image" class="form-label">Logo Image</label>
                                    <input type="file" class="form-control" id="logo_image" name="logo_image" 
                                           accept="image/*">
                                    <?php if ($currentSettings['logo_image']): ?>
                                        <div class="mt-2">
                                            <small class="text-muted">Current logo:</small><br>
                                            <img src="../<?php echo htmlspecialchars($currentSettings['logo_image']); ?>" 
                                                 alt="Current Logo" style="max-height: 50px; max-width: 100px;" class="border rounded">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Color Theme -->
                        <h5 class="mb-3"><i class="fas fa-palette"></i> Color Theme</h5>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="primary_color" class="form-label">Primary Color</label>
                                <div class="d-flex align-items-center">
                                    <input type="color" class="form-control form-control-color" id="primary_color" 
                                           name="primary_color" value="<?php echo $currentSettings['primary_color']; ?>">
                                    <span class="color-preview" id="primary_preview" 
                                          style="background-color: <?php echo $currentSettings['primary_color']; ?>"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="secondary_color" class="form-label">Secondary Color</label>
                                <div class="d-flex align-items-center">
                                    <input type="color" class="form-control form-control-color" id="secondary_color" 
                                           name="secondary_color" value="<?php echo $currentSettings['secondary_color']; ?>">
                                    <span class="color-preview" id="secondary_preview" 
                                          style="background-color: <?php echo $currentSettings['secondary_color']; ?>"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="accent_color" class="form-label">Accent Color</label>
                                <div class="d-flex align-items-center">
                                    <input type="color" class="form-control form-control-color" id="accent_color" 
                                           name="accent_color" value="<?php echo $currentSettings['accent_color']; ?>">
                                    <span class="color-preview" id="accent_preview" 
                                          style="background-color: <?php echo $currentSettings['accent_color']; ?>"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Style Options -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="theme_style" class="form-label fw-bold">Theme Style</label>
                                <select class="form-select" id="theme_style" name="theme_style">
                                    <option value="gradient" <?php echo $currentSettings['theme_style'] === 'gradient' ? 'selected' : ''; ?>>Gradient</option>
                                    <option value="solid" <?php echo $currentSettings['theme_style'] === 'solid' ? 'selected' : ''; ?>>Solid Colors</option>
                                    <option value="modern" <?php echo $currentSettings['theme_style'] === 'modern' ? 'selected' : ''; ?>>Modern Flat</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="background_style" class="form-label fw-bold">Background Style</label>
                                <select class="form-select" id="background_style" name="background_style">
                                    <option value="gradient" <?php echo $currentSettings['background_style'] === 'gradient' ? 'selected' : ''; ?>>Animated Gradient</option>
                                    <option value="solid" <?php echo $currentSettings['background_style'] === 'solid' ? 'selected' : ''; ?>>Solid Color</option>
                                    <option value="pattern" <?php echo $currentSettings['background_style'] === 'pattern' ? 'selected' : ''; ?>>Pattern</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="card_style" class="form-label fw-bold">Card Style</label>
                                <select class="form-select" id="card_style" name="card_style">
                                    <option value="glassmorphism" <?php echo $currentSettings['card_style'] === 'glassmorphism' ? 'selected' : ''; ?>>Glassmorphism</option>
                                    <option value="shadow" <?php echo $currentSettings['card_style'] === 'shadow' ? 'selected' : ''; ?>>Shadow Cards</option>
                                    <option value="flat" <?php echo $currentSettings['card_style'] === 'flat' ? 'selected' : ''; ?>>Flat Design</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-lg px-5 ms-3" onclick="previewChanges()">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <a href="../test_settings_propagation.php" class="btn btn-outline-info btn-lg px-5 ms-3" target="_blank">
                                <i class="fas fa-vial"></i> Test Propagation
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preset Themes -->
            <div class="col-md-4">
                <div class="settings-card p-4">
                    <h5 class="mb-3"><i class="fas fa-swatchbook"></i> Preset Themes</h5>
                    <?php foreach ($colorThemes as $themeName => $colors): ?>
                        <div class="theme-preset" onclick="applyTheme('<?php echo $colors['primary']; ?>', '<?php echo $colors['secondary']; ?>', '<?php echo $colors['accent']; ?>')">
                            <div class="theme-preview">
                                <div class="theme-color" style="background-color: <?php echo $colors['primary']; ?>"></div>
                                <div class="theme-color" style="background-color: <?php echo $colors['secondary']; ?>"></div>
                                <div class="theme-color" style="background-color: <?php echo $colors['accent']; ?>"></div>
                            </div>
                            <strong><?php echo $themeName; ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Live Preview -->
                <div class="settings-card p-4 mt-4">
                    <h5 class="mb-3"><i class="fas fa-desktop"></i> Live Preview</h5>
                    <div id="livePreview" class="preview-section">
                        <div class="text-center">
                            <h4 id="previewTitle"><?php echo htmlspecialchars($currentSettings['pageant_name']); ?></h4>
                            <div class="btn btn-primary mb-2">Primary Button</div>
                            <div class="badge bg-primary">Primary Badge</div>
                            <div class="mt-2" id="previewLogoContainer">
                                <?php if ($currentSettings['logo_type'] === 'image' && $currentSettings['logo_image']): ?>
                                    <img id="previewLogoImage" src="../<?php echo htmlspecialchars($currentSettings['logo_image']); ?>" 
                                         alt="Logo" style="max-height: 50px; max-width: 100px;">
                                <?php else: ?>
                                    <span id="previewLogoText" style="font-size: 2rem;"><?php echo htmlspecialchars($currentSettings['logo_text']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Real-time preview updates
        document.getElementById('pageant_name').addEventListener('input', function() {
            document.getElementById('previewTitle').textContent = this.value;
        });

        document.getElementById('logo_text').addEventListener('input', function() {
            if (document.getElementById('logo_type_emoji').checked) {
                const previewText = document.getElementById('previewLogoText');
                if (previewText) previewText.textContent = this.value;
            }
        });

        // Logo type switching
        document.querySelectorAll('input[name="logo_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const textSection = document.getElementById('text_logo_section');
                const imageSection = document.getElementById('image_logo_section');
                const previewContainer = document.getElementById('previewLogoContainer');
                
                if (this.value === 'emoji') {
                    textSection.style.display = 'block';
                    imageSection.style.display = 'none';
                    previewContainer.innerHTML = '<span id="previewLogoText" style="font-size: 2rem;">' + document.getElementById('logo_text').value + '</span>';
                } else {
                    textSection.style.display = 'none';
                    imageSection.style.display = 'block';
                    previewContainer.innerHTML = '<span style="color: #666;">Upload an image to preview</span>';
                }
            });
        });

        // Image upload preview
        document.getElementById('logo_image').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewContainer = document.getElementById('previewLogoContainer');
                    previewContainer.innerHTML = '<img id="previewLogoImage" src="' + e.target.result + '" alt="Logo Preview" style="max-height: 50px; max-width: 100px;">';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Color picker updates
        ['primary_color', 'secondary_color', 'accent_color'].forEach(colorId => {
            document.getElementById(colorId).addEventListener('change', function() {
                document.getElementById(colorId.replace('_color', '_preview')).style.backgroundColor = this.value;
                updateLivePreview();
            });
        });

        function applyTheme(primary, secondary, accent) {
            document.getElementById('primary_color').value = primary;
            document.getElementById('secondary_color').value = secondary;
            document.getElementById('accent_color').value = accent;
            
            document.getElementById('primary_preview').style.backgroundColor = primary;
            document.getElementById('secondary_preview').style.backgroundColor = secondary;
            document.getElementById('accent_preview').style.backgroundColor = accent;
            
            updateLivePreview();
        }

        function updateLivePreview() {
            const primary = document.getElementById('primary_color').value;
            const secondary = document.getElementById('secondary_color').value;
            
            const previewElements = document.querySelectorAll('#livePreview .btn-primary, #livePreview .bg-primary');
            previewElements.forEach(el => {
                el.style.background = `linear-gradient(135deg, ${primary} 0%, ${secondary} 100%)`;
            });
        }

        function previewChanges() {
            // Apply changes temporarily for preview
            const root = document.documentElement;
            root.style.setProperty('--pageant-primary', document.getElementById('primary_color').value);
            root.style.setProperty('--pageant-secondary', document.getElementById('secondary_color').value);
            root.style.setProperty('--pageant-accent', document.getElementById('accent_color').value);
            
            // Update navbar brand
            const navbarBrand = document.querySelector('.navbar-brand');
            navbarBrand.innerHTML = `<span style="font-size: 1.5rem;">${document.getElementById('logo_text').value}</span> ${document.getElementById('pageant_name').value}`;
            
            alert('Preview applied! The changes are temporary. Save to make them permanent.');
        }

        // Form submission with loading state
        document.getElementById('settingsForm').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
            
            // Set flag for other tabs to know settings were updated
            sessionStorage.setItem('settingsUpdated', Date.now());
        });
    </script>
</body>
</html>
