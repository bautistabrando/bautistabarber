<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {
    $datetime = $_POST['datetime'];
    $user = $_SESSION['username'];
    $service = sanitizeInput($_POST['service']);
    
    // Validate datetime is in the future
    if (strtotime($datetime) < time()) {
        $error = "Please select a future date and time";
    } else {
        // Check if time slot is available (at least 1 hour apart)
        $stmt = $conn->prepare("SELECT id FROM appointments 
                               WHERE ABS(TIMESTAMPDIFF(MINUTE, datetime, ?)) < 60
                               AND status != 'cancelled'");
        $stmt->bind_param("s", $datetime);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "This time slot is already taken or too close to another appointment. Please choose a different time.";
        } else {
            $stmt = $conn->prepare("INSERT INTO appointments (username, datetime, service, status) 
                                   VALUES (?, ?, ?, 'pending')");
            $stmt->bind_param("sss", $user, $datetime, $service);
            
            if ($stmt->execute()) {
                $success = "Appointment booked successfully! Our barber will confirm your appointment soon.";
            } else {
                $error = "Failed to book appointment. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment | Brando Barber shop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">Brando Barber shop</a>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link">Logout</a>
            </li>
        </ul>
    </nav>

    <div class="container">
        <h1>Book an Appointment</h1>
        
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php else: ?>
            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label for="service">Service</label>
                        <select name="service" id="service" required>
                            <option value="Classic Haircut">Classic Haircut - 60 PEsos only</option>
                             </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="datetime">Date & Time</label>
                        <input type="datetime-local" name="datetime" id="datetime" required 
                               min="<?= date('Y-m-d\TH:i', strtotime('+1 day')) ?>">
                        <small>We're open Monday-Saturday, 9AM-7PM</small>
                    </div>
                    
                    <button type="submit" name="book" class="btn">Book Appointment</button>
                </form>
            </div>
        <?php endif; ?>
        
        <a href="dashboard.php" class="btn back-btn">Back to Dashboard</a>
    </div>
</body>
</html>