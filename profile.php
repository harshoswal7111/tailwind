<?php 
include './includes/header.php'; 

if (isset($_GET['id'])) {
    $memberId = $_GET['id'];
    $membersData = json_decode(file_get_contents('./data/members.json'), true);
    foreach ($membersData['members'] as $member) {
        if ($member['id'] == $memberId && $member['status'] === 'approved') {
            echo "<h1>$member[name]</h1>";
            // Display other member details
            break;
        }
    }
} else {
    echo "Member ID not provided.";
}

include './includes/footer.php'; 
?>