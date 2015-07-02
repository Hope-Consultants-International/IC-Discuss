<?php
require_once 'includes/bootstrap.php';

function _requestOrDefault($parameter, $default = '', $null_value = null) {
	$value = (isset($_REQUEST[$parameter])) ? $_REQUEST[$parameter] : $default;
	if ($value === $null_value) {
		$value = null;
	}
	return $value;
}

$group_id = null;
$issues = array();

// get default group to make sure we can do this
$stmt = db()->preparedStatement(
	"SELECT GroupId FROM `%table` WHERE Frontpage = 1",
	array('%table' => TABLE_GROUPS)
);
if ($stmt->foundRows == 1) {
	$group_id = $stmt->fetchColumn();

	// get issues for frontpage
	$stmt = db()->preparedStatement("SELECT IssueId, Title FROM `%table` WHERE Frontpage = 1 ORDER BY Title", array('%table' => TABLE_ISSUES));
	if ($stmt->success) {
		while ($issue = $stmt->fetchObject()) {
			$issues[$issue->IssueId] = $issue->Title;
		}
	}
}

// get issue id
$issue_id = _requestOrDefault('IssueId', null);
if (!array_key_exists($issue_id, $issues)) {
	$issue_id = null;
}
	
$statement = _requestOrDefault('Statement');
if (!is_null($issue_id) && !is_null($group_id) && !empty($statement)) {
	$stmt = db()->preparedStatement(
		"INSERT INTO `%table` SET GroupId = :group, IssueId = :issue, Statement = :statement",
		array(
			'%table' => TABLE_STATEMENTS,
			':group' => $group_id,
			':issue' => $issue_id,
			':statement' => $statement,
		)
	);
	if (!$stmt->success) {
		set_message('Could not add statement.', MSG_TYPE_ERR);
	} else {
		set_message('Statement added.', MSG_TYPE_INFO);
	}
}

$title = 'Home';
$vars = array(
	'title' => $title,
	'issues' => $issues,
	'last_issue' => $issue_id,
);
display(APP_TITLE . ' - Home', 'Home', 'index.tpl.php', $vars);