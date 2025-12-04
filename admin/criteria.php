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

// Determine current round
$roundId = null;
if (isset($_GET['round_id'])) {
    $roundId = (int) $_GET['round_id'];
} elseif (isset($_POST['round_id'])) {
    $roundId = (int) $_POST['round_id'];
}

if (!$roundId) {
    header('Location: rounds.php');
    exit();
}

// Get round/segment details
$roundQuery = "SELECT * FROM segments WHERE id = ?";
$roundStmt = $db->prepare($roundQuery);
$roundStmt->execute([$roundId]);
$round = $roundStmt->fetch(PDO::FETCH_ASSOC);

if (!$round) {
    header('Location: rounds.php');
    exit();
}

$message = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'];
                $percentage = $_POST['percentage'];
                $description = $_POST['description'];
                $roundId = (int) $_POST['round_id'];
                
                // Check if total percentage would exceed 100% for this round
                $query = "SELECT SUM(percentage) as total FROM criteria WHERE round_id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$roundId]);
                $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                
                if ($total + $percentage > 100) {
                    $message = '<div class="alert alert-danger">Total percentage for this round cannot exceed 100%. Current total: ' . $total . '%</div>';
                } else {
                    $query = "INSERT INTO criteria (round_id, name, percentage, description) VALUES (?, ?, ?, ?)";
                    $stmt = $db->prepare($query);
                    if ($stmt->execute([$roundId, $name, $percentage, $description])) {
                        $message = '<div class="alert alert-success">Criteria added successfully!</div>';
                    } else {
                        $message = '<div class="alert alert-danger">Error adding criteria.</div>';
                    }
                }
                break;
                
            case 'update':
                $id = $_POST['id'];
                $name = $_POST['name'];
                $percentage = $_POST['percentage'];
                $description = $_POST['description'];
                $roundId = (int) $_POST['round_id'];
                
                // Check if total percentage would exceed 100% for this round (excluding current criteria)
                $query = "SELECT SUM(percentage) as total FROM criteria WHERE id != ? AND round_id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$id, $roundId]);
                $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                
                if ($total + $percentage > 100) {
                    $message = '<div class="alert alert-danger">Total percentage for this round cannot exceed 100%. Current total (excluding this): ' . $total . '%</div>';
                } else {
                    $query = "UPDATE criteria SET name = ?, percentage = ?, description = ? WHERE id = ?";
                    $stmt = $db->prepare($query);
                    if ($stmt->execute([$name, $percentage, $description, $id])) {
                        $message = '<div class="alert alert-success">Criteria updated successfully!</div>';
                    } else {
                        $message = '<div class="alert alert-danger">Error updating criteria.</div>';
                    }
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $query = "DELETE FROM criteria WHERE id = ?";
                $stmt = $db->prepare($query);
                if ($stmt->execute([$id])) {
                    $message = '<div class="alert alert-success">Criteria deleted successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error deleting criteria.</div>';
                }
                break;
        }
    }
}

// Get all criteria for this round
$query = "SELECT * FROM criteria WHERE round_id = ? ORDER BY percentage DESC";
$stmt = $db->prepare($query);
$stmt->execute([$roundId]);
$criteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total percentage for this round
$totalPercentage = array_sum(array_column($criteria, 'percentage'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Criteria - Pageantry System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        <?php echo $settings->generateCSS(); ?>
        .criteria-page-container {
            max-width: 1200px;
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
                <a class="nav-link" href="../index.php">Dashboard</a>
                <a class="nav-link" href="candidates.php">Candidates</a>
                <a class="nav-link" href="judges.php">Judges</a>
                <a class="nav-link" href="results.php">Results</a>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 criteria-page-container">
        <h3 class="text-white mb-3">
            <i class="fas fa-layer-group"></i>
            Criteria for Round: <?php echo htmlspecialchars($round['name']); ?>
        </h3>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-plus"></i> Add New Criteria</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="criteriaForm">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="round_id" value="<?php echo $roundId; ?>">
                            <div class="mb-3">
                                <label for="name" class="form-label">Criteria Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="percentage" class="form-label">Percentage (%)</label>
                                <input type="number" class="form-control" id="percentage" name="percentage" 
                                       min="0" max="100" step="0.01" required>
                                <div class="form-text">
                                    Current total: <?php echo $totalPercentage; ?>%
                                    (Remaining: <?php echo 100 - $totalPercentage; ?>%)
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Add Criteria
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header bg-info text-white">
                        <h6><i class="fas fa-info-circle"></i> Percentage Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-2">
                            <div class="progress-bar" role="progressbar" style="width: <?php echo $totalPercentage; ?>%">
                                <?php echo $totalPercentage; ?>%
                            </div>
                        </div>
                        <small class="text-muted">
                            Total: <?php echo $totalPercentage; ?>% / 100%
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-list-check"></i> Current Criteria</h5>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <?php if (empty($criteria)): ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                <p>No criteria added yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Percentage</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($criteria as $criterion): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($criterion['name']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $criterion['percentage']; ?>%</span>
                                                </td>
                                                <td><?php echo htmlspecialchars(substr($criterion['description'], 0, 50)) . '...'; ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" onclick="editCriteria(<?php echo htmlspecialchars(json_encode($criterion)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this criteria?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $criterion['id']; ?>">
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
                    <h5 class="modal-title">Edit Criteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        <input type="hidden" name="round_id" id="editRoundId" value="<?php echo $roundId; ?>">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Criteria Name</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPercentage" class="form-label">Percentage (%)</label>
                            <input type="number" class="form-control" id="editPercentage" name="percentage" 
                                   min="0" max="100" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Criteria</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCriteria(criteria) {
            document.getElementById('editId').value = criteria.id;
            document.getElementById('editName').value = criteria.name;
            document.getElementById('editPercentage').value = criteria.percentage;
            document.getElementById('editDescription').value = criteria.description;
            
            var editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
        }
    </script>
</body>
</html>
