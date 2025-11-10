<?php
session_start();
include 'db_connection.php';

// Redirect if not admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get appointment statistics
$stats = [];
$result = $conn->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status");
while ($row = $result->fetch_assoc()) {
    $stats[$row['status']] = $row['count'];
}

// Get recent appointments
$recent_appointments = [];
$result = $conn->query("SELECT * FROM appointments ORDER BY datetime DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $recent_appointments[] = $row;
}

// Get daily appointment counts for the week
$daily_counts = [];
$result = $conn->query("
    SELECT DATE(datetime) as day, COUNT(*) as count 
    FROM appointments 
    WHERE datetime >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(datetime)
    ORDER BY day
");
while ($row = $result->fetch_assoc()) {
    $daily_counts[$row['day']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Brando Barber shop</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary);
            margin: 0.5rem 0;
        }
        
        .stat-label {
            color: var(--secondary);
            font-size: 1.1rem;
        }
        
        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .recent-appointments {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .appointment-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .appointment-item:last-child {
            border-bottom: none;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body class="dashboard">
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">Brando Barber shop</a>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">Admin Panel</a>
            </li>
            <li class="nav-item">
                <a href="pending_appointments.php" class="nav-link">Pending Approvals</a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link">Logout</a>
            </li>
        </ul>
    </nav>

    <div class="admin-container">
        <h1>Appointments Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Appointments</div>
                <div class="stat-value"><?= array_sum($stats) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending Approval</div>
                <div class="stat-value"><?= $stats['pending'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Confirmed</div>
                <div class="stat-value"><?= $stats['approved'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Cancelled</div>
                <div class="stat-value"><?= $stats['cancelled'] ?? 0 ?></div>
            </div>
        </div>
        
        <div class="chart-container">
            <h2>Appointments This Week</h2>
            <canvas id="dailyChart" height="100"></canvas>
        </div>
        
        <div class="chart-container">
            <h2>Appointment Status</h2>
            <canvas id="statusChart" height="100"></canvas>
        </div>
        
        <div class="recent-appointments">
            <h2>Recent Appointments</h2>
            <?php if (!empty($recent_appointments)): ?>
                <?php foreach ($recent_appointments as $appt): ?>
                    <div class="appointment-item">
                        <div>
                            <strong><?= htmlspecialchars($appt['service']) ?></strong><br>
                            <?= htmlspecialchars($appt['username']) ?> - 
                            <?= date('M j, Y g:i A', strtotime($appt['datetime'])) ?>
                        </div>
                        <div>
                            <span class="status-badge status-<?= $appt['status'] ?>">
                                <?= ucfirst($appt['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No recent appointments found.</p>
            <?php endif; ?>
        </div>
        
        <div class="btn-group">
            <a href="pending_appointments.php" class="btn btn-primary">Manage Pending Appointments</a>
            <a href="dashboard.php" class="btn btn-secondary">Back to Admin Panel</a>
        </div>
    </div>

    <script>
        // Daily appointments chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const dailyChart = new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    // Generate labels for the last 7 days
                    for ($i = 6; $i >= 0; $i--) {
                        $day = date('Y-m-d', strtotime("-$i days"));
                        echo "'" . date('D, M j', strtotime($day)) . "',";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Appointments',
                    data: [
                        <?php 
                        // Fill in counts for each day
                        for ($i = 6; $i >= 0; $i--) {
                            $day = date('Y-m-d', strtotime("-$i days"));
                            echo ($daily_counts[$day] ?? 0) . ",";
                        }
                        ?>
                    ],
                    backgroundColor: 'rgba(212, 175, 55, 0.7)',
                    borderColor: 'rgba(212, 175, 55, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Status chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Pending', 'Approved', 'Cancelled'],
                datasets: [{
                    data: [
                        <?= $stats['pending'] ?? 0 ?>,
                        <?= $stats['approved'] ?? 0 ?>,
                        <?= $stats['cancelled'] ?? 0 ?>
                    ],
                    backgroundColor: [
                        '#fff3cd',
                        '#d4edda',
                        '#f8d7da'
                    ],
                    borderColor: [
                        '#856404',
                        '#155724',
                        '#721c24'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>   
</body>
</html>