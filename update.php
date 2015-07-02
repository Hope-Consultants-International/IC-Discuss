<?php
require_once 'includes/bootstrap.php';

assert_access(SECTION_MANAGE);

?><!DOCTYPE html>
<html><head><title>DB Update</title></head>
<body><pre>
<?php

// check if update table exists and create if necessary
$s = db()->preparedStatement(
	'CREATE TABLE IF NOT EXISTS `db_version` (
		`version` int(11) NOT NULL DEFAULT 1
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;'
);

// get current db version
$db_version = 1;
$current_version = 1;
$s = db()->preparedStatement('SELECT version FROM `db_version` LIMIT 1');
if (!$db_version = $s->fetchColumn(0)) {
	$s = db()->preparedStatement('INSERT INTO `db_version` SET version = 1');
	$db_version = 1;
}
printf("DB at version %d\n", $db_version);

// version 1 of the database was the version from 2015-05-22

/***
// this is an example update block
$current_version++;
if ($db_version < $current_version) {
	$sql = "SELECT 1; -- some statement";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}
*/

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `issues` ADD `AllowUpload` BOOLEAN NOT NULL DEFAULT TRUE";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `statements` ADD `Highlight` BOOLEAN NOT NULL DEFAULT FALSE";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `statements` ADD `Weight` INT NOT NULL DEFAULT '0'";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `issues` ADD `Frontpage` TINYINT NOT NULL DEFAULT '0', ADD INDEX (`Frontpage`) ;";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

// ---- NEW UPDATES GO HERE ----

// save new version to database	
if ($db_version != $current_version) {
	$s = db()->preparedStatement('UPDATE `db_version` SET version = :version', array(':version' => $current_version));
	if ($s->success) {
		printf("DB updated to version %d\n", $current_version);
	}
} else {
	print("No update needed\n");
}
?>
</pre>
<a href="index.php">Back</a>
</body></html>