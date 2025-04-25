<?php include './includes/header.php'; ?>
<form action="process_registration.php" method="post">
    <!-- Form fields for member signup -->
    <label for="one_time_code">One-Time Code:</label>
    <input type="text" id="one_time_code" name="one_time_code" required>
    <!-- Other form fields -->
    <input type="submit" value="Submit">
</form>
<?php include './includes/footer.php'; ?>