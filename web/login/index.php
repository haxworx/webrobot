<?php

require_once '../lib/project.php';
require_once 'lib/Database.php';
require_once 'lib/Session.php';
require_once 'lib/Twig.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $template = $twig->load('login.html.twig');
    echo $template->render([]);
    exit(0);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ((!isset($_POST['username'])) || (!isset($_POST['password']))) {
        header("Location: /login/");
        exit(1);
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

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
}

?>
