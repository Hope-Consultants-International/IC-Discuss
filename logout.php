<?php
require_once 'includes/bootstrap.php';

$vars = array(
    'target' => BASE_URL . 'index.php',
    'temp_page' => BASE_URL . 'index.php',
);

display(APP_TITLE . ' - Logout', 'Logout', 'logout.tpl.php', $vars);
