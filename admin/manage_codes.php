<?php 
include '../includes/auth_check.php'; 
include '../includes/admin_header.php'; 

$codeData = json_decode(file_get_contents('../data/one_time_codes.json'), true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numberOfCodes = $_POST['number_of_codes'];
    for ($i = 0; $i < $numberOfCodes; $i++) {
        $code = uniqid();
        $codeData['codes'][] = ['code_value' => $code, 'status' => 'unused'];
    }
    file_put_contents('../data/one_time_codes.json', json_encode($codeData));
}

?>

<form action="" method="post">
    <label for="number_of_codes">Number of Codes:</label>
    <input type="number" id="number_of_codes" name="number_of_codes" required>
    <input type="submit" value="Generate Codes">
</form>

<h2>Existing Codes</h2>
<?php foreach ($codeData['codes'] as $code) { ?>
    <p><?php echo $code['code_value']; ?> - <?php echo $code['status']; ?></p>
<?php } ?>

<?php include '../includes/admin_footer.php'; ?>