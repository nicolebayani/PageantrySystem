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

// Calculate results
$resultsQuery = "
    SELECT 
        c.id,
        c.name,
        c.age,
        c.description,
        SUM(s.score * cr.percentage / 100) as weighted_score,
        COUNT(DISTINCT s.judge_id) as judge_count
    FROM candidates c
    LEFT JOIN scores s ON c.id = s.candidate_id
    LEFT JOIN criteria cr ON s.criteria_id = cr.id
    GROUP BY c.id, c.name, c.age, c.description
    HAVING judge_count > 0
    ORDER BY weighted_score DESC
";

$resultsStmt = $db->prepare($resultsQuery);
$resultsStmt->execute();
$results = $resultsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get detailed scores for each candidate
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
    GROUP BY c.id, c.name, cr.id, cr.name, cr.percentage
    ORDER BY c.name, cr.percentage DESC
";

$detailedStmt = $db->prepare($detailedQuery);
$detailedStmt->execute();
$detailedScores = $detailedStmt->fetchAll(PDO::FETCH_ASSOC);

// Group detailed scores by candidate
$candidateDetails = [];
foreach ($detailedScores as $score) {
    $candidateDetails[$score['candidate_name']][] = $score;
}

// Get total number of judges
$judgeCountQuery = "SELECT COUNT(*) as total_judges FROM users WHERE role = 'judge'";
$judgeCountStmt = $db->prepare($judgeCountQuery);
$judgeCountStmt->execute();
$totalJudges = $judgeCountStmt->fetch(PDO::FETCH_ASSOC)['total_judges'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results - Pageantry System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .winner-card {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            border: 3px solid #ffd700;
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        .runner-up-card {
            background: linear-gradient(135deg, #c0c0c0, #e8e8e8);
            border: 2px solid #c0c0c0;
        }
        
        .third-place-card {
            background: linear-gradient(135deg, #cd7f32, #daa520);
            border: 2px solid #cd7f32;
        }
        
        @keyframes glow {
            from { box-shadow: 0 0 20px #ffd700; }
            to { box-shadow: 0 0 30px #ffd700, 0 0 40px #ffd700; }
        }
        
        .crown-animation {
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: #ffd700;
            animation: confetti-fall 3s linear infinite;
        }
        
        @keyframes confetti-fall {
            0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
        
        .winner-announcement {
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #ffeaa7);
            background-size: 300% 300%;
            animation: gradient-animation 4s ease infinite;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        @keyframes gradient-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-crown me-2"></i>Pageantry System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">Dashboard</a>
                <a class="nav-link" href="candidates.php">Candidates</a>
                <a class="nav-link" href="criteria.php">Criteria</a>
                <a class="nav-link" href="judges.php">Judges</a>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (empty($results)): ?>
            <div class="text-center">
                <div class="card">
                    <div class="card-body">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h4>No Results Available</h4>
                        <p>No scores have been submitted by judges yet.</p>
                        <a href="judges.php" class="btn btn-primary">Manage Judges</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Winner Announcement -->
            <?php if (count($results) > 0): ?>
                <div class="winner-announcement text-center p-4 rounded mb-4">
                    <h1 class="display-4 mb-3">
                        <i class="fas fa-crown crown-animation"></i>
                        WINNER ANNOUNCEMENT
                        <i class="fas fa-crown crown-animation"></i>
                    </h1>
                    <button class="btn btn-light btn-lg" onclick="announceWinner()">
                        <i class="fas fa-trophy"></i> Reveal Winner
                    </button>
                </div>
            <?php endif; ?>

            <!-- Results Cards -->
            <div class="row" id="resultsContainer" style="display: none;">
                <?php foreach ($results as $index => $result): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 <?php 
                            if ($index === 0) echo 'winner-card';
                            elseif ($index === 1) echo 'runner-up-card';
                            elseif ($index === 2) echo 'third-place-card';
                        ?>">
                            <div class="card-header text-center">
                                <h3>
                                    <?php if ($index === 0): ?>
                                        <i class="fas fa-crown text-warning"></i> 1st Place
                                    <?php elseif ($index === 1): ?>
                                        <i class="fas fa-medal text-secondary"></i> 2nd Place
                                    <?php elseif ($index === 2): ?>
                                        <i class="fas fa-medal text-warning"></i> 3rd Place
                                    <?php else: ?>
                                        <?php echo ($index + 1); ?><?php echo ($index + 1) == 4 ? 'th' : 'th'; ?> Place
                                    <?php endif; ?>
                                </h3>
                            </div>
                            <div class="card-body text-center">
                                <h4><?php echo htmlspecialchars($result['name']); ?></h4>
                                <?php if ($result['age']): ?>
                                    <p class="text-muted">Age: <?php echo $result['age']; ?></p>
                                <?php endif; ?>
                                <h2 class="text-primary">
                                    <?php echo number_format($result['weighted_score'], 2); ?>
                                </h2>
                                <p class="text-muted">Final Score</p>
                                <small class="text-muted">
                                    Scored by <?php echo $result['judge_count']; ?> of <?php echo $totalJudges; ?> judges
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Detailed Scores -->
            <div class="card mt-4" id="detailedScores" style="display: none;">
                <div class="card-header bg-info text-white">
                    <h5><i class="fas fa-chart-bar"></i> Detailed Scores Breakdown</h5>
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
    <script>
        function announceWinner() {
            // Hide announcement button
            document.querySelector('.winner-announcement').style.display = 'none';
            
            // Create confetti
            createConfetti();
            
            // Show results with animation
            const resultsContainer = document.getElementById('resultsContainer');
            resultsContainer.style.display = 'flex';
            
            // Animate cards appearing one by one
            const cards = resultsContainer.querySelectorAll('.card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(50px)';
                    card.style.transition = 'all 0.8s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 500);
            });
            
            // Show detailed scores after all cards are shown
            setTimeout(() => {
                document.getElementById('detailedScores').style.display = 'block';
                document.getElementById('detailedScores').style.opacity = '0';
                document.getElementById('detailedScores').style.transition = 'opacity 1s ease';
                setTimeout(() => {
                    document.getElementById('detailedScores').style.opacity = '1';
                }, 100);
            }, cards.length * 500 + 1000);
        }
        
        function createConfetti() {
            const colors = ['#ffd700', '#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#ffeaa7'];
            
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + 'vw';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animationDelay = Math.random() * 2 + 's';
                    confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
                    
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => {
                        confetti.remove();
                    }, 5000);
                }, i * 100);
            }
        }
        
        // Auto-refresh results every 30 seconds
        setInterval(() => {
            if (document.getElementById('resultsContainer').style.display !== 'none') {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html>
