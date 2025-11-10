<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$page_title = $_SESSION['role'] === 'admin' ? 'Barber Dashboard' : 'Customer Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | Brando Barber shop</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body class="dashboard">
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">Brando Barber shop</a>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="index_haircuts.php" class="nav-link">Services</a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link">Logout</a>
            </li>
        </ul>
    </nav>

    <div class="container">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
        
        <?php if ($_SESSION['role'] === 'user'): ?>
            <div class="card">
                <h2>Customer Dashboard</h2>
                <a href="book.php" class="btn">Book New Appointment</a>
                <a href="my_appointments.php" class="btn">View My Appointments</a>
            </div>
        <?php else: ?>
            <div class="card">
                <h2>Barber Dashboard</h2>
                <a href="admin_dashboard.php" class="btn">my dashboard</a>
                <a href="pending_appointments.php" class="btn">Manage Pending Appointments</a>
            </div>
            
            <div class="card">
                <h3>Today's Appointments</h3>
                <?php
                $today = date('Y-m-d');
                $stmt = $conn->prepare("SELECT * FROM appointments WHERE DATE(datetime) = ? AND status = 'approved' ORDER BY datetime");
                $stmt->bind_param("s", $today);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='appointment-card'>
                            <p><strong>Customer:</strong> {$row['username']}</p>
                            <p><strong>Time:</strong> " . date('h:i A', strtotime($row['datetime'])) . "</p>
                            <p><strong>Service:</strong> Classic Haircut</p>
                        </div>";
                    }
                } else {
                    echo "<p>No appointments scheduled for today.</p>";
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>