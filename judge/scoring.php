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

$message = '';

// Handle score submission
if ($_POST && isset($_POST['scores'])) {
    $judge_id = $_SESSION['user_id'];
    $scores = $_POST['scores'];
    
    try {
        $db->beginTransaction();
        
        foreach ($scores as $candidate_id => $criteriaScores) {
            foreach ($criteriaScores as $criteria_id => $score) {
                if ($score !== '') {
                    $query = "INSERT INTO scores (judge_id, candidate_id, criteria_id, score) 
                             VALUES (?, ?, ?, ?) 
                             ON DUPLICATE KEY UPDATE score = VALUES(score)";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$judge_id, $candidate_id, $criteria_id, $score]);
                }
            }
        }
        
        $db->commit();
        $message = '<div class="alert alert-success">Scores saved successfully!</div>';
    } catch (Exception $e) {
        $db->rollback();
        $message = '<div class="alert alert-danger">Error saving scores: ' . $e->getMessage() . '</div>';
    }
}

// Get judge's assigned pageant from session
if (!isset($_SESSION['pageant_id'])) {
    // Redirect to dashboard if pageant_id is not in session
    header('Location: dashboard.php');
    exit();
}
$pageant_id = $_SESSION['pageant_id'];

<<<<<<< HEAD
// Get all candidates for the assigned pageant
$candidatesQuery = "SELECT * FROM candidates WHERE pageant_id = ? ORDER BY gender, name";
=======
// Get all candidates for the assigned pageant (no gender column, order by name)
$candidatesQuery = "SELECT * FROM candidates WHERE pageant_id = ? ORDER BY name";
>>>>>>> 5e70549 (adding printing function for all segments)
$candidatesStmt = $db->prepare($candidatesQuery);
$candidatesStmt->execute([$pageant_id]);
$candidates = $candidatesStmt->fetchAll(PDO::FETCH_ASSOC);

<<<<<<< HEAD
// Group candidates by gender
$maleCandidates = array_filter($candidates, function($candidate) {
    return $candidate['gender'] === 'Male';
});
$femaleCandidates = array_filter($candidates, function($candidate) {
    return $candidate['gender'] === 'Female';
});

=======
>>>>>>> 5e70549 (adding printing function for all segments)
// Get all criteria for the assigned pageant
$criteriaQuery = "SELECT cr.* FROM criteria cr WHERE (cr.pageant_id = ? AND cr.round_id IS NULL) OR (cr.round_id IN (SELECT id FROM segments WHERE pageant_id = ?)) ORDER BY cr.percentage DESC";
$criteriaStmt = $db->prepare($criteriaQuery);
$criteriaStmt->execute([$pageant_id, $pageant_id]);
$criteria = $criteriaStmt->fetchAll(PDO::FETCH_ASSOC);

// Get existing scores for this judge
$scoresQuery = "SELECT candidate_id, criteria_id, score FROM scores WHERE judge_id = ?";
$scoresStmt = $db->prepare($scoresQuery);
$scoresStmt->execute([$_SESSION['user_id']]);
$existingScores = [];
while ($row = $scoresStmt->fetch(PDO::FETCH_ASSOC)) {
    $existingScores[$row['candidate_id']][$row['criteria_id']] = $row['score'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Score Candidates - Pageantry System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-crown me-2"></i>Pageantry System
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link">Judge: <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php echo $message; ?>
        
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-star"></i> Score Candidates</h4>
                <p class="mb-0">Rate each candidate from 1-10 for each criteria</p>
            </div>
            <div class="card-body">
                <?php if (empty($candidates) || empty($criteria)): ?>
                    <div class="text-center text-muted">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <p>No candidates or criteria available for scoring.</p>
                        <p>Please contact the administrator.</p>
                    </div>
                <?php else: ?>
                    <form method="POST" id="scoringForm">
<<<<<<< HEAD
                        <!-- Male Candidates Section -->
                        <div class="mb-5">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-male"></i> Male Candidates
                            </h4>
                            <?php if (empty($maleCandidates)): ?>
                                <div class="text-center text-muted mb-4">
                                    <p>No male candidates available for scoring.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Candidate</th>
                                                <?php foreach ($criteria as $criterion): ?>
                                                    <th class="text-center">
                                                        <?php echo htmlspecialchars($criterion['name']); ?>
                                                        <br><small class="text-muted"><?php echo $criterion['percentage']; ?>%</small>
                                                    </th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($maleCandidates as $candidate): ?>
                                                <tr>
                                                    <td class="fw-bold">
                                                        <?php echo htmlspecialchars($candidate['name']); ?>
                                                        <?php if ($candidate['age']): ?>
                                                            <br><small class="text-muted">Age: <?php echo $candidate['age']; ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php foreach ($criteria as $criterion): ?>
                                                        <td class="text-center">
                                                            <input type="number" 
                                                                   class="form-control text-center score-input" 
                                                                   name="scores[<?php echo $candidate['id']; ?>][<?php echo $criterion['id']; ?>]"
                                                                   min="1" max="10" step="0.1"
                                                                   value="<?php echo isset($existingScores[$candidate['id']][$criterion['id']]) ? $existingScores[$candidate['id']][$criterion['id']] : ''; ?>"
                                                                   placeholder="1-10">
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Female Candidates Section -->
                        <div class="mb-4">
                            <h4 class="text-danger mb-3">
                                <i class="fas fa-female"></i> Female Candidates
                            </h4>
                            <?php if (empty($femaleCandidates)): ?>
                                <div class="text-center text-muted mb-4">
                                    <p>No female candidates available for scoring.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Candidate</th>
                                                <?php foreach ($criteria as $criterion): ?>
                                                    <th class="text-center">
                                                        <?php echo htmlspecialchars($criterion['name']); ?>
                                                        <br><small class="text-muted"><?php echo $criterion['percentage']; ?>%</small>
                                                    </th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($femaleCandidates as $candidate): ?>
                                                <tr>
                                                    <td class="fw-bold">
                                                        <?php echo htmlspecialchars($candidate['name']); ?>
                                                        <?php if ($candidate['age']): ?>
                                                            <br><small class="text-muted">Age: <?php echo $candidate['age']; ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php foreach ($criteria as $criterion): ?>
                                                        <td class="text-center">
                                                            <input type="number" 
                                                                   class="form-control text-center score-input" 
                                                                   name="scores[<?php echo $candidate['id']; ?>][<?php echo $criterion['id']; ?>]"
                                                                   min="1" max="10" step="0.1"
                                                                   value="<?php echo isset($existingScores[$candidate['id']][$criterion['id']]) ? $existingScores[$candidate['id']][$criterion['id']] : ''; ?>"
                                                                   placeholder="1-10">
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
=======
                        <div class="mb-4">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-users"></i> Candidates
                            </h4>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Candidate</th>
                                            <?php foreach ($criteria as $criterion): ?>
                                                <th class="text-center">
                                                    <?php echo htmlspecialchars($criterion['name']); ?>
                                                    <br><small class="text-muted"><?php echo $criterion['percentage']; ?>%</small>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($candidates as $candidate): ?>
                                            <tr>
                                                <td class="fw-bold">
                                                    <?php echo htmlspecialchars($candidate['name']); ?>
                                                    <?php if ($candidate['age']): ?>
                                                        <br><small class="text-muted">Age: <?php echo $candidate['age']; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <?php foreach ($criteria as $criterion): ?>
                                                    <td class="text-center">
                                                        <input type="number" 
                                                               class="form-control text-center score-input" 
                                                               name="scores[<?php echo $candidate['id']; ?>][<?php echo $criterion['id']; ?>]"
                                                               min="1" max="10" step="0.1"
                                                               value="<?php echo isset($existingScores[$candidate['id']][$criterion['id']]) ? $existingScores[$candidate['id']][$criterion['id']] : ''; ?>"
                                                               placeholder="1-10">
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
>>>>>>> 5e70549 (adding printing function for all segments)
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Save Scores
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4">
                        <h5>Scoring Guidelines:</h5>
                        <ul class="list-unstyled">
                            <li><strong>10:</strong> Exceptional/Outstanding</li>
                            <li><strong>8-9:</strong> Very Good/Excellent</li>
                            <li><strong>6-7:</strong> Good/Above Average</li>
                            <li><strong>4-5:</strong> Average/Fair</li>
                            <li><strong>1-3:</strong> Below Average/Poor</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-save functionality
        document.addEventListener('DOMContentLoaded', function() {
            const scoreInputs = document.querySelectorAll('.score-input');
            
            scoreInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value < 1 || this.value > 10) {
                        alert('Score must be between 1 and 10');
                        this.focus();
                    }
                });
            });
        });
        
        // Confirm before leaving if there are unsaved changes
        let formChanged = false;
        document.getElementById('scoringForm').addEventListener('change', function() {
            formChanged = true;
        });
        
        document.getElementById('scoringForm').addEventListener('submit', function() {
            formChanged = false;
        });
        
        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    </script>
</body>
</html>
