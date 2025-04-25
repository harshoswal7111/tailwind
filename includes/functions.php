<?php
function getAllApprovedMembers() {
    $membersData = json_decode(file_get_contents('./data/members.json'), true);
    $approvedMembers = array_filter($membersData['members'], function($member) {
        return $member['status'] === 'approved';
    });
    return $approvedMembers;
}

// Other functions will be added here
?>