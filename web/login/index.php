<?php

require_once '../lib/project.php';
require_once 'lib/Database.php';
require_once 'lib/Session.php';
require_once 'lib/Twig.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $session = new Session;
    $session->start();
    $template = $twig->load('login.html.twig');
    echo $template->render(['token' => $session->getToken()]);
    exit(0);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    # Check our username, password and pre-authenticated session token.
    if ((!isset($_POST['username'])) || (!isset($_POST['password'])) || (!isset($_POST['token']))) {
        header("Location: /login/");
        exit(1);
    }

    $session = new Session;
    $session->start();
    if (!isset($session->attempts)) {
       $session->attempts = 1;
    } else {
       $session->attempts++;
    }

    if ($session->attempts > 10) {
        $template = $twig->load('login_errors.html.twig');
        echo $template->render(['message' => 'Too many authentication attempts, try again later.']);
        exit(1);
    }

    $username = $_POST['username'];
    # Ensure our username is sensible.
    if (!preg_match('/^[a-z0-9]{6,32}$/', $username)) {
        header("Location: /login/");
        exit(1);
    }
    # Ensure our password attempt is sensible.
    $password = $_POST['password'];
    if (!preg_match('/^[[:print:]]{8,255}$/', $password)) {
        header("Location: /login/");
        exit(1);
    }
    # Check our CSRF token.
    $token = $_POST['token'];
    if (($token !== $session->getToken())) {
        http_response_code(401);
        exit(1);
    }

    # Destroy our pre-authenticated session.
    $session->destroy();

    try {
        $db = new DB;
    } catch (Exception $e) {
        error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
        http_response_code(500);
        exit(1);
    }

    try {
        $SQL = "SELECT user_id, password FROM tbl_users WHERE username = ?";
        $stmt = $db->pdo->prepare($SQL);
        $stmt->execute([$username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (($row === false) || (!isset($row['user_id'])) || (!isset($row['password']))) {
            header("Location: /login/?failed=1");
            exit(1);
        }

        $user_id = $row['user_id'];
        $hash = $row['password'];

        if (password_verify($password, $hash)) {
            # We authenticated. Create our new authenticated session.
            $session = new Session;
            $session->authenticated($user_id);
            $hash = $password = null;
            header('Location: /');
        } else {
            $hash = null;
            header("Location: /login/?failed=1");
            exit(1);
        }
    } catch (Exception $e) {
        error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
        http_response_code(500);
        exit(1);
    }
} else {
    http_response_code(401);
}

?>
