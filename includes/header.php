<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JRFC Member Directory</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @tailwind base;
        @tailwind components;
        @tailwind utilities;
    </style>
</head>
<body class="bg-gray-100">
<nav class="bg-blue-600 p-4">
    <div class="container mx-auto flex justify-between items-center">
        <a href="/" class="text-white text-xl font-bold">JRFC Members</a>
        <div class="space-x-4">
            <a href="register_family.php" class="text-white hover:text-blue-200">Register Family</a>
            <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']): ?>
                <a href="admin/" class="text-white hover:text-blue-200">Admin Panel</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="container mx-auto p-4">