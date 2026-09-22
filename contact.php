<?php require 'config/db.php'; session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Us - Moctale Reborn</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <h1><a href="index.php" style="text-decoration:none; color:inherit;">Moctale Reborn</a></h1>
        <a href="index.php" style="color:white;">Back to Home</a>
    </div>
    <div style="padding: 30px; max-width: 700px;">
        <h2>Contact Us</h2>
        <p style="color: var(--text-muted); line-height: 1.7;">
            Have feedback, found a bug, or just want to say hi? Reach out below.
        </p>
        <p><strong>Email:</strong> <a href="mailto:youremail@example.com">youremail@example.com</a></p>
        <p><strong>GitHub:</strong> <a href="https://github.com/gituser-16/Moctale-Reborn" target="_blank">View the project source</a></p>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>