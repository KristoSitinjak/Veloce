<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

/**
 * Ensure user_profile table exists (id, user_id, full_name, phone, address, city, postal_code)
 */
function ensureUserProfileTable()
{
    static $checked = false;
    if ($checked) {
        return;
    }

    global $pdo;

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_profile (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            full_name VARCHAR(150) DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            address TEXT DEFAULT NULL,
            city VARCHAR(100) DEFAULT NULL,
            postal_code VARCHAR(20) DEFAULT NULL,
            avatar VARCHAR(255) DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES akun(id) ON DELETE CASCADE
        )
    ");

    $checked = true;
}

function getUserProfile($userId = null)
{
    global $pdo;
    ensureUserProfileTable();

    if ($userId === null) {
        $userId = getUserId();
    }

    if (!$userId) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT * FROM user_profile WHERE user_id = ?");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch();

    if (!$profile) {
        // Return empty defaults when no profile yet
        return [
            'user_id' => $userId,
            'full_name' => '',
            'phone' => '',
            'address' => '',
            'city' => '',
            'postal_code' => '',
            'avatar' => null
        ];
    }

    return $profile;
}

function saveUserProfile($data, $userId = null)
{
    global $pdo;
    ensureUserProfileTable();

    if ($userId === null) {
        $userId = getUserId();
    }

    if (!$userId) {
        return false;
    }

    $profile = getUserProfile($userId);

    $fields = ['full_name', 'phone', 'address', 'city', 'postal_code', 'avatar'];
    $values = [];

    foreach ($fields as $field) {
        $values[$field] = trim($data[$field] ?? ($profile[$field] ?? ''));
    }

    if (empty($profile['id'])) {
        $stmt = $pdo->prepare("
            INSERT INTO user_profile (user_id, full_name, phone, address, city, postal_code, avatar)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $userId,
            $values['full_name'],
            $values['phone'],
            $values['address'],
            $values['city'],
            $values['postal_code'],
            $values['avatar']
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE user_profile
            SET full_name = ?, phone = ?, address = ?, city = ?, postal_code = ?, avatar = ?
            WHERE user_id = ?
        ");
        return $stmt->execute([
            $values['full_name'],
            $values['phone'],
            $values['address'],
            $values['city'],
            $values['postal_code'],
            $values['avatar'],
            $userId
        ]);
    }
}


