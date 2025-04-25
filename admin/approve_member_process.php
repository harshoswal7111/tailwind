<?php
include '../includes/auth_check.php';

if (isset($_GET['id'])) {
    $memberId = $_GET['id'];
    $membersData = json_decode(file_get_contents('../data/members.json'), true);
    foreach ($membersData['members'] as &$member) {
        if ($member['id'] == $memberId) {
            $member['status'] = 'approved';
            break;
        }
    }
    file_put_contents('../data/members.json', json_encode($membersData));
    header('Location: manage_pending.php');
    exit;
} else {
    header('Location: manage_pending.php');
    exit;
}
?>