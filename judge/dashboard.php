<?php
session_start();
require_once '../config/database.php';

// Check if user is judge
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'judge') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$judge_id = $_SESSION['user_id'];

// Get judge's assigned pageant
$assignmentQuery = "SELECT pageant_id FROM judge_assignments WHERE judge_id = ?";
$assignmentStmt = $db->prepare($assignmentQuery);
$assignmentStmt->execute([$judge_id]);
$assignment = $assignmentStmt->fetch(PDO::FETCH_ASSOC);

if (!$assignment) {
    // Handle case where judge is not assigned to any pageant
    // You can display a message and exit or redirect
    die("You are not assigned to any pageant. Please contact the administrator.");
}

$pageant_id = $assignment['pageant_id'];
$_SESSION['pageant_id'] = $pageant_id;

// Get Pageant Details
$pageantQuery = "SELECT name, theme FROM pageants WHERE id = ?";
$pageantStmt = $db->prepare($pageantQuery);
$pageantStmt->execute([$pageant_id]);
$pageant = $pageantStmt->fetch(PDO::FETCH_ASSOC);

if (!$pageant) {
    // Handle case where pageant details are not found
    die("Could not retrieve pageant details. Please contact the administrator.");
}

// Get judge's scoring progress
$progressQuery = "
    SELECT 
        c.id,
        c.name,
        c.age,
        c.gender,
        COUNT(s.id) as scores_given,
        (SELECT COUNT(cr.id) FROM criteria cr WHERE (cr.pageant_id = ? AND cr.round_id IS NULL) OR (cr.round_id IN (SELECT id FROM segments WHERE pageant_id = ?))) as total_criteria,
        CASE 
            WHEN COUNT(s.id) = (SELECT COUNT(cr.id) FROM criteria cr WHERE (cr.pageant_id = ? AND cr.round_id IS NULL) OR (cr.round_id IN (SELECT id FROM segments WHERE pageant_id = ?))) THEN 'Complete'
            WHEN COUNT(s.id) > 0 THEN 'Partial'
            ELSE 'Not Started'
        END as status
    FROM candidates c
    LEFT JOIN scores s ON c.id = s.candidate_id AND s.judge_id = ?
    WHERE c.pageant_id = ?
    GROUP BY c.id, c.name, c.age, c.gender
    ORDER BY c.gender, c.name
";
$progressStmt = $db->prepare($progressQuery);
$progressStmt->execute([$pageant_id, $pageant_id, $pageant_id, $pageant_id, $judge_id, $pageant_id]);
$candidates = $progressStmt->fetchAll(PDO::FETCH_ASSOC);

// Group candidates by gender
$maleCandidates = array_filter($candidates, function($candidate) {
    return $candidate['gender'] === 'Male';
});
$femaleCandidates = array_filter($candidates, function($candidate) {
    return $candidate['gender'] === 'Female';
});

// Calculate overall progress
$totalCandidates = count($candidates);
$completedCandidates = 0;
$partialCandidates = 0;

foreach ($candidates as $candidate) {
    if ($candidate['status'] === 'Complete') {
        $completedCandidates++;
    } elseif ($candidate['status'] === 'Partial') {
        $partialCandidates++;
    }
}

$overallProgress = $totalCandidates > 0 ? round(($completedCandidates / $totalCandidates) * 100, 1) : 0;

// Get criteria information
$criteriaQuery = "SELECT cr.* FROM criteria cr WHERE (cr.pageant_id = ? AND cr.round_id IS NULL) OR (cr.round_id IN (SELECT id FROM segments WHERE pageant_id = ?)) ORDER BY cr.percentage DESC";
$criteriaStmt = $db->prepare($criteriaQuery);
$criteriaStmt->execute([$pageant_id, $pageant_id]);
$criteria = $criteriaStmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent scores by this judge
$recentQuery = "
    SELECT 
        c.name as candidate_name,
        cr.name as criteria_name,
        s.score,
        s.created_at
    FROM scores s
    JOIN candidates c ON s.candidate_id = c.id
    JOIN criteria cr ON s.criteria_id = cr.id
    WHERE s.judge_id = ? AND c.pageant_id = ?
    ORDER BY s.created_at DESC
    LIMIT 10
";
$recentStmt = $db->prepare($recentQuery);
$recentStmt->execute([$judge_id, $pageant_id]);
$recentScores = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Judge Dashboard - <?php echo htmlspecialchars($pageant['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .judge-dashboard-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.95) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .judge-dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }
        
        .progress-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(#28a745 0deg, #28a745 calc(var(--progress) * 3.6deg), #e9ecef calc(var(--progress) * 3.6deg), #e9ecef 360deg);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 0 auto 1rem;
        }
        
        .progress-circle::before {
            content: '';
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            position: absolute;
        }
        
        .progress-text {
            position: relative;
            z-index: 1;
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
        }
        
        .candidate-card {
            border-radius: 15px;
            border: none;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .candidate-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .status-complete { border-left: 5px solid #28a745; }
        .status-partial { border-left: 5px solid #ffc107; }
        .status-not-started { border-left: 5px solid #dc3545; }
        
        .score-badge {
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
        }
        
        .criteria-item {
            background: rgba(255, 255, 255, 0.7);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .recent-score-item {
            background: rgba(255, 255, 255, 0.6);
            border-radius: 10px;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border-left: 4px solid #007bff;
        }
        
        .start-scoring-btn {
            background: var(--success-gradient);
            border: none;
            border-radius: 15px;
            padding: 1rem 2rem;
            font-weight: 600;
            color: white;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .start-scoring-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-crown me-2"></i><?php echo htmlspecialchars($pageant['name']); ?>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link">Judge: <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="judge-dashboard-card p-4 text-center">
                    <h1 class="display-5 fw-bold mb-3">
                        <i class="fas fa-gavel text-success"></i>
                        <?php echo htmlspecialchars($pageant['name']); ?>
                    </h1>
                    <p class="lead"><?php echo htmlspecialchars($pageant['theme'] ?? 'Your scoring progress and candidate overview'); ?></p>
                </div>
            </div>
        </div>

        <!-- Progress Overview -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="judge-dashboard-card p-4 text-center">
                    <div class="progress-circle" style="--progress: <?php echo $overallProgress; ?>">
                        <div class="progress-text"><?php echo $overallProgress; ?>%</div>
                    </div>
                    <h5>Overall Progress</h5>
                    <p class="text-muted"><?php echo $completedCandidates; ?> of <?php echo $totalCandidates; ?> candidates completed</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="judge-dashboard-card p-4 text-center">
                    <div class="display-4 text-primary mb-3"><?php echo $totalCandidates; ?></div>
                    <h5>Total Candidates</h5>
                    <p class="text-muted">Ready for scoring</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="judge-dashboard-card p-4 text-center">
                    <div class="display-4 text-warning mb-3"><?php echo count($criteria); ?></div>
                    <h5>Criteria</h5>
                    <p class="text-muted">Scoring categories</p>
                </div>
            </div>
        </div>

        <!-- Quick Action -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="judge-dashboard-card p-4 text-center">
                    <h4 class="mb-3"><i class="fas fa-star"></i> Ready to Score?</h4>
                    <a href="scoring.php" class="start-scoring-btn">
                        <i class="fas fa-clipboard-list"></i> Start Scoring Candidates
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Male Candidates Progress -->
            <div class="col-md-6 mb-4">
                <div class="judge-dashboard-card p-4">
                    <h5 class="mb-3 text-primary">
                        <i class="fas fa-male"></i> Male Candidates 
                        <span class="badge bg-primary ms-2"><?php echo count($maleCandidates); ?></span>
                    </h5>
                    <?php if (empty($maleCandidates)): ?>
                        <p class="text-muted text-center">No male candidates available for scoring.</p>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($maleCandidates as $candidate): ?>
                                <div class="candidate-card card status-<?php echo strtolower(str_replace(' ', '-', $candidate['status'])); ?>">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($candidate['name']); ?></h6>
                                                <?php if ($candidate['age']): ?>
                                                    <small class="text-muted">Age: <?php echo $candidate['age']; ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <span class="score-badge badge bg-<?php 
                                                    echo $candidate['status'] === 'Complete' ? 'success' : 
                                                        ($candidate['status'] === 'Partial' ? 'warning' : 'danger'); 
                                                ?>">
                                                    <?php echo $candidate['status']; ?>
                                                </span>
                                                <div class="small text-muted mt-1">
                                                    <?php echo $candidate['scores_given']; ?>/<?php echo $candidate['total_criteria']; ?> criteria
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Female Candidates Progress -->
            <div class="col-md-6 mb-4">
                <div class="judge-dashboard-card p-4">
                    <h5 class="mb-3 text-danger">
                        <i class="fas fa-female"></i> Female Candidates 
                        <span class="badge bg-danger ms-2"><?php echo count($femaleCandidates); ?></span>
                    </h5>
                    <?php if (empty($femaleCandidates)): ?>
                        <p class="text-muted text-center">No female candidates available for scoring.</p>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($femaleCandidates as $candidate): ?>
                                <div class="candidate-card card status-<?php echo strtolower(str_replace(' ', '-', $candidate['status'])); ?>">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($candidate['name']); ?></h6>
                                                <?php if ($candidate['age']): ?>
                                                    <small class="text-muted">Age: <?php echo $candidate['age']; ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <span class="score-badge badge bg-<?php 
                                                    echo $candidate['status'] === 'Complete' ? 'success' : 
                                                        ($candidate['status'] === 'Partial' ? 'warning' : 'danger'); 
                                                ?>">
                                                    <?php echo $candidate['status']; ?>
                                                </span>
                                                <div class="small text-muted mt-1">
                                                    <?php echo $candidate['scores_given']; ?>/<?php echo $candidate['total_criteria']; ?> criteria
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

            <!-- Scoring Criteria & Recent Activity -->
            <div class="col-md-6">
                <!-- Criteria -->
                <div class="judge-dashboard-card p-4 mb-4">
                    <h5 class="mb-3"><i class="fas fa-list-check text-success"></i> Scoring Criteria</h5>
                    <?php foreach ($criteria as $criterion): ?>
                        <div class="criteria-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($criterion['name']); ?></strong>
                                    <div class="small text-muted"><?php echo htmlspecialchars($criterion['description']); ?></div>
                                </div>
                                <span class="badge bg-primary"><?php echo $criterion['percentage']; ?>%</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Recent Scores -->
                <div class="judge-dashboard-card p-4">
                    <h5 class="mb-3"><i class="fas fa-clock text-info"></i> Recent Scores</h5>
                    <?php if (empty($recentScores)): ?>
                        <p class="text-muted text-center">No scores submitted yet.</p>
                    <?php else: ?>
                        <div style="max-height: 250px; overflow-y: auto;">
                            <?php foreach ($recentScores as $score): ?>
                                <div class="recent-score-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($score['candidate_name']); ?></strong>
                                            <div class="small text-muted"><?php echo htmlspecialchars($score['criteria_name']); ?></div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary"><?php echo $score['score']; ?>/10</span>
                                            <div class="small text-muted">
                                                <?php echo date('M j, g:i A', strtotime($score['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animate progress circle on load
        document.addEventListener('DOMContentLoaded', function() {
            const progressCircle = document.querySelector('.progress-circle');
            if (progressCircle) {
                progressCircle.style.setProperty('--progress', 0);
                setTimeout(() => {
                    progressCircle.style.setProperty('--progress', <?php echo $overallProgress; ?>);
                }, 500);
            }
        });
        
        // Auto-refresh every 60 seconds
        setTimeout(() => {
            location.reload();
        }, 60000);
    </script>
</body>
</html>
