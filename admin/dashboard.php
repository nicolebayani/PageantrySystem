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

// Get statistics
$stats = [];

// Total candidates
$candidateQuery = "SELECT COUNT(*) as total FROM candidates";
$candidateStmt = $db->prepare($candidateQuery);
$candidateStmt->execute();
$stats['candidates'] = $candidateStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total criteria
$criteriaQuery = "SELECT COUNT(*) as total FROM criteria";
$criteriaStmt = $db->prepare($criteriaQuery);
$criteriaStmt->execute();
$stats['criteria'] = $criteriaStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total judges
$judgeQuery = "SELECT COUNT(*) as total FROM users WHERE role = 'judge'";
$judgeStmt = $db->prepare($judgeQuery);
$judgeStmt->execute();
$stats['judges'] = $judgeStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total scores submitted
$scoreQuery = "SELECT COUNT(*) as total FROM scores";
$scoreStmt = $db->prepare($scoreQuery);
$scoreStmt->execute();
$stats['scores'] = $scoreStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Scoring progress
$progressQuery = "
    SELECT 
        u.full_name as judge_name,
        COUNT(DISTINCT s.candidate_id) as scored_candidates,
        (SELECT COUNT(*) FROM candidates) as total_candidates,
        ROUND((COUNT(DISTINCT s.candidate_id) / (SELECT COUNT(*) FROM candidates)) * 100, 1) as progress_percent
    FROM users u
    LEFT JOIN scores s ON u.id = s.judge_id
    WHERE u.role = 'judge'
    GROUP BY u.id, u.full_name
    ORDER BY progress_percent DESC
";
$progressStmt = $db->prepare($progressQuery);
$progressStmt->execute();
$judgeProgress = $progressStmt->fetchAll(PDO::FETCH_ASSOC);

// Recent activities
$activityQuery = "
    SELECT 
        'score' as type,
        u.full_name as actor,
        c.name as target,
        s.created_at as timestamp
    FROM scores s
    JOIN users u ON s.judge_id = u.id
    JOIN candidates c ON s.candidate_id = c.id
    ORDER BY s.created_at DESC
    LIMIT 10
";
$activityStmt = $db->prepare($activityQuery);
$activityStmt->execute();
$activities = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

// Check if criteria percentages total 100%
$percentageQuery = "SELECT SUM(percentage) as total FROM criteria";
$percentageStmt = $db->prepare($percentageQuery);
$percentageStmt->execute();
$totalPercentage = $percentageStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pageantry System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        <?php echo $settings->generateCSS(); ?>
        .dashboard-card {
            background: <?php echo $currentSettings['card_style'] === 'glassmorphism' ? 'linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.95) 100%)' : ($currentSettings['card_style'] === 'shadow' ? 'rgba(255, 255, 255, 0.98)' : 'rgba(255, 255, 255, 1)'); ?>;
            backdrop-filter: <?php echo $currentSettings['card_style'] === 'glassmorphism' ? 'blur(10px)' : 'none'; ?>;
            border: <?php echo $currentSettings['card_style'] === 'flat' ? 'none' : '1px solid rgba(255, 255, 255, 0.2)'; ?>;
            border-radius: <?php echo $currentSettings['card_style'] === 'flat' ? '8px' : '20px'; ?>;
            box-shadow: <?php echo $currentSettings['card_style'] === 'shadow' ? 'var(--shadow-medium)' : ($currentSettings['card_style'] === 'flat' ? 'none' : 'var(--shadow-soft)'); ?>;
            transition: all 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }
        
        .stat-card {
            text-align: center;
            padding: 2rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: #6c757d;
            font-weight: 500;
        }
        
        .progress-item {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .activity-item {
            background: rgba(255, 255, 255, 0.6);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border-left: 4px solid #007bff;
        }
        
        .quick-action-btn {
            border-radius: 15px;
            padding: 1rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin: 0.5rem;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-3px);
            color: white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .btn-candidates { background: <?php echo $currentSettings['theme_style'] === 'gradient' ? 'var(--pageant-gradient)' : 'var(--pageant-primary)'; ?>; }
        .btn-criteria { background: <?php echo $currentSettings['theme_style'] === 'gradient' ? 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)' : '#4facfe'; ?>; }
        .btn-judges { background: <?php echo $currentSettings['theme_style'] === 'gradient' ? 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)' : '#43e97b'; ?>; }
        .btn-results { background: <?php echo $currentSettings['theme_style'] === 'gradient' ? 'var(--pageant-accent-gradient)' : 'var(--pageant-accent)'; ?>; }
        .btn-rounds { background: <?php echo $currentSettings['theme_style'] === 'gradient' ? 'linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%)' : '#ff9a9e'; ?>; }
        
        .alert-setup {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 1px solid #ffc107;
            border-radius: 15px;
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
                <a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card p-4 text-center">
                    <h1 class="display-5 fw-bold mb-3">
                        <i class="fas fa-tachometer-alt text-primary"></i>
                        Admin Dashboard
                    </h1>
                    <p class="lead">Manage <?php echo htmlspecialchars($currentSettings['pageant_name']); ?> with ease</p>
                </div>
            </div>
        </div>

        <!-- Setup Alert -->
        <?php if ($totalPercentage != 100 || $stats['candidates'] == 0 || $stats['judges'] == 0): ?>
        <div class="alert alert-setup mb-4">
            <h5><i class="fas fa-exclamation-triangle text-warning"></i> Setup Required</h5>
            <ul class="mb-0">
                <?php if ($stats['candidates'] == 0): ?>
                    <li>Add candidates to the competition</li>
                <?php endif; ?>
                <?php if ($totalPercentage != 100): ?>
                    <li>Criteria percentages must total 100% (currently <?php echo $totalPercentage; ?>%)</li>
                <?php endif; ?>
                <?php if ($stats['judges'] == 0): ?>
                    <li>Add judges to score the candidates</li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="dashboard-card stat-card">
                    <div class="stat-number text-primary"><?php echo $stats['candidates']; ?></div>
                    <div class="stat-label">Candidates</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stat-card">
                    <div class="stat-number text-success"><?php echo $stats['criteria']; ?></div>
                    <div class="stat-label">Criteria</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stat-card">
                    <div class="stat-number text-warning"><?php echo $stats['judges']; ?></div>
                    <div class="stat-label">Judges</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card stat-card">
                    <div class="stat-number text-info"><?php echo $stats['scores']; ?></div>
                    <div class="stat-label">Scores Submitted</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card p-4">
                    <h4 class="mb-3"><i class="fas fa-bolt"></i> Quick Actions</h4>
                    <div class="text-center">
                        <a href="candidates.php" class="quick-action-btn btn-candidates">
                            <i class="fas fa-users"></i> Manage Candidates
                        </a>
                        <a href="criteria_overview.php" class="quick-action-btn btn-criteria">
                            <i class="fas fa-list-check"></i> View Criteria
                        </a>
                        <a href="judges.php" class="quick-action-btn btn-judges">
                            <i class="fas fa-gavel"></i> Manage Judges
                        </a>
                        <a href="rounds.php" class="quick-action-btn btn-rounds">
                            <i class="fas fa-layer-group"></i> Manage Segments
                        </a>
                        <a href="results.php" class="quick-action-btn btn-results">
                            <i class="fas fa-trophy"></i> View Results
                        </a>
                        <a href="settings.php" class="quick-action-btn" style="background: var(--royal-gradient);">
                            <i class="fas fa-cog"></i> Pageant Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Judge Progress -->
            <div class="col-md-6 mb-4">
                <div class="dashboard-card p-4">
                    <h5 class="mb-3"><i class="fas fa-chart-line text-primary"></i> Judge Progress</h5>
                    <?php if (empty($judgeProgress)): ?>
                        <p class="text-muted text-center">No judges added yet.</p>
                    <?php else: ?>
                        <?php foreach ($judgeProgress as $progress): ?>
                            <div class="progress-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong><?php echo htmlspecialchars($progress['judge_name']); ?></strong>
                                    <span class="badge bg-primary"><?php echo $progress['progress_percent']; ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo $progress['progress_percent']; ?>%">
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <?php echo $progress['scored_candidates']; ?> of <?php echo $progress['total_candidates']; ?> candidates scored
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-md-6 mb-4">
                <div class="dashboard-card p-4">
                    <h5 class="mb-3"><i class="fas fa-clock text-success"></i> Recent Activity</h5>
                    <?php if (empty($activities)): ?>
                        <p class="text-muted text-center">No recent activity.</p>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($activity['actor']); ?></strong>
                                            scored <em><?php echo htmlspecialchars($activity['target']); ?></em>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('M j, g:i A', strtotime($activity['timestamp'])); ?>
                                        </small>
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
        // Auto-refresh dashboard every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
        
        // Animate statistics on load
        document.addEventListener('DOMContentLoaded', function() {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const finalValue = parseInt(stat.textContent);
                let currentValue = 0;
                const increment = Math.ceil(finalValue / 20);
                
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(timer);
                    }
                    stat.textContent = currentValue;
                }, 50);
            });
        });
    </script>
</body>
</html>
