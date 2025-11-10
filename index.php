<?php
session_start();
require_once 'db_connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username and password are required";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Automatically determine role
                $_SESSION['role'] = ($user['username'] === 'admin') ? 'admin' : 'user';
                
                header("Location: dashboard.php");
                exit();
            }
        }
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | The Classic Barber</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-page {
            background: linear-gradient(rgba(0, 0, 0, 0.6), url('barber-bg.jpg');
            background-size: cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .brand-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .brand-header h1 {
            color: #3a2c1a;
            font-size: 2rem;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #c19a6b;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: #d4af37;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .admin-notice {
            text-align: center;
            margin-top: 1rem;
            color: #666;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="brand-header">
            <h1>✂ The Classic Barber ✂</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
        
        <div class="links">
            <p>New customer? <a href="register.php">Create account</a></p>
            <p class="admin-notice">Barber login: Use 'admin' account</p>
        </div>
    </div>
</body>
</html>