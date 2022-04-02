<?php

require '../lib/project.php';
require 'lib/Session.php';

# Extend our pre-auth and post-auth sessions.
$session = new Session;
if ($session->IsValid()) {
    $session->startExtend();
} else {
    $session->destroy();
    header("Location: /login/");
}

?>
