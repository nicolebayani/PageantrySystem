<?php
session_start();
require_once '../config/database.php';
require_once '../config/settings.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$pageant_name = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pageant_name = trim($_POST['pageant_name']);
    $gender_type = $_POST['gender_type'] ?? 'female';
    $primary_color = $_POST['primary_color'];
    $secondary_color = $_POST['secondary_color'];
    $accent_color = $_POST['accent_color'];
    $logo_image = '';

    if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = 'logo_' . time() . '_' . basename($_FILES['logo_image']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $targetPath)) {
            $logo_image = 'uploads/logos/' . $fileName;
        }
    }

        if (!empty($pageant_name)) {
        $stmt = $db->prepare("INSERT INTO pageants (name, gender_type, primary_color, secondary_color, accent_color, logo_image) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$pageant_name, $gender_type, $primary_color, $secondary_color, $accent_color, $logo_image])) {
            $message = '<div class="alert alert-success">Pageant event created successfully!</div>';
            $pageant_name = '';
        } else {
            $message = '<div class="alert alert-danger">Failed to create pageant event.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Pageant name is required.</div>';
    }
}

$stmt = $db->prepare("SELECT * FROM pageants ORDER BY created_at DESC");
$stmt->execute();
$pageants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$settings = new Settings($db);
$currentSettings = $settings->getAll();

$pageTitle = 'Manage Pageant Events';
$homeUrl = 'dashboard.php';
$navContent = <<<HTML
<ul class="navbar-nav ms-auto">
    <li class="nav-item"><a class="nav-link" href="dashboard.php">👑 Admin Dashboard</a></li>
    <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
    <li class="nav-item"><a class="nav-link" href="../auth/logout.php">Logout</a></li>
</ul>
HTML;

include_once __DIR__ . '/../includes/header.php';
?>

    <div class="container mt-4">
        <div class="page-header">
            <h1 class="page-title">Manage Pageant Events</h1>
        </div>

        <?php echo $message; ?>

        <div class="row">
            <div class="col-lg-4">
                <div class="card form-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Pageant</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group mb-3">
                                <label for="pageant_name">Pageant Name</label>
                                <input type="text" class="form-control" id="pageant_name" name="pageant_name" value="<?php echo htmlspecialchars($pageant_name); ?>" required>
                            </div>
                            <div class="form-group mb-3">
                                <label>Gender Type</label>
                                <div class="gender-options">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender_type" id="gender_female" value="female" checked>
                                        <label class="form-check-label" for="gender_female"><i class="fas fa-venus"></i> Female</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender_type" id="gender_male" value="male">
                                        <label class="form-check-label" for="gender_male"><i class="fas fa-mars"></i> Male</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender_type" id="gender_both" value="both">
                                        <label class="form-check-label" for="gender_both"><i class="fas fa-venus-mars"></i> Both</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label>Theme Colors</label>
                                <div class="color-picker-group">
                                    <input type="color" class="form-control-color" id="primary_color" name="primary_color" value="#667eea" title="Primary Color">
                                    <input type="color" class="form-control-color" id="secondary_color" name="secondary_color" value="#764ba2" title="Secondary Color">
                                    <input type="color" class="form-control-color" id="accent_color" name="accent_color" value="#ffd700" title="Accent Color">
                                </div>
                            </div>
                            <div class="form-group mb-4">
                                <label for="logo_image">Logo Image</label>
                                <input type="file" class="form-control" id="logo_image" name="logo_image" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create Pageant</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card list-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i>Existing Pageants</h5>
                    </div>
                    <div class="card-body">
                        <div class="pageant-grid">
                            <?php foreach ($pageants as $pageant): ?>
                                <div class="pageant-card">
                                    <div class="pageant-card-header" style="background: linear-gradient(135deg, <?php echo $pageant['primary_color']; ?>, <?php echo $pageant['secondary_color']; ?>);">
                                        <?php if ($pageant['logo_image']): ?>
                                            <img src="../<?php echo htmlspecialchars($pageant['logo_image']); ?>" alt="Logo" class="pageant-card-logo">
                                        <?php endif; ?>
                                    </div>
                                    <div class="pageant-card-body">
                                        <h5 class="pageant-card-title"><?php echo htmlspecialchars($pageant['name']); ?></h5>
                                        <p class="pageant-card-gender"><i class="fas fa-venus-mars me-2"></i><?php echo ucfirst(htmlspecialchars($pageant['gender_type'])); ?></p>
                                        <div class="color-swatches mb-3">
                                            <div class="color-swatch" style="background-color: <?php echo $pageant['primary_color']; ?>;" title="Primary"></div>
                                            <div class="color-swatch" style="background-color: <?php echo $pageant['secondary_color']; ?>;" title="Secondary"></div>
                                            <div class="color-swatch" style="background-color: <?php echo $pageant['accent_color']; ?>;" title="Accent"></div>
                                        </div>
                                    </div>
                                    <div class="pageant-card-footer">
                                        <a href="edit_pageant.php?id=<?php echo $pageant['id']; ?>" class="btn btn-sm btn-outline-light">Edit</a>
                                        <a href="dashboard.php?pageant_id=<?php echo $pageant['id']; ?>" class="btn btn-sm btn-primary">Manage</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
