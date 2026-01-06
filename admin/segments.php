<?php
session_start();
require_once '../config/database.php';
require_once '../config/settings.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$settings = new Settings($db);
$currentSettings = $settings->getAll();

$message = '';

if (!isset($_GET['pageant_id'])) {
    die('Pageant ID is required.');
}
$pageant_id = $_GET['pageant_id'];

// Fetch pageant details
$pageantStmt = $db->prepare("SELECT * FROM pageants WHERE id = ?");
$pageantStmt->execute([$pageant_id]);
$pageant = $pageantStmt->fetch(PDO::FETCH_ASSOC);

if (!$pageant) {
    die('Pageant not found.');
}

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'];
                $description = $_POST['description'];
                $pageant_id_post = $_POST['pageant_id'];

                $query = 'INSERT INTO segments (name, description, pageant_id) VALUES (?, ?, ?)';
                $stmt = $db->prepare($query);
                if ($stmt->execute([$name, $description, $pageant_id_post])) {
                    $message = '<div class="alert alert-success">Segment added successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error adding segment.</div>';
                }
                break;

            case 'update':
                $id = $_POST['id'];
                $name = $_POST['name'];
                $description = $_POST['description'];

                $query = 'UPDATE segments SET name = ?, description = ? WHERE id = ?';
                $stmt = $db->prepare($query);
                if ($stmt->execute([$name, $description, $id])) {
                    $message = '<div class="alert alert-success">Segment updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error updating segment.</div>';
                }
                break;

            case 'delete':
                $id = $_POST['id'];
                $query = 'DELETE FROM segments WHERE id = ?';
                $stmt = $db->prepare($query);
                if ($stmt->execute([$id])) {
                    $message = '<div class="alert alert-success">Segment deleted successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error deleting segment.</div>';
                }
                break;
        }
    }
}

// Verify 'segments' table exists before querying
try {
    $db->query("SELECT 1 FROM segments LIMIT 1");
} catch (PDOException $e) {
    die("<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px;'>"
        . "<strong>Fatal Error:</strong> The 'segments' table is missing from the database.<br>"
        . "Please run the database setup script to create it.<br><br>"
        . "<a href='../setup_database.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Setup Script</a>"
        . "</div>");
}

// Get all segments for the current pageant
$query = 'SELECT * FROM segments WHERE pageant_id = ? ORDER BY id ASC';
$stmt = $db->prepare($query);
$stmt->execute([$pageant_id]);
$segments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Segments - Pageantry System</title>
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
                <a class="nav-link" href="../index.php">Dashboard</a>
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
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-plus"></i> Add New Segment</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="segmentForm">
                                                        <input type="hidden" name="action" value="add">
                            <input type="hidden" name="pageant_id" value="<?php echo $pageant_id; ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Segment Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Add Segment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-layer-group"></i> Current Segments</h5>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <?php if (empty($segments)): ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                <p>No segments added yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Criteria</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($segments as $segment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($segment['name']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($segment['description'], 0, 50)) . '...'; ?></td>
                                                <td>
                                                    <a href="criteria.php?segment_id=<?php echo $segment['id']; ?>" class="btn btn-info btn-sm">
                                                        <i class="fas fa-list-check"></i> Setup Criteria
                                                    </a>
                                                </td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" onclick='editSegment(<?php echo json_encode($segment); ?>)'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this segment?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $segment['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Segment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Segment Name</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Segment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editSegment(segment) {
            document.getElementById('editId').value = segment.id;
            document.getElementById('editName').value = segment.name;
            document.getElementById('editDescription').value = segment.description;
            
            var editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }
    </script>
</body>
</html>
