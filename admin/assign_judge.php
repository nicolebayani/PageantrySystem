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
$settings = new Settings($db);
$currentSettings = $settings->getAll();

$message = '';
if (!isset($_GET['id'])) {
    $_SESSION['message'] = '<div class="alert alert-danger">Error: Judge ID not specified.</div>';
    header('Location: judges.php');
    exit();
}
$judge_id = $_GET['id'];

// Fetch judge details
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'judge'");
$stmt->execute([$judge_id]);
$judge = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$judge) {
    die('Judge not found.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['assign_pageant'])) {
        $pageant_id = $_POST['pageant_id'];
        
        // Check for existing assignment
        $stmt = $db->prepare("SELECT id FROM judge_assignments WHERE judge_id = ?");
        $stmt->execute([$judge_id]);
        $existing_assignment = $stmt->fetch();

        if ($existing_assignment) {
            // Update existing assignment
            $stmt = $db->prepare("UPDATE judge_assignments SET pageant_id = ? WHERE judge_id = ?");
            $stmt->execute([$pageant_id, $judge_id]);
        } else {
            // Insert new assignment
            $stmt = $db->prepare("INSERT INTO judge_assignments (judge_id, pageant_id) VALUES (?, ?)");
            $stmt->execute([$judge_id, $pageant_id]);
        }
        $message = '<div class="alert alert-success">Judge assigned successfully!</div>';
    } elseif (isset($_POST['remove_assignment'])) {
        $stmt = $db->prepare("DELETE FROM judge_assignments WHERE judge_id = ?");
        $stmt->execute([$judge_id]);
        $message = '<div class="alert alert-success">Judge assignment removed successfully!</div>';
    }
}

// Fetch all pageants
$stmt = $db->prepare("SELECT * FROM pageants ORDER BY name");
$stmt->execute();
$pageants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch current assignment
$stmt = $db->prepare("SELECT pageant_id FROM judge_assignments WHERE judge_id = ?");
$stmt->execute([$judge_id]);
$current_assignment = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Judge to Pageant</title>
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
                <a class="nav-link" href="judges.php">Judges</a>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Assign Judge to Pageant</h1>
        
        <?php echo $message; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Assign <?php echo htmlspecialchars($judge['full_name']); ?></h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="pageant_id" class="form-label">Select Pageant</label>
                        <select class="form-select" id="pageant_id" name="pageant_id">
                            <?php foreach ($pageants as $pageant): ?>
                                <option value="<?php echo $pageant['id']; ?>" <?php echo ($current_assignment && $current_assignment['pageant_id'] == $pageant['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($pageant['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="assign_pageant" class="btn btn-primary">Assign Pageant</button>
                    <?php if ($current_assignment): ?>
                        <button type="submit" name="remove_assignment" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this assignment?')">Remove Assignment</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
