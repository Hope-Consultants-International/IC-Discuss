<?php
require_once 'includes/bootstrap.php';

$title = 'Home';
$vars = array(
	'title' => $title,
);
display(APP_TITLE . ' - Home', 'Home', 'index.tpl.php', $vars);