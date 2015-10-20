<?php
/**
IC-Discuss (c) 2015 by Hope Consultants International Ltd.

IC-Discuss is licensed under a
Creative Commons Attribution-ShareAlike 4.0 International License.

You should have received a copy of the license along with this
work.  If not, see <http://creativecommons.org/licenses/by-sa/4.0/>.
**/
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
		'CREATE TABLE IF NOT EXISTS `' . TABLE_GROUPS . '` (
			`GroupId` int(11) NOT NULL, `Name` varchar(255) NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8',
		'CREATE TABLE IF NOT EXISTS `' . TABLE_ISSUES . '` (
			`IssueId` int(11) NOT NULL, `Title` varchar(255) NOT NULL,
			`Description` varchar(255) NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8',
		'CREATE TABLE IF NOT EXISTS `' . TABLE_STATEMENTS . '` (
			`StatementId` int(11) NOT NULL, `GroupId` int(11) NOT NULL,
			`IssueId` int(11) NOT NULL, `SummaryId` int(11) DEFAULT NULL,
			`Statement` text NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8',
		'CREATE TABLE IF NOT EXISTS `' . TABLE_SUMMARIES . '` (
			`SummaryId` int(11) NOT NULL,
			`IssueId` int(11) NOT NULL,
			`Summary` text NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8',
		'ALTER TABLE `' . TABLE_GROUPS . '` ADD PRIMARY KEY (`GroupId`), ADD UNIQUE KEY `Name` (`Name`)',
		'ALTER TABLE `' . TABLE_ISSUES . '` ADD PRIMARY KEY (`IssueId`), ADD UNIQUE KEY `Title` (`Title`)',
		'ALTER TABLE `' . TABLE_STATEMENTS . '` ADD PRIMARY KEY (`StatementId`), ADD KEY `GroupId` (`GroupId`), ADD KEY `IssueId` (`IssueId`), ADD KEY `SummaryId` (`SummaryId`)',
		'ALTER TABLE `' . TABLE_SUMMARIES . '` ADD PRIMARY KEY (`SummaryId`), ADD KEY `IssueId` (`IssueId`)',
		'ALTER TABLE `' . TABLE_GROUPS . '` MODIFY `GroupId` int(11) NOT NULL AUTO_INCREMENT',
		'ALTER TABLE `' . TABLE_ISSUES . '` MODIFY `IssueId` int(11) NOT NULL AUTO_INCREMENT',
		'ALTER TABLE `' . TABLE_STATEMENTS . '` MODIFY `StatementId` int(11) NOT NULL AUTO_INCREMENT',
		'ALTER TABLE `' . TABLE_SUMMARIES . '` MODIFY `SummaryId` int(11) NOT NULL AUTO_INCREMENT',
		'ALTER TABLE `' . TABLE_STATEMENTS . '`
			ADD CONSTRAINT `statements_ibfk_3` FOREIGN KEY (`SummaryId`) REFERENCES `' . TABLE_SUMMARIES . '` (`SummaryId`) ON DELETE SET NULL,
			ADD CONSTRAINT `statements_ibfk_1` FOREIGN KEY (`GroupId`) REFERENCES `' . TABLE_GROUPS . '` (`GroupId`) ON DELETE CASCADE,
			ADD CONSTRAINT `statements_ibfk_2` FOREIGN KEY (`IssueId`) REFERENCES `' . TABLE_ISSUES . '` (`IssueId`) ON DELETE CASCADE',
		'ALTER TABLE `' . TABLE_SUMMARIES . '`
			ADD CONSTRAINT `summaries_ibfk_1` FOREIGN KEY (`IssueId`) REFERENCES `' . TABLE_ISSUES . '` (`IssueId`) ON DELETE CASCADE',
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
	$sql = "ALTER TABLE `" . TABLE_ISSUES . "` ADD `AllowUpload` BOOLEAN NOT NULL DEFAULT TRUE";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `" . TABLE_STATEMENTS . "` ADD `Highlight` BOOLEAN NOT NULL DEFAULT FALSE";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `" . TABLE_STATEMENTS . "` ADD `Weight` INT NOT NULL DEFAULT '0'";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `" . TABLE_ISSUES . "` ADD `Frontpage` TINYINT NOT NULL DEFAULT '0', ADD INDEX (`Frontpage`) ;";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `" . TABLE_GROUPS . "` ADD `Frontpage` TINYINT NOT NULL DEFAULT '0', ADD INDEX (`Frontpage`) ;";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `" . TABLE_ISSUES . "` ADD `Folder` VARCHAR(250) NOT NULL DEFAULT ''";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `" . TABLE_STATEMENTS . "` ADD `ParentStatementId` INT NULL , ADD INDEX (`ParentStatementId`);";
	$s = db()->preparedStatement($sql);
	if (!$s->success) {
		die(sprintf('Error updating to version %d:<p><b>Query</b><br>%s</p>', $current_version, $sql));
	}
}

$current_version++;
if ($db_version < $current_version) {
	$sql = "ALTER TABLE `" . TABLE_STATEMENTS . "` ADD FOREIGN KEY (`ParentStatementId`) REFERENCES `" . TABLE_STATEMENTS . "` (`StatementId`) ON DELETE SET NULL ON UPDATE RESTRICT;";
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
