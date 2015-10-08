<?php
require_once 'includes/bootstrap.php';

assertAccess(SECTION_MANAGE);

?><!DOCTYPE html>
<html><head><title>DB Update</title></head>
<body>
<h1>DB Update</h1>
<pre>
<?php

// check if update table exists and create if necessary
$s = db()->preparedStatement(
	'CREATE TABLE IF NOT EXISTS `db_version` (
		`version` int(11) NOT NULL DEFAULT 1
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;'
);

// get current db version
$db_version = 0;
$current_version = 0;
$s = db()->preparedStatement('SELECT version FROM `db_version` LIMIT 1');
if (!$db_version = $s->fetchColumn(0)) {
	$s = db()->preparedStatement('INSERT INTO `db_version` SET version = 0');
	$db_version = 0;
}
printf("DB at version %d\n", $db_version);

// version 1 of the database
$current_version++;
if ($db_version < $current_version) {
	// create database
	$statements = array(
		'CREATE TABLE IF NOT EXISTS `groups` (
			`GroupId` int(11) NOT NULL, `Name` varchar(255) NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8',
		'CREATE TABLE IF NOT EXISTS `issues` (
			`IssueId` int(11) NOT NULL, `Title` varchar(255) NOT NULL,
			`Description` varchar(255) NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8',
		'CREATE TABLE IF NOT EXISTS `statements` (
			`StatementId` int(11) NOT NULL, `GroupId` int(11) NOT NULL,
			`IssueId` int(11) NOT NULL, `SummaryId` int(11) DEFAULT NULL,
			`Statement` text NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8',
		'CREATE TABLE IF NOT EXISTS `summaries` (
			`SummaryId` int(11) NOT NULL,
			`IssueId` int(11) NOT NULL,
			`Summary` text NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8',
		'ALTER TABLE `groups` ADD PRIMARY KEY (`GroupId`), ADD UNIQUE KEY `Name` (`Name`)',
		'ALTER TABLE `issues` ADD PRIMARY KEY (`IssueId`), ADD UNIQUE KEY `Title` (`Title`)',
		'ALTER TABLE `statements` ADD PRIMARY KEY (`StatementId`), ADD KEY `GroupId` (`GroupId`), ADD KEY `IssueId` (`IssueId`), ADD KEY `SummaryId` (`SummaryId`)',
		'ALTER TABLE `summaries` ADD PRIMARY KEY (`SummaryId`), ADD KEY `IssueId` (`IssueId`)',
		'ALTER TABLE `groups` MODIFY `GroupId` int(11) NOT NULL AUTO_INCREMENT',
		'ALTER TABLE `issues` MODIFY `IssueId` int(11) NOT NULL AUTO_INCREMENT',
		'ALTER TABLE `statements` MODIFY `StatementId` int(11) NOT NULL AUTO_INCREMENT',
		'ALTER TABLE `summaries` MODIFY `SummaryId` int(11) NOT NULL AUTO_INCREMENT',
		'ALTER TABLE `statements`
			ADD CONSTRAINT `statements_ibfk_3` FOREIGN KEY (`SummaryId`) REFERENCES `summaries` (`SummaryId`) ON DELETE SET NULL,
			ADD CONSTRAINT `statements_ibfk_1` FOREIGN KEY (`GroupId`) REFERENCES `groups` (`GroupId`) ON DELETE CASCADE,
			ADD CONSTRAINT `statements_ibfk_2` FOREIGN KEY (`IssueId`) REFERENCES `issues` (`IssueId`) ON DELETE CASCADE',
		'ALTER TABLE `summaries`
			ADD CONSTRAINT `summaries_ibfk_1` FOREIGN KEY (`IssueId`) REFERENCES `issues` (`IssueId`) ON DELETE CASCADE',
	);
	foreach ($statements as $sql) {
		$s = db()->preparedStatement($sql);
		if (!$s->success) {
			die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
		}
	}
}

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

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `groups` ADD `Frontpage` TINYINT NOT NULL DEFAULT '0', ADD INDEX (`Frontpage`) ;";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `issues` ADD `Folder` VARCHAR(250) NOT NULL DEFAULT ''";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `statements` ADD `ParentStatementId` INT NULL , ADD INDEX (`ParentStatementId`);";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `statements` ADD FOREIGN KEY (`ParentStatementId`) REFERENCES `ic-discuss`.`statements`(`StatementId`) ON DELETE SET NULL ON UPDATE RESTRICT;";
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
