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

// Fetch all segments with their criteria
$query = "
    SELECT s.id as round_id, s.name as round_name, s.description as round_description,
           c.id as criteria_id, c.name as criteria_name, c.percentage, c.description as criteria_description
    FROM segments s
    LEFT JOIN criteria c ON c.round_id = s.id
    ORDER BY s.id ASC, c.percentage DESC, c.name ASC
";

$stmt = $db->prepare($query);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rounds = [];
foreach ($rows as $row) {
    $rid = $row['round_id'];
    if (!isset($rounds[$rid])) {
        $rounds[$rid] = [
            'id' => $rid,
            'name' => $row['round_name'],
            'description' => $row['round_description'],
            'criteria' => []
        ];
    }
    if (!empty($row['criteria_id'])) {
        $rounds[$rid]['criteria'][] = [
            'id' => $row['criteria_id'],
            'name' => $row['criteria_name'],
            'percentage' => $row['percentage'],
            'description' => $row['criteria_description']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Criteria by Rounds - Pageantry System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        <?php echo $settings->generateCSS(); ?>
        .criteria-overview-container {
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
                    <span style="font-size: 1.8rem; margin-right: 0.75rem;">&<?php echo htmlspecialchars($currentSettings['logo_text']); ?></span>
                <?php endif; ?>
                <span style="color: #ffffff; text-shadow: 0 1px 3px rgba(0,0,0,0.3); letter-spacing: 0.5px;">
                    <?php echo htmlspecialchars($currentSettings['pageant_name']); ?>
                </span>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">Dashboard</a>
                <a class="nav-link" href="candidates.php">Candidates</a>
                <a class="nav-link" href="rounds.php">Rounds</a>
                <a class="nav-link" href="judges.php">Judges</a>
                <a class="nav-link" href="results.php">Results</a>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 criteria-overview-container">
        <h3 class="text-white mb-3">
            <i class="fas fa-eye"></i>
            View Criteria by Rounds
        </h3>

        <?php if (empty($rounds)): ?>
            <div class="card">
                <div class="card-body text-center text-muted">
                    <i class="fas fa-layer-group fa-3x mb-3"></i>
                    <p>No rounds or criteria found.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($rounds as $round): ?>
                <div class="card mb-3">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-layer-group"></i>
                            <strong><?php echo htmlspecialchars($round['name']); ?></strong>
                        </div>
                        <a href="criteria.php?round_id=<?php echo $round['id']; ?>" class="btn btn-light btn-sm">
                            <i class="fas fa-pen"></i> Manage Criteria
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($round['description'])): ?>
                            <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($round['description'])); ?></p>
                        <?php endif; ?>

                        <?php if (empty($round['criteria'])): ?>
                            <p class="text-muted mb-0">No criteria added for this round yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th style="width: 120px;">Percentage</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($round['criteria'] as $criterion): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($criterion['name']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $criterion['percentage']; ?>%</span>
                                                </td>
                                                <td><?php echo htmlspecialchars($criterion['description']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
