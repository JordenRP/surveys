<nav style="background-color: #f4f4f9; padding: 10px; margin-bottom: 20px;">
    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="/" style="margin-right: 15px;">Home</a>
    <a href="/dashboard.php" style="margin-right: 15px;">Dashboard</a>
    <a href="/admin.php" style="margin-right: 15px;">Admin Panel</a>
    <a href="/charts.php" style="margin-right: 15px;">Charts</a>
    <a href="/logout.php">Logout</a>
    <?php else: ?>
    <a href="/login.php" style="margin-right: 15px;">Login</a>
    <a href="/register.php">Register</a>
    <?php endif; ?>
</nav>