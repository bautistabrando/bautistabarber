<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE appointments SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif (isset($_POST['reject'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE appointments SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

$stmt = $conn->prepare("SELECT * FROM appointments WHERE status = 'pending' ORDER BY datetime");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Appointments |Brando Barber shop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard">
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">The Classic Barber</a>
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
        <h1>Pending Appointments</h1>
        
        <div class="card">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="appointment-card">
                        <p><strong>Customer:</strong> <?= htmlspecialchars($row['username']) ?></p>
                        <p><strong>Service:</strong> <?= htmlspecialchars($row['service']) ?></p>
                        <p><strong>Requested Time:</strong> <?= date('F j, Y g:i A', strtotime($row['datetime'])) ?></p>
                        
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" name="approve" class="btn">Approve</button>
                            <button type="submit" name="reject" class="btn danger">Reject</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No pending appointments at this time.</p>
            <?php endif; ?>
        </div>
        
        <a href="dashboard.php" class="btn back-btn">Back to Dashboard</a>
    </div>
</body>
</html>