<?php

require_once 'lib/project.php';
require_once 'lib/Session.php';

$session = Session::getInstance();
setcookie(session_name(), session_id(), time() + 3600);
$session->modified = time();
?>
