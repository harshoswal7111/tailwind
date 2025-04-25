<?php 
include '../includes/auth_check.php'; 
include '../includes/admin_header.php'; 

$membersData = json_decode(file_get_contents('../data/members.json'), true);
$pendingMembers = array_filter($membersData['members'], function($member) {
    return $member['status'] === 'pending';
});

if (empty($pendingMembers)) {
    echo "<p>No pending members.</p>";
} else {
    foreach ($pendingMembers as $member) {
        echo "<p>$member[name] - <a href='approve_member_process.php?id=$member[id]'>Approve</a> | <a href='reject_member_process.php?id=$member[id]'>Reject</a></p>";
    }
}

include '../includes/admin_footer.php'; 
?>