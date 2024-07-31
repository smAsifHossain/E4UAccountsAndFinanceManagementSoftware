<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Education 4 You</title>
    <link rel="stylesheet" href="index_styles.css"> <!-- Link to your CSS file -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet"> <!-- Google Fonts -->
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>Education 4 You</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h1>Welcome to Education 4 You</h1>
                <p>Your trusted partner to study abroad!</p>
                <a href="login.php" class="btn">Get Started with Accounts Management Software</a>
            </div>
        </section>

        <section class="developer-info">
            <div class="container">
                <h2>Backslash N</h2>
                <p>This software has been developed by Backslash N. <br> Developer: S M Asif Hossain</p>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h2>Our Services in this Software</h2>
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-graduation-cap"></i>
                        <h3>Student Accounts Management</h3>
                        <p>Manage student accounts with ease and efficiency.</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <h3>Student Finance Management</h3>
                        <p>Keep track of student finances and transactions.</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-briefcase"></i>
                        <h3>Office Finance Management</h3>
                        <p>Handle all office-related financial activities.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Backslash N Limited. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>