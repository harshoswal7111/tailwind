<?php
require_once 'config.php'; // Ensure config is loaded

// --- JSON Data Handling Functions ---

/**
 * Reads data from a JSON file with file locking.
 *
 * @param string $filePath The path to the JSON file.
 * @return array The decoded JSON data as an associative array or an empty array on failure/empty file.
 */
function readJsonFile(string $filePath): array {
    if (!file_exists($filePath)) {
        // Attempt to create the file if it doesn't exist
        if (file_put_contents($filePath, '[]') === false) {
             error_log("Failed to create JSON file: " . $filePath);
             return []; // Or throw an exception
        }
    }

    $fileHandle = fopen($filePath, 'r');
    if (!$fileHandle) {
        error_log("Failed to open JSON file for reading: " . $filePath);
        return []; // Or throw an exception
    }

    // Acquire a shared lock (LOCK_SH) for reading
    if (flock($fileHandle, LOCK_SH)) {
        $content = fread($fileHandle, filesize($filePath) ?: 1); // Read content, handle empty file
        flock($fileHandle, LOCK_UN); // Release the lock
        fclose($fileHandle);

        if ($content === false) {
             error_log("Failed to read JSON file: " . $filePath);
             return [];
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error in file " . $filePath . ": " . json_last_error_msg());
            // Decide how to handle invalid JSON: return empty, throw exception, try to recover?
            // For now, return empty array to avoid breaking the application entirely.
             return [];
        }
        // Ensure we always return an array, even if the file was empty or contained `null`
        return is_array($data) ? $data : [];
    } else {
        fclose($fileHandle);
        error_log("Failed to acquire lock for reading JSON file: " . $filePath);
        // Handle lock failure - maybe retry or throw an exception
        return []; // Or throw an exception
    }
}

/**
 * Writes data to a JSON file with exclusive file locking.
 *
 * @param string $filePath The path to the JSON file.
 * @param array $data The data to encode and write.
 * @return bool True on success, false on failure.
 */
function writeJsonFile(string $filePath, array $data): bool {
    $fileHandle = fopen($filePath, 'c'); // Open for writing; place pointer at beginning; create if not exists
     if (!$fileHandle) {
        error_log("Failed to open JSON file for writing: " . $filePath);
        return false;
    }

    // Acquire an exclusive lock (LOCK_EX) for writing
    if (flock($fileHandle, LOCK_EX)) {
        // Truncate the file to zero length before writing
        if (!ftruncate($fileHandle, 0)) {
             error_log("Failed to truncate JSON file: " . $filePath);
             flock($fileHandle, LOCK_UN);
             fclose($fileHandle);
             return false;
        }
        // Rewind pointer after truncating
        rewind($fileHandle);

        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON encode error: " . json_last_error_msg());
            flock($fileHandle, LOCK_UN);
            fclose($fileHandle);
            return false;
        }

        $bytesWritten = fwrite($fileHandle, $jsonData);
        fflush($fileHandle); // Ensure data is written to disk
        flock($fileHandle, LOCK_UN); // Release the lock
        fclose($fileHandle);

        if ($bytesWritten === false) {
             error_log("Failed to write to JSON file: " . $filePath);
             return false;
        }
        return true;
    } else {
        fclose($fileHandle);
        error_log("Failed to acquire lock for writing JSON file: " . $filePath);
        return false; // Handle lock failure
    }
}

// --- Member-related functions (using JSON) ---

/**
 * Gets all members sorted by name.
 *
 * @return array List of members.
 */
function getAllMembers(): array {
    $members = readJsonFile(MEMBERS_FILE);
    // Ensure it's always an indexed array, even if readJsonFile returns an empty associative array
    $members = array_values($members);
    usort($members, function($a, $b) {
        return strcmp($a['name'] ?? '', $b['name'] ?? '');
    });
    return $members;
}

/**
 * Gets a single member by their ID.
 *
 * @param int $member_id The ID of the member.
 * @return array|null The member data or null if not found.
 */
function getMemberById(int $member_id): ?array {
    $members = readJsonFile(MEMBERS_FILE);
    foreach ($members as $member) {
        // Ensure strict type comparison if IDs are consistently integers
        if (isset($member['member_id']) && (int)$member['member_id'] === $member_id) {
            return $member;
        }
    }
    return null;
}

/**
 * Gets a single member by their email address.
 *
 * @param string $email The email of the member.
 * @return array|null The member data or null if not found.
 */
function getMemberByEmail(string $email): ?array {
    $members = readJsonFile(MEMBERS_FILE);
    foreach ($members as $member) {
        if (isset($member['email']) && strtolower($member['email']) === strtolower($email)) {
            return $member;
        }
    }
    return null;
}

/**
 * Adds a new member to the JSON file.
 *
 * @param array $memberData Associative array of member data.
 * @return int|false The new member ID or false on failure.
 */
function addMember(array $memberData): int|false {
    $members = readJsonFile(MEMBERS_FILE);
    // Ensure $members is an indexed array for reliable ID generation
    $members = array_values($members);

    // Generate a new unique ID (simple auto-increment for file-based storage)
    $newId = 1;
    if (!empty($members)) {
        // Find the maximum existing ID and add 1
        $maxId = 0;
        foreach ($members as $member) {
            if (isset($member['member_id']) && $member['member_id'] > $maxId) {
                $maxId = $member['member_id'];
            }
        }
        $newId = $maxId + 1;
    }

    $memberData['member_id'] = $newId;
    $memberData['created_at'] = date('Y-m-d H:i:s'); // Add creation timestamp
    $memberData['updated_at'] = date('Y-m-d H:i:s'); // Add updated timestamp

    $members[] = $memberData; // Add the new member

    if (writeJsonFile(MEMBERS_FILE, $members)) {
        return $newId;
    } else {
        return false;
    }
}

/**
 * Updates an existing member's data.
 *
 * @param int $member_id The ID of the member to update.
 * @param array $memberData Associative array of data to update.
 * @return bool True on success, false on failure or if member not found.
 */
function updateMember(int $member_id, array $memberData): bool {
    $members = readJsonFile(MEMBERS_FILE);
    $found = false;
    foreach ($members as $index => $member) {
         // Ensure strict type comparison if IDs are consistently integers
        if (isset($member['member_id']) && (int)$member['member_id'] === $member_id) {
            // Merge existing data with new data, preserving fields not being updated
            $members[$index] = array_merge($member, $memberData);
            $members[$index]['updated_at'] = date('Y-m-d H:i:s'); // Update timestamp
            // Ensure member_id isn't accidentally overwritten if not present in $memberData
            $members[$index]['member_id'] = $member_id;
            $found = true;
            break;
        }
    }

    if ($found) {
        return writeJsonFile(MEMBERS_FILE, $members);
    } else {
        return false; // Member not found
    }
}

/**
 * Deletes a member by their ID.
 * Also attempts to delete associated images.
 *
 * @param int $member_id The ID of the member to delete.
 * @return bool True on success, false on failure or if member not found.
 */
function deleteMember(int $member_id): bool {
    $members = readJsonFile(MEMBERS_FILE);
    $initialCount = count($members);
    $memberToDelete = null;

    $filteredMembers = array_filter($members, function($member) use ($member_id, &$memberToDelete) {
        if (isset($member['member_id']) && (int)$member['member_id'] === $member_id) {
            $memberToDelete = $member; // Store member data before filtering out
            return false; // Filter out the member
        }
        return true; // Keep other members
    });

    // Re-index the array after filtering
    $filteredMembers = array_values($filteredMembers);

    if (count($filteredMembers) < $initialCount && $memberToDelete !== null) {
        // Attempt to delete associated images
        if (!empty($memberToDelete['profile_image'])) {
            @unlink(MEMBER_UPLOADS . $memberToDelete['profile_image']);
        }
        if (!empty($memberToDelete['family_image'])) {
             @unlink(FAMILY_UPLOADS . $memberToDelete['family_image']);
        }
         if (!empty($memberToDelete['business_logo'])) {
             @unlink(BUSINESS_UPLOADS . $memberToDelete['business_logo']);
        }
        // Note: Deleting family member images or business images associated via relations
        // would require reading/updating those structures if they were separate files/entries.
        // Assuming they are part of the main member record for now.

        return writeJsonFile(MEMBERS_FILE, $filteredMembers);
    } else {
        return false; // Member not found or deletion failed
    }
}


// --- Family Member & Business Details (Simplified: Assumed part of main member record) ---
// If these were separate tables, they'd need their own JSON files and functions.
// For now, assume 'family_members' and 'business_details' are keys within the member record.

function getFamilyMembers(int $member_id): array {
    $member = getMemberById($member_id);
    $familyMembers = $member['family_members'] ?? [];

    // Sort family members (e.g., spouse first, then children by name)
    usort($familyMembers, function($a, $b) {
        $order = ['spouse' => 1, 'child' => 2];
        $aOrder = $order[$a['relation_type'] ?? 'child'] ?? 99;
        $bOrder = $order[$b['relation_type'] ?? 'child'] ?? 99;

        if ($aOrder != $bOrder) {
            return $aOrder <=> $bOrder;
        }
        return strcmp($a['name'] ?? '', $b['name'] ?? '');
    });

    return $familyMembers;
}

function getBusinessDetails(int $member_id): ?array {
    $member = getMemberById($member_id);
    return $member['business_details'] ?? null;
}


// --- Image handling functions ---
/**
 * Handles file uploads and moves them to the correct directory.
 *
 * @param array $file The $_FILES['input_name'] array.
 * @param string $type 'members', 'family', or 'businesses'. Determines the subdirectory.
 * @param string|null $existingFilename Optional. If provided, delete this file before saving the new one.
 * @return string|false The generated filename on success, false on failure.
 * @throws Exception On validation errors (type, size) or move failure.
 */
function uploadImage(array $file, string $type, ?string $existingFilename = null): string|false {
    // Basic validation
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new Exception('Invalid file upload parameters.');
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            // Allow no file to be uploaded if it's optional
            return null; // Indicate no file was uploaded
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new Exception('Exceeded filesize limit.');
        default:
            throw new Exception('Unknown file upload error.');
    }

    // Check file size (e.g., 5MB)
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        throw new Exception('File size too large. Maximum size is 5MB.');
    }

    // Check MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.');
    }

    // Determine upload directory based on type
    $upload_dir = match ($type) {
        'members' => MEMBER_UPLOADS,
        'family' => FAMILY_UPLOADS,
        'businesses' => BUSINESS_UPLOADS,
        default => throw new Exception('Invalid upload type specified.'),
    };

     // Ensure the target directory exists (config should handle this, but double-check)
    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
        throw new Exception("Failed to create upload directory: {$upload_dir}");
    }
     if (!is_writable($upload_dir)) {
        throw new Exception("Upload directory is not writable: {$upload_dir}");
    }


    // Generate a unique filename to prevent overwrites and handle special characters
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safe_basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
    $filename = uniqid($safe_basename . '_', true) . '.' . strtolower($extension); // More unique filename
    $target_path = $upload_dir . $filename;

    // Delete existing file if requested
    if ($existingFilename) {
        $existingPath = $upload_dir . $existingFilename;
        if (file_exists($existingPath)) {
            @unlink($existingPath); // Use @ to suppress errors if file doesn't exist or permissions fail
        }
    }

    // Move the uploaded file
    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        // Provide more context on failure if possible
         $error = error_get_last();
         $errorMessage = 'Failed to move uploaded file.';
         if ($error) {
             $errorMessage .= " System Error: " . $error['message'];
         }
         // Check permissions specifically
         if (!is_writable(dirname($target_path))) {
              $errorMessage .= " Directory not writable: " . dirname($target_path);
         }
        throw new Exception($errorMessage);
    }

    return $filename; // Return the new filename
}


// --- Admin authentication functions ---

/**
 * Checks if an admin user is logged in via session.
 *
 * @return bool True if logged in, false otherwise.
 */
function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && isset($_SESSION['admin_username']);
}

/**
 * Redirects to the admin login page if the admin is not logged in.
 */
function requireAdminLogin(): void {
    if (!isAdminLoggedIn()) {
        // Store the intended destination to redirect after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit();
    }
}

/**
 * Logs out the admin user by destroying the session.
 */
function adminLogout(): void {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header('Location: ' . BASE_URL . 'admin/login.php?logged_out=true');
    exit();
}

/**
 * Finds an admin user by username (case-insensitive).
 * Reads from admins.json.
 *
 * @param string $username The username to find.
 * @return array|null Admin data array if found, null otherwise.
 */
function getAdminByUsername(string $username): ?array {
    $admins = readJsonFile(ADMINS_FILE);
    foreach ($admins as $admin) {
        if (isset($admin['username']) && strtolower($admin['username']) === strtolower($username)) {
            return $admin;
        }
    }
    return null;
}

/**
 * Adds a new admin user.
 * !! Be cautious using this - ensure proper access control !!
 * Hashes the password before saving.
 *
 * @param string $username
 * @param string $password Plain text password
 * @return bool True on success, false on failure.
 */
function addAdmin(string $username, string $password): bool {
     if (empty($username) || empty($password)) {
         return false; // Basic validation
     }

     $admins = readJsonFile(ADMINS_FILE);

     // Check if username already exists
     foreach ($admins as $admin) {
         if (isset($admin['username']) && strtolower($admin['username']) === strtolower($username)) {
             return false; // Username already taken
         }
     }

     $newAdmin = [
         'admin_id' => count($admins) + 1, // Simple ID assignment
         'username' => $username,
         'password_hash' => generatePasswordHash($password), // Hash the password
         'created_at' => date('Y-m-d H:i:s')
     ];

     $admins[] = $newAdmin;
     return writeJsonFile(ADMINS_FILE, $admins);
}


// --- Security functions ---

/**
 * Sanitizes input data to prevent XSS.
 *
 * @param mixed $data Input data (string or array).
 * @return mixed Sanitized data.
 */
function sanitizeInput(mixed $data): mixed {
    if (is_array($data)) {
        // Recursively sanitize array elements
        return array_map('sanitizeInput', $data);
    } elseif (is_string($data)) {
        // Trim whitespace, remove tags, encode special HTML characters
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    // Return non-string, non-array data as is (e.g., numbers, booleans)
    return $data;
}


/**
 * Generates a secure password hash.
 *
 * @param string $password Plain text password.
 * @return string The password hash.
 */
function generatePasswordHash(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verifies a password against a hash.
 *
 * @param string $password Plain text password.
 * @param string $hash The hash to verify against.
 * @return bool True if the password matches the hash, false otherwise.
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}


// --- Registration code functions (using JSON) ---

/**
 * Generates a random registration code.
 *
 * @param int $length Length of the code.
 * @return string The generated code.
 */
function generateRegistrationCode(int $length = 10): string {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    $charactersLength = strlen($characters);
    for ($i = 0; $i < $length; $i++) {
        try {
            $code .= $characters[random_int(0, $charactersLength - 1)];
        } catch (Exception $e) {
            // Fallback for environments where random_int is not available (less secure)
            $code .= $characters[mt_rand(0, $charactersLength - 1)];
        }
    }
    return $code;
}

/**
 * Creates and stores a new registration code.
 * Codes are stored as keys in codes.json for easy lookup.
 *
 * @param string $adminUsername Username of the admin creating the code.
 * @param int|null $expires_in_days Days until expiry, null for no expiry.
 * @return string|false The generated code or false on failure.
 */
function createRegistrationCode(string $adminUsername, ?int $expires_in_days = 7): string|false {
    $codes = readJsonFile(CODES_FILE);
    $newCode = '';
    // Ensure the generated code is unique
    do {
        $newCode = generateRegistrationCode();
    } while (isset($codes[$newCode]));

    $expires_at = null;
    if ($expires_in_days !== null && $expires_in_days > 0) {
        $expires_at = date('Y-m-d H:i:s', strtotime("+{$expires_in_days} days"));
    }

    $codes[$newCode] = [
        'code' => $newCode, // Store code itself for easier iteration if needed later
        'created_by' => $adminUsername,
        'created_at' => date('Y-m-d H:i:s'),
        'expires_at' => $expires_at,
        'is_active' => true,
        'used_by_member_id' => null,
        'used_at' => null
    ];

    if (writeJsonFile(CODES_FILE, $codes)) {
        return $newCode;
    } else {
        return false;
    }
}

/**
 * Gets all registration codes with details.
 * Reads from codes.json.
 *
 * @return array List of code details.
 */
function getRegistrationCodes(): array {
    $codesData = readJsonFile(CODES_FILE);
    // Convert associative array back to indexed array for display consistency
    $codesList = array_values($codesData);

    // Optional: Fetch member names if needed for display (adds overhead)
    // $members = readJsonFile(MEMBERS_FILE); // Read members once if needed
    // foreach ($codesList as &$code) {
    //     if (!empty($code['used_by_member_id'])) {
    //         $member = getMemberById($code['used_by_member_id']); // Use existing function
    //         $code['member_name'] = $member ? $member['name'] : 'Unknown Member';
    //     } else {
    //          $code['member_name'] = null;
    //     }
    //     // Admin name is already stored as username
    //     $code['admin_name'] = $code['created_by'];
    // }
    // unset($code); // Unset reference

     // Sort by creation date descending
     usort($codesList, function($a, $b) {
        return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
    });


    return $codesList;
}

/**
 * Validates a registration code.
 * Checks if it exists, is active, not expired, and not used.
 *
 * @param string $code The code to validate.
 * @return array|null The code data if valid, null otherwise.
 */
function validateRegistrationCode(string $code): ?array {
    $codes = readJsonFile(CODES_FILE);

    if (!isset($codes[$code])) {
        return null; // Code doesn't exist
    }

    $codeData = $codes[$code];

    if (!$codeData['is_active']) {
        return null; // Code is inactive
    }

    if ($codeData['used_by_member_id'] !== null) {
        return null; // Code has already been used
    }

    // Check expiry date if set
    if ($codeData['expires_at'] !== null && strtotime($codeData['expires_at']) < time()) {
        // Optional: Deactivate expired codes automatically?
        // $codes[$code]['is_active'] = false;
        // writeJsonFile(CODES_FILE, $codes);
        return null; // Code has expired
    }

    return $codeData; // Code is valid
}

/**
 * Marks a registration code as used by a specific member.
 *
 * @param string $code The code that was used.
 * @param int $member_id The ID of the member who used the code.
 * @return bool True on success, false on failure or if code not found/invalid.
 */
function markRegistrationCodeAsUsed(string $code, int $member_id): bool {
    $codes = readJsonFile(CODES_FILE);

    if (!isset($codes[$code])) {
        return false; // Code doesn't exist
    }

    // Double-check validity before marking as used (optional, depends on workflow)
    // if (!validateRegistrationCode($code)) { return false; }

    $codes[$code]['is_active'] = false; // Deactivate the code
    $codes[$code]['used_by_member_id'] = $member_id;
    $codes[$code]['used_at'] = date('Y-m-d H:i:s');

    return writeJsonFile(CODES_FILE, $codes);
}

/**
 * Deactivates a specific registration code.
 *
 * @param string $code The code to deactivate.
 * @return bool True on success, false on failure or if code not found.
 */
function deactivateRegistrationCode(string $code): bool {
    $codes = readJsonFile(CODES_FILE);

    if (!isset($codes[$code])) {
        return false; // Code doesn't exist
    }

    if (!$codes[$code]['is_active']) {
        return true; // Already inactive, consider it a success
    }

    $codes[$code]['is_active'] = false;

    return writeJsonFile(CODES_FILE, $codes);
}

/**
 * Deletes a registration code permanently.
 *
 * @param string $code The code to delete.
 * @return bool True on success, false on failure or if code not found.
 */
function deleteRegistrationCode(string $code): bool {
    $codes = readJsonFile(CODES_FILE);

    if (!isset($codes[$code])) {
        return false; // Code doesn't exist
    }

    unset($codes[$code]); // Remove the code entry

    return writeJsonFile(CODES_FILE, $codes);
}

?>