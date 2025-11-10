<?php
session_start();
include 'db_connection.php';

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Handle appointment cancellation
if (isset($_GET['cancel'])) {
    $appointment_id = (int)$_GET['cancel'];
    $username = $_SESSION['username'];
    
    $stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' 
                           WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $appointment_id, $username);
    $stmt->execute();
    
    header("Location: my_appointments.php?cancelled=1");
    exit();
}

// Handle appointment rescheduling
if (isset($_POST['reschedule'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $new_datetime = $_POST['datetime'];
    $username = $_SESSION['username'];
    
    // Validate new datetime is in the future
    if (strtotime($new_datetime) > time()) {
        $stmt = $conn->prepare("UPDATE appointments SET datetime = ?, status = 'pending' 
                               WHERE id = ? AND username = ?");
        $stmt->bind_param("sis", $new_datetime, $appointment_id, $username);
        $stmt->execute();
        
        header("Location: my_appointments.php?rescheduled=1");
        exit();
    } else {
        $error = "Please select a future date and time";
    }
}

// Get user's appointments
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT * FROM appointments 
                       WHERE username = ? 
                       AND status != 'cancelled'
                       AND datetime >= NOW()
                       ORDER BY datetime");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments |Brando Barber shop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .appointments-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            margin-left: 1rem;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .appointment-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary);
        }
        
        .appointment-actions {
            margin-top: 1rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .reschedule-form {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .no-appointments {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body class="dashboard">
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">Brando Barber shop</a>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
            </li>
            <li class="nav-item">
                <a href="my_appointments.php" class="nav-link">My Appointments</a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="logout-btn">Logout</a>
            </li>
        </ul>
    </nav>

    <div class="appointments-container">
        <h1>My Appointments</h1>
        
        <?php if (isset($_GET['cancelled'])): ?>
            <div class="alert alert-success">
                Appointment cancelled successfully.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['rescheduled'])): ?>
            <div class="alert alert-success">
                Appointment rescheduled successfully. Waiting for barber confirmation.
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="appointment-card">
                    <h3><?= htmlspecialchars($row['service']) ?></h3>
                    <p><strong>Date & Time:</strong> <?= date('l, F j, Y \a\t h:i A', strtotime($row['datetime'])) ?></p>
                    <p><strong>Duration:</strong> <?= $row['duration'] ?> minutes</p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge status-<?= $row['status'] ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </p>
                    
                    <div class="appointment-actions">
                        <?php if ($row['status'] != 'cancelled' && strtotime($row['datetime']) > time()): ?>
                            <button onclick="showRescheduleForm(<?= $row['id'] ?>)" class="btn btn-primary">
                                Reschedule
                            </button>
                            
                            <a href="my_appointments.php?cancel=<?= $row['id'] ?>" class="btn btn-danger">
                                Cancel Appointment
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div id="reschedule-form-<?= $row['id'] ?>" class="reschedule-form">
                        <form method="POST">
                            <input type="hidden" name="appointment_id" value="<?= $row['id'] ?>">
                            <div class="form-group">
                                <label for="datetime-<?= $row['id'] ?>">New Date & Time</label>
                                <input type="datetime-local" name="datetime" id="datetime-<?= $row['id'] ?>" 
                                       min="<?= date('Y-m-d\TH:i', strtotime('+1 day')) ?>" required>
                            </div>
                            <button type="submit" name="reschedule" class="btn btn-primary">
                                Confirm Reschedule
                            </button>
                            <button type="button" onclick="hideRescheduleForm(<?= $row['id'] ?>)" class="btn btn-danger">
                                Cancel
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-appointments">
                <h3>You have no upcoming appointments</h3>
                <p>Book your next grooming session now!</p>
                <a href="book.php" class="btn btn-primary">Book Appointment</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function showRescheduleForm(appointmentId) {
            document.getElementById('reschedule-form-' + appointmentId).style.display = 'block';
        }
        
        function hideRescheduleForm(appointmentId) {
            document.getElementById('reschedule-form-' + appointmentId).style.display = 'none';
        }
    </script>
</body>
</html>