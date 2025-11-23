<?php
session_start();
require_once '../config/database.php';

$error = '';

if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, username, password, role, full_name FROM users WHERE username = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        
        if ($user['role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: ../judge/dashboard.php');
        }
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pageantry System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4><i class="fas fa-sign-in-alt"></i> Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p class="mb-2">Don't have an account?</p>
                            <a href="register.php" class="btn btn-outline-success">
                                <i class="fas fa-user-plus"></i> Create Account
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="../index.php" class="text-decoration-none text-muted">
                                <i class="fas fa-home"></i> Back to Home
                            </a>
                        </div>
                        
                        <div class="mt-4">
                            <h6>Demo Accounts:</h6>
                            <small class="text-muted">
                                Admin: admin / admin123<br>
                                Judge: judge1 / judge123
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
