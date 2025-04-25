<?php
require_once '../includes/auth.php';
require_once '../includes/FileDB.php';

$auth = new Auth();
$fileDB = new FileDB();

if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pendingMembers = $fileDB->getAllMembers('pending');
?>
<?php include '../includes/header.php'; ?>
<h1 class="text-2xl font-bold mb-4">Pending Approvals</h1>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php foreach ($pendingMembers as $member): ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($member['name']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($member['email']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap"><?= date('M j, Y', $member['created_at']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <form method="post" action="process/approve_member.php" class="inline">
                        <input type="hidden" name="id" value="<?= $member['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $auth->getCSRFToken() ?>">
                        <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                    </form>
                    <form method="post" action="process/reject_member.php" class="inline ml-2">
                        <input type="hidden" name="id" value="<?= $member['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $auth->getCSRFToken() ?>">
                        <button type="submit" class="text-red-600 hover:text-red-900">Reject</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>