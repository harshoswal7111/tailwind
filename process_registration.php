<?php
include './includes/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $oneTimeCode = $_POST['one_time_code'];
    // Validate one-time code
    $codeData = json_decode(file_get_contents('./data/one_time_codes.json'), true);
    $validCode = false;
    foreach ($codeData['codes'] as $code) {
        if ($code['code_value'] === $oneTimeCode && $code['status'] === 'unused') {
            $validCode = true;
            // Mark code as used
            foreach ($codeData['codes'] as &$c) {
                if ($c['code_value'] === $oneTimeCode) {
                    $c['status'] = 'used';
                    $c['used_at'] = date('Y-m-d H:i:s');
                    break;
                }
            }
            file_put_contents('./data/one_time_codes.json', json_encode($codeData));
            break;
        }
    }

    if ($validCode) {
        // Process member registration
        $memberData = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            // Add other member details
            'status' => 'pending'
        ];
        $membersData = json_decode(file_get_contents('./data/members.json'), true);
        $membersData['members'][] = $memberData;
        file_put_contents('./data/members.json', json_encode($membersData));
        header('Location: signup_success.php');
        exit;
    } else {
        // Handle invalid code
        echo "Invalid one-time code.";
    }
} else {
    header('Location: register_family.php');
    exit;
}
?>