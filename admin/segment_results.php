<?php
session_start();
require_once '../config/database.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get all segments
$segmentsStmt = $db->prepare("SELECT id, name, description FROM segments ORDER BY name");
$segmentsStmt->execute();
$segments = $segmentsStmt->fetchAll(PDO::FETCH_ASSOC);

$selectedSegmentId = isset($_GET['segment_id']) ? (int)$_GET['segment_id'] : (count($segments) ? (int)$segments[0]['id'] : 0);
$segmentResults = [];
$candidateDetails = [];
$segmentName = '';

if ($selectedSegmentId) {
    // Get selected segment name
    foreach ($segments as $seg) {
        if ((int)$seg['id'] === $selectedSegmentId) {
            $segmentName = $seg['name'];
            break;
        }
    }

    // Overall per-candidate results for this segment
    $resultsQuery = "
        SELECT 
            c.id,
            c.name,
            c.age,
            c.description,
            SUM(s.score * cr.percentage / 100) as weighted_score,
            COUNT(DISTINCT s.judge_id) as judge_count
        FROM candidates c
        JOIN scores s ON c.id = s.candidate_id
        JOIN criteria cr ON s.criteria_id = cr.id
        WHERE cr.round_id = :segment_id
        GROUP BY c.id, c.name, c.age, c.description
        HAVING judge_count > 0
        ORDER BY weighted_score DESC
    ";

    $resultsStmt = $db->prepare($resultsQuery);
    $resultsStmt->execute(['segment_id' => $selectedSegmentId]);
    $segmentResults = $resultsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Detailed scores per criteria within this segment
    $detailedQuery = "
        SELECT 
            c.name as candidate_name,
            cr.name as criteria_name,
            cr.percentage,
            AVG(s.score) as avg_score,
            COUNT(s.score) as judge_count
        FROM candidates c
        JOIN scores s ON c.id = s.candidate_id
        JOIN criteria cr ON s.criteria_id = cr.id
        WHERE cr.round_id = :segment_id
        GROUP BY c.id, c.name, cr.id, cr.name, cr.percentage
        ORDER BY c.name, cr.percentage DESC
    ";

    $detailedStmt = $db->prepare($detailedQuery);
    $detailedStmt->execute(['segment_id' => $selectedSegmentId]);
    $detailedScores = $detailedStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($detailedScores as $score) {
        $candidateDetails[$score['candidate_name']][] = $score;
    }

    // Total number of judges
    $judgeCountQuery = "SELECT COUNT(*) as total_judges FROM users WHERE role = 'judge'";
    $judgeCountStmt = $db->prepare($judgeCountQuery);
    $judgeCountStmt->execute();
    $totalJudges = $judgeCountStmt->fetch(PDO::FETCH_ASSOC)['total_judges'];
} else {
    $totalJudges = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Segment Results - Pageantry System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary no-print">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-crown me-2"></i>Pageantry System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">Dashboard</a>
                <a class="nav-link" href="candidates.php">Candidates</a>
                <a class="nav-link" href="criteria.php">Criteria</a>
                <a class="nav-link" href="judges.php">Judges</a>
                <a class="nav-link" href="results.php">Overall Results</a>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h2 class="mb-0"><i class="fas fa-layer-group"></i> Segment Results</h2>
            <button class="btn btn-outline-light" onclick="window.print()">
                <i class="fas fa-print"></i> Print This Segment
            </button>
        </div>

        <div class="card mb-4 no-print">
            <div class="card-body">
                <form method="get" class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Select Segment</label>
                        <select name="segment_id" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($segments as $segment): ?>
                                <option value="<?php echo $segment['id']; ?>" <?php echo $segment['id'] == $selectedSegmentId ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($segment['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!$selectedSegmentId): ?>
            <div class="text-center text-light py-5">
                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                <h4>No segments found.</h4>
                <p>Please create segments and assign criteria to them.</p>
            </div>
        <?php elseif (empty($segmentResults)): ?>
            <div class="text-center text-light py-5">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h4>No results for this segment yet.</h4>
                <p>Judges need to submit scores for criteria under this segment.</p>
            </div>
        <?php else: ?>
            <div class="text-center mb-4">
                <h3 class="text-light mb-1">Segment: <?php echo htmlspecialchars($segmentName); ?></h3>
                <p class="text-light-50 mb-0">Per-candidate rankings and detailed breakdown for this segment.</p>
            </div>

            <div class="row">
                <?php foreach ($segmentResults as $index => $result): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header text-center">
                                <h5>
                                    <?php echo ($index + 1); ?><?php echo ($index + 1) == 1 ? 'st' : (($index + 1) == 2 ? 'nd' : (($index + 1) == 3 ? 'rd' : 'th')); ?> Place
                                </h5>
                            </div>
                            <div class="card-body text-center">
                                <h4><?php echo htmlspecialchars($result['name']); ?></h4>
                                <?php if ($result['age']): ?>
                                    <p class="text-muted">Age: <?php echo $result['age']; ?></p>
                                <?php endif; ?>
                                <h2 class="text-primary">
                                    <?php echo number_format($result['weighted_score'], 2); ?>
                                </h2>
                                <p class="text-muted mb-0">Segment Score</p>
                                <small class="text-muted">
                                    Scored by <?php echo $result['judge_count']; ?> of <?php echo $totalJudges; ?> judges
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5><i class="fas fa-chart-bar"></i> Detailed Scores Breakdown (<?php echo htmlspecialchars($segmentName); ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($candidateDetails as $candidateName => $scores): ?>
                            <div class="col-md-6 mb-4">
                                <h6 class="fw-bold"><?php echo htmlspecialchars($candidateName); ?></h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Criteria</th>
                                                <th>Weight</th>
                                                <th>Avg Score</th>
                                                <th>Weighted</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($scores as $score): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($score['criteria_name']); ?></td>
                                                    <td><?php echo $score['percentage']; ?>%</td>
                                                    <td><?php echo number_format($score['avg_score'], 2); ?></td>
                                                    <td><?php echo number_format($score['avg_score'] * $score['percentage'] / 100, 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
