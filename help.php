<?php
require_once 'includes/bootstrap.php';

$title = 'Help';
$vars = array(
	'title' => $title,
);
display(APP_TITLE . ' - Help', 'Help', 'help.tpl.php', $vars);