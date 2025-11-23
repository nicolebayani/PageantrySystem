<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pageantry Tabulating System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-crown me-2"></i>Pageantry System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if ($isLoggedIn): ?>
                        <?php if ($userRole === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/candidates.php">Candidates</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/criteria.php">Criteria</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/results.php">Results</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="judge/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="judge/scoring.php">Score Candidates</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2 text-center">
                <div class="hero-section">
                    <h1 class="display-4 mb-4">
                        <i class="fas fa-crown text-warning"></i>
                        Pageantry Tabulating System
                    </h1>
                    <p class="lead mb-4">Professional scoring and results management for beauty pageants</p>
                    
                    <?php if (!$isLoggedIn): ?>
                        <div class="row mt-5">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-user-shield text-primary"></i> Admin Access
                                        </h5>
                                        <p class="card-text">Manage candidates, criteria, and view results</p>
                                        <a href="auth/login.php" class="btn btn-primary">Admin Login</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-gavel text-success"></i> Judge Access
                                        </h5>
                                        <p class="card-text">Score candidates based on criteria</p>
                                        <a href="auth/login.php" class="btn btn-success">Judge Login</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-user-plus text-warning"></i> New User
                                        </h5>
                                        <p class="card-text">Create your admin or judge account</p>
                                        <a href="auth/register.php" class="btn btn-warning">Register Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if ($userRole === 'admin'): ?>
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                            <h5>Candidates</h5>
                                            <a href="admin/candidates.php" class="btn btn-primary">Manage</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <i class="fas fa-list-check fa-3x text-success mb-3"></i>
                                            <h5>Criteria</h5>
                                            <a href="admin/criteria.php" class="btn btn-success">Manage</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <i class="fas fa-gavel fa-3x text-warning mb-3"></i>
                                            <h5>Judges</h5>
                                            <a href="admin/judges.php" class="btn btn-warning">Manage</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card text-center">
                                        <div class="card-body">
                                            <i class="fas fa-trophy fa-3x text-danger mb-3"></i>
                                            <h5>Results</h5>
                                            <a href="admin/results.php" class="btn btn-danger">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card mt-4">
                                <div class="card-body text-center">
                                    <h3><i class="fas fa-clipboard-list text-primary"></i> Judge Dashboard</h3>
                                    <p class="mb-4">Score candidates based on the established criteria</p>
                                    <a href="judge/scoring.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-star"></i> Start Scoring
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
