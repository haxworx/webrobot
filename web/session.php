<?php

require 'lib/project.php';
require 'lib/Session.php';

$session = new Session;
if (!$session->authorized()) {
    $session->destroy();
    http_response_code(500);
    exit(1);
}

$session->startExtend();

?>
