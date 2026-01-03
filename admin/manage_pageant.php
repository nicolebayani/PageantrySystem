<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

if (!isset($_GET['pageant_id'])) {
    header('Location: pageants.php');
    exit();
}

$pageant_id = $_GET['pageant_id'];

// Fetch pageant details
$pageantQuery = $db->prepare("SELECT * FROM pageants WHERE id = ?");
$pageantQuery->execute([$pageant_id]);
$pageant = $pageantQuery->fetch(PDO::FETCH_ASSOC);

if (!$pageant) {
    die('Pageant not found.');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage <?php echo htmlspecialchars($pageant['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-white">Manage: <?php echo htmlspecialchars($pageant['name']); ?></h1>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x mb-3"></i>
                        <h5 class="card-title">Candidates</h5>
                        <p class="card-text">Manage the contestants for this pageant.</p>
                        <a href="candidates.php?pageant_id=<?php echo $pageant_id; ?>" class="btn btn-primary">Manage Candidates</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-gavel fa-3x mb-3"></i>
                        <h5 class="card-title">Judges</h5>
                        <p class="card-text">Assign and manage judges for this pageant.</p>
                        <a href="assign_judge.php?pageant_id=<?php echo $pageant_id; ?>" class="btn btn-primary">Manage Judges</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-trophy fa-3x mb-3"></i>
                        <h5 class="card-title">Results</h5>
                        <p class="card-text">View the live results and final tally.</p>
                        <a href="results.php?pageant_id=<?php echo $pageant_id; ?>" class="btn btn-primary">View Results</a>
                    </div>
                </div>
            </div>
        </div>
         <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-layer-group fa-3x mb-3"></i>
                        <h5 class="card-title">Segments</h5>
                        <p class="card-text">Manage the segments or rounds of the pageant.</p>
                        <a href="segments.php?pageant_id=<?php echo $pageant_id; ?>" class="btn btn-primary">Manage Segments</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-star fa-3x mb-3"></i>
                        <h5 class="card-title">Criteria</h5>
                        <p class="card-text">Manage the scoring criteria for each segment.</p>
                        <a href="criteria.php?pageant_id=<?php echo $pageant_id; ?>" class="btn btn-primary">Manage Criteria</a>
                    </div>
                </div>
            </div>
        </div>
        <a href="pageants.php" class="btn btn-secondary mt-4">Back to Pageants</a>
    </div>
</body>
</html>
