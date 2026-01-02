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
        $stmt = $db->prepare("INSERT INTO pageants (name, primary_color, secondary_color, accent_color, logo_image) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$pageant_name, $primary_color, $secondary_color, $accent_color, $logo_image])) {
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pageant Events</title>
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
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Manage Pageant Events</h1>

        <?php echo $message; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Add New Pageant Event</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="pageant_name" class="form-label">Pageant Name</label>
                        <input type="text" class="form-control" id="pageant_name" name="pageant_name" value="<?php echo htmlspecialchars($pageant_name); ?>" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="primary_color" class="form-label">Primary Color</label>
                            <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" value="#667eea">
                        </div>
                        <div class="col-md-4">
                            <label for="secondary_color" class="form-label">Secondary Color</label>
                            <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color" value="#764ba2">
                        </div>
                        <div class="col-md-4">
                            <label for="accent_color" class="form-label">Accent Color</label>
                            <input type="color" class="form-control form-control-color" id="accent_color" name="accent_color" value="#ffd700">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="logo_image" class="form-label">Logo Image</label>
                        <input type="file" class="form-control" id="logo_image" name="logo_image" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Pageant</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Existing Pageant Events</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Colors</th>
                            <th>Logo</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pageants as $pageant): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pageant['name']); ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo $pageant['primary_color']; ?>;">Primary</span>
                                    <span class="badge" style="background-color: <?php echo $pageant['secondary_color']; ?>;">Secondary</span>
                                    <span class="badge" style="background-color: <?php echo $pageant['accent_color']; ?>;">Accent</span>
                                </td>
                                <td>
                                    <?php if ($pageant['logo_image']): ?>
                                        <img src="../<?php echo htmlspecialchars($pageant['logo_image']); ?>" alt="Logo" style="max-height: 40px;">
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_pageant.php?id=<?php echo $pageant['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="candidates.php?pageant_id=<?php echo $pageant['id']; ?>" class="btn btn-sm btn-info">Candidates</a>
                                    <a href="criteria.php?pageant_id=<?php echo $pageant['id']; ?>" class="btn btn-sm btn-secondary">Criteria</a>
                                    <a href="rounds.php?pageant_id=<?php echo $pageant['id']; ?>" class="btn btn-sm btn-warning">Segments</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
