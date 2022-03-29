<?php

require '../lib/project.php';
require 'lib/Session.php';

$session = new Session;
$session->destroy();

header("Location: /login/");
?>
