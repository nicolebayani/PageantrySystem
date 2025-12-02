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

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $candidate_number = $_POST['candidate_number'];
                $name = $_POST['name'];
                $age = $_POST['age'];
                $description = $_POST['description'];
                
                // Handle file upload
                $photo = null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['photo']['name'];
                    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($filetype), $allowed)) {
                        $newname = 'candidate_' . $candidate_number . '_' . time() . '.' . $filetype;
                        $upload_path = '../uploads/candidates/';
                        
                        // Create directory if it doesn't exist
                        if (!file_exists($upload_path)) {
                            mkdir($upload_path, 0777, true);
                        }
                        
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path . $newname)) {
                            $photo = $newname;
                        } else {
                            $message = '<div class="alert alert-warning">Candidate added but photo upload failed.</div>';
                        }
                    } else {
                        $message = '<div class="alert alert-warning">Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.</div>';
                    }
                }
                
                $query = "INSERT INTO candidates (candidate_number, name, age, description, photo) VALUES (?, ?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                if ($stmt->execute([$candidate_number, $name, $age, $description, $photo])) {
                    if (!$message) {
                        $message = '<div class="alert alert-success">Candidate added successfully!</div>';
                    }
                } else {
                    $message = '<div class="alert alert-danger">Error adding candidate. Candidate number might already exist.</div>';
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $query = "DELETE FROM candidates WHERE id = ?";
                $stmt = $db->prepare($query);
                if ($stmt->execute([$id])) {
                    $message = '<div class="alert alert-success">Candidate deleted successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error deleting candidate.</div>';
                }
                break;
        }
    }
}

// Get all candidates
$query = "SELECT * FROM candidates ORDER BY candidate_number";
$stmt = $db->prepare($query);
$stmt->execute();
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - Pageantry System</title>
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
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-user-plus"></i> Add New Candidate</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label for="candidate_number" class="form-label">Candidate Number</label>
                                <input type="text" class="form-control" id="candidate_number" name="candidate_number" required placeholder="e.g., 001, 002, etc.">
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="age" class="form-label">Age</label>
                                <input type="number" class="form-control" id="age" name="age" min="18" max="35">
                            </div>
                            <div class="mb-3">
                                <label for="photo" class="form-label">Candidate Photo</label>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                <div class="form-text">Accepted formats: JPG, JPEG, PNG, GIF</div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Add Candidate
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i> Current Candidates</h5>
                        <span class="badge bg-white text-success"><?php echo count($candidates); ?> Candidates</span>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <?php if (empty($candidates)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-user-slash fa-4x mb-3 opacity-50"></i>
                                <p class="h5">No candidates added yet.</p>
                                <p class="text-muted">Add your first candidate using the form on the left</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach ($candidates as $candidate): ?>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <div class="candidate-card card h-100 border-0 shadow-sm hover-shadow transition-all">
                                            <div class="position-relative">
                                                <?php if ($candidate['photo']): ?>
                                                    <img src="../uploads/candidates/<?php echo htmlspecialchars($candidate['photo']); ?>" 
                                                         class="card-img-top" alt="<?php echo htmlspecialchars($candidate['name']); ?>"
                                                         style="height: 200px; object-fit: cover; border-radius: 0.5rem 0.5rem 0 0;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                                         style="height: 200px; border-radius: 0.5rem 0.5rem 0 0; background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);">
                                                        <i class="fas fa-user text-muted fa-5x opacity-25"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="candidate-number">
                                                    <span class="badge rounded-pill bg-primary">#<?php echo htmlspecialchars($candidate['candidate_number']); ?></span>
                                                </div>
                                            </div>
                                            <div class="card-body position-relative">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($candidate['name']); ?></h5>
                                                    <span class="badge bg-light text-dark"><?php echo $candidate['age']; ?> yrs</span>
                                                </div>
                                                <p class="card-text text-muted small mb-3">
                                                    <?php 
                                                    $desc = trim($candidate['description']);
                                                    echo !empty($desc) ? htmlspecialchars($desc) : '<span class="text-muted">No description provided</span>';
                                                    ?>
                                                </p>
                                                <form method="POST" class="d-flex justify-content-end" onsubmit="return confirm('Are you sure you want to delete this candidate?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $candidate['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Candidate">
                                                        <i class="fas fa-trash-alt me-1"></i> Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <style>
                .candidate-card {
                    transition: all 0.3s ease;
                    border-radius: 0.75rem;
                    overflow: hidden;
                    position: relative;
                    background: #fff;
                }
                
                .candidate-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
                }
                
                .candidate-number {
                    position: absolute;
                    top: 1rem;
                    left: 1rem;
                }
                
                .candidate-number .badge {
                    font-size: 0.8rem;
                    padding: 0.35em 0.65em;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }
                
                .card-title {
                    font-weight: 600;
                    color: #2d3748;
                }
                
                .transition-all {
                    transition: all 0.2s ease-in-out;
                }
                
                .hover-shadow:hover {
                    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
                }
                
                /* Animation for card entrance */
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                .candidate-card {
                    animation: fadeInUp 0.5s ease-out forwards;
                    opacity: 0;
                }
                
                /* Staggered animation delay */
                <?php 
                $delay = 0;
                foreach ($candidates as $index => $candidate): 
                    $delay = $index * 0.1;
                ?>
                    .candidate-card:nth-child(<?php echo $index + 1; ?>) {
                        animation-delay: <?php echo $delay; ?>s;
                    }
                <?php endforeach; ?>
            </style>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
