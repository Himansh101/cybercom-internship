<?php
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!$isLoggedIn) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_profile':
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $street_address = trim($_POST['street_address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';

        $errors = [];

        // Validate required fields
        if (strlen($name) < 3 || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
            $errors[] = "Invalid name.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email.";
        }
        if (!empty($mobile) && !preg_match("/^(\+91)[6-9][0-9]{9}$/", $mobile)) {
            $errors[] = "Invalid mobile number.";
        }
        if (!empty($pincode) && !preg_match("/^[1-9][0-9]{5}$/", $pincode)) {
            $errors[] = "Invalid pincode.";
        }

        // Check email uniqueness (if changed)
        $stmt = $pdo->prepare("SELECT entity_id FROM customer_entity WHERE email = ? AND entity_id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $errors[] = "Email already in use.";
        }

        // Password change validation
        $updatePassword = false;
        if (!empty($new_password)) {
            if (empty($current_password)) {
                $errors[] = "Current password required.";
            } else {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password FROM customer_entity WHERE entity_id = ?");
                $stmt->execute([$userId]);
                $dbPassword = $stmt->fetchColumn();

                if (!password_verify($current_password, $dbPassword)) {
                    $errors[] = "Current password is incorrect.";
                } elseif (strlen($new_password) < 8) {
                    $errors[] = "New password must be at least 8 characters.";
                } else {
                    $updatePassword = true;
                }
            }
        }

        if (!empty($errors)) {
            echo json_encode(['status' => 'error', 'message' => implode("\n", $errors)]);
            exit();
        }

        try {
            // Build update query
            $sql = "UPDATE customer_entity SET name = ?, email = ?, mobile = ?, street_address = ?, city = ?, pincode = ?";
            $params = [$name, $email, $mobile ?: null, $street_address ?: null, $city ?: null, $pincode ?: null];

            if ($updatePassword) {
                $sql .= ", password = ?";
                $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE entity_id = ?";
            $params[] = $userId;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully!']);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update profile.']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>