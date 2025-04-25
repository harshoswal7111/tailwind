<?php 
include './includes/header.php'; 
$approvedMembers = getAllApprovedMembers();
foreach ($approvedMembers as $member) {
    echo "<p>$member[name]</p>";
}
include './includes/footer.php'; 
?>