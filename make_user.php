<?php

require_once 'web/lib/Database.php';

# Create and update users and passwords.

function main($argv)
{
    if (count($argv) != 3) {
        echo "Usage:\n" .
             "  php $argv[0] username password\n";
        exit(1);
    }

    $user = strtolower($argv[1]);
    $pass = $argv[2];

    if (!preg_match('/^[a-z0-9]{6,32}$/', $user)) {
        echo "Username must consist of alpha numeric characters between 6 and".
             "32 characters in length.\n";
        return 1;
    }

    if (!preg_match('/^[[:print:]]{8,255}$/', $pass)) {
       echo "Password must consist of printable characters and be between 8 ".
            "and 255 characters in length.\n";
       return 1;
    }

    try {
        $db = new DB;
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        return 1;
    }

    $update = false;

    try {
        $SQL = "SELECT user_id FROM tbl_users WHERE username = ?";
        $stmt = $db->pdo->prepare($SQL);
        $stmt->execute([$user]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($res !== false) {
            $update = true;
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        return 1;
    }

    try {
        if ($update) {
            $SQL = "UPDATE tbl_users SET PASSWORD = ? WHERE username = ?";
            $stmt = $db->pdo->prepare($SQL);
            $stmt->execute([password_hash($pass, PASSWORD_DEFAULT), $user]);
        } else {
            $SQL = "INSERT INTO tbl_users (username, password) VALUES (?, ?)";
            $stmt = $db->pdo->prepare($SQL);
            $stmt->execute([$user, password_hash($pass, PASSWORD_DEFAULT)]);
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        return 1;
    }

    return 0;
}

exit(main($argv));

?>
