<?php session_start(); ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EAMUPortal - University Attendance System</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- CSS -->
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/forms.css">
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">    <link rel="stylesheet" href="css/forms.css">
    </head>
    <body>
        <div class="container">
            <div class="login-container">
                <div class="login-form">
                    <div class="logo">
                        <img src="images/EAMU.png" alt="EAMU Logo">
                    </div>
                    <h1>Welcome to EAMUPortal 👋</h1>
                    <p class="subtitle">Today is a new day. It's your day. You shape it.<br>Sign in to start managing your projects.</p>
                    
                    <?php if(isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                        ?>
                    </div>
                    <?php endif; ?>
                    
                    <form action="auth/login.php" method="POST">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="Example@email.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="At least 8 characters" required>
                        </div>
                        
                        <div class="forgot-password">
                            <a href="auth/forgot-password.php">Forgot Password?</a>
                        </div>
                        
                        <button type="submit" class="sign-in-btn">Sign in</button>
                    </form>
                </div>
                <div class="building-image">
                    <img src="images/eamu-building.jpg" alt="EAMU Building">
                </div>
            </div>
            <footer>
                <p>&copy; 2023 ALL RIGHTS RESERVED</p>
            </footer>
        </div>
    </body>
    </html>
