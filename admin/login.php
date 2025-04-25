<?php 
include '../includes/header.php'; 
?>
<form action="login_process.php" method="post">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <input type="submit" value="Login">
</form>
<?php include '../includes/footer.php'; ?>