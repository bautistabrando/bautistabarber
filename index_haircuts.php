<?php
session_start();
include 'db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services | Brando Barber shop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .services-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .service-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .service-header h1 {
            font-size: 2.5rem;
            color: var(--secondary);
            margin-bottom: 1rem;
        }
        
        .service-header p {
            font-size: 1.2rem;
            color: var(--dark);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .service-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
        }
        
        .service-image {
            height: 200px;
            overflow: hidden;
        }
        
        .service-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .service-card:hover .service-image img {
            transform: scale(1.1);
        }
        
        .service-content {
            padding: 1.5rem;
        }
        
        .service-content h3 {
            color: var(--secondary);
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }
        
        .service-content p {
            color: var(--dark);
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .service-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .service-duration {
            display: inline-block;
            background: var(--light);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            color: var(--secondary);
        }
        
        .book-now-btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 1rem;
            transition: background 0.3s ease;
        }
        
        .book-now-btn:hover {
            background: var(--secondary);
        }
        
        .nav-back {
            display: block;
            text-align: center;
            margin-top: 3rem;
        }
    </style>
</head>
<body class="dashboard">
    <nav class="navbar">
        <a href="<?php echo isset($_SESSION['username']) ? 'dashboard.php' : 'index.php'; ?>" class="navbar-brand">Brando Barber shop</a>
        <ul class="navbar-nav">
            <?php if (isset($_SESSION['username'])): ?>
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">Logout</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Login</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="services-container">
        <div class="service-header">
            <h1><span class="barber-pole">✂</span> Our Premium Services <span class="barber-pole">✂</span></h1>
            <p>Experience the finest traditional barber services with modern techniques. Our master barbers provide exceptional grooming services tailored to your style.</p>
        </div>
        
        <div class="services-grid">
            <!-- Service 1 -->
            <div class="service-card">
                <div class="service-image">
                    <img src="image/buzzcut.jpg" alt="Classic Haircut">
                </div>
                <div class="service-content">
                    <h3>buzzcut Haircut</h3>
                    <p>A precision haircut using traditional techniques with modern styling for a sharp, clean look.</p>
                    <div class="service-price">$30</div>
                    <span class="service-duration">45 min</span>
                    <?php if (isset($_SESSION['username']) && $_SESSION['role'] === 'user'): ?>
                        <a href="book.php?service=Classic+Haircut" class="book-now-btn">Book Now</a>
                    <?php elseif (!isset($_SESSION['username'])): ?>
                        <a href="index.php" class="book-now-btn">Login to Book</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Service 2 -->
            <div class="service-card">
                <div class="service-image">
                    <img src="image/blow out taper.jpg" alt="Beard Trim">
                </div>
                <div class="service-content">
                    <h3>Beard Trim & Shape</h3>
                    <p>Professional beard trimming and shaping to maintain your desired look with precision.</p>
                    <div class="service-price">$20</div>
                    <span class="service-duration">30 min</span>
                    <?php if (isset($_SESSION['username']) && $_SESSION['role'] === 'user'): ?>
                        <a href="book.php?service=Beard+Trim" class="book-now-btn">Book Now</a>
                    <?php elseif (!isset($_SESSION['username'])): ?>
                        <a href="index.php" class="book-now-btn">Login to Book</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Service 3 -->
            <div class="service-card">
                <div class="service-image">
                    <img src="image/mid-fade-crop-top.jpg" alt="Hot Towel Shave">
                </div>
                <div class="service-content">
                    <h3>Hot Towel Shave</h3>
                    <p>The ultimate traditional shaving experience with hot towels, premium products, and a straight razor.</p>
                    <div class="service-price">$25</div>
                    <span class="service-duration">40 min</span>
                    <?php if (isset($_SESSION['username']) && $_SESSION['role'] === 'user'): ?>
                        <a href="book.php?service=Hot+Towel+Shave" class="book-now-btn">Book Now</a>
                    <?php elseif (!isset($_SESSION['username'])): ?>
                        <a href="index.php" class="book-now-btn">Login to Book</a>
                    <?php endif; ?>
                </div>
            </div>
            
           
        <a href="<?php echo isset($_SESSION['username']) ? 'dashboard.php' : 'index.php'; ?>" class="nav-back">← Back to <?php echo isset($_SESSION['username']) ? 'Dashboard' : 'Home'; ?></a>
    </div>
</body>
</html>