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
$message = '';
$pageant = null;

if (!isset($_GET['id'])) {
    header('Location: pageants.php');
    exit();
}

$pageant_id = $_GET['id'];

// Fetch pageant details
$stmt = $db->prepare("SELECT * FROM pageants WHERE id = ?");
$stmt->execute([$pageant_id]);
$pageant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pageant) {
    header('Location: pageants.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pageant_name = trim($_POST['pageant_name']);
    $primary_color = $_POST['primary_color'];
    $secondary_color = $_POST['secondary_color'];
    $accent_color = $_POST['accent_color'];
    $logo_image = $pageant['logo_image'];

    if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = 'logo_' . time() . '_' . basename($_FILES['logo_image']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $targetPath)) {
            // Delete old logo if it exists
            if ($logo_image && file_exists('../' . $logo_image)) {
                unlink('../' . $logo_image);
            }
            $logo_image = 'uploads/logos/' . $fileName;
        }
    }

    if (!empty($pageant_name)) {
        $stmt = $db->prepare("UPDATE pageants SET name = ?, primary_color = ?, secondary_color = ?, accent_color = ?, logo_image = ? WHERE id = ?");
        if ($stmt->execute([$pageant_name, $primary_color, $secondary_color, $accent_color, $logo_image, $pageant_id])) {
            $message = '<div class="alert alert-success">Pageant event updated successfully!</div>';
            // Refresh pageant data
            $refreshStmt = $db->prepare("SELECT * FROM pageants WHERE id = ?");
            $refreshStmt->execute([$pageant_id]);
            $pageant = $refreshStmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = '<div class="alert alert-danger">Failed to update pageant event.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Pageant name is required.</div>';
    }
}

$settings = new Settings($db);
$currentSettings = $settings->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pageant Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        <?php echo $settings->generateCSS(); ?>
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Dashboard</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="pageants.php">Back to Pageants</a>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Edit Pageant Event</h1>

        <?php echo $message; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editing '<?php echo htmlspecialchars($pageant['name']); ?>'</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="pageant_name" class="form-label">Pageant Name</label>
                        <input type="text" class="form-control" id="pageant_name" name="pageant_name" value="<?php echo htmlspecialchars($pageant['name']); ?>" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="primary_color" class="form-label">Primary Color</label>
                            <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" value="<?php echo htmlspecialchars($pageant['primary_color']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="secondary_color" class="form-label">Secondary Color</label>
                            <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color" value="<?php echo htmlspecialchars($pageant['secondary_color']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="accent_color" class="form-label">Accent Color</label>
                            <input type="color" class="form-control form-control-color" id="accent_color" name="accent_color" value="<?php echo htmlspecialchars($pageant['accent_color']); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="logo_image" class="form-label">Logo Image</label>
                        <input type="file" class="form-control" id="logo_image" name="logo_image" accept="image/*">
                        <?php if ($pageant['logo_image']): ?>
                            <div class="mt-2">
                                <small>Current logo:</small><br>
                                <img src="../<?php echo htmlspecialchars($pageant['logo_image']); ?>" alt="Current Logo" style="max-height: 60px;" class="border rounded p-1">
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
