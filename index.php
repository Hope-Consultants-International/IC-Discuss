<?php
require_once 'includes/bootstrap.php';

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
		$issues = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
		$issues = array_map('reset', $issues);
	}
}

// get issue id
$issue_id = Utils::requestOrDefault('issue_id', null);
if (!array_key_exists($issue_id, $issues)) {
	$issue_id = null;
}

$action = Utils::requestOrDefault('action', 'display');
switch ($action) {
	case 'get_issues':
		$reply = (object) array(
			'success' => true,
			'message' => 'OK',
			'data' => $issues,
		);
		header('Expires: Sun, 01 Jan 2010 00:00:00 GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
		header('Content-Type: application/json');
		print(json_encode($reply));
		break;
	case 'add_statement':
		$statement = Utils::requestOrDefault('statement');
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
				setMessage('Could not add statement.', MSG_TYPE_ERR);
			} else {
				setMessage('Statement added.', MSG_TYPE_INFO);
			}
		}
	case 'display':
	default:
		$script = <<<EOJS
const debug = %s;
const ajaxHandlerURL = '%s';

var issue_id = %s;
EOJS;
		$script = sprintf($script, ((DEBUG) ? 'true' : 'false' ), htmlentities(BASE_URL . 'index.php'), (is_null($issue_id) ? 0 : $issue_id));

		$title = 'Home';
		$vars = array(
			'title' => $title,
			'script' => $script,
		);
		display(APP_TITLE . ' - Home', 'Home', 'index.tpl.php', $vars);
		break;
}
