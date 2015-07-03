<?php
require_once 'includes/bootstrap.php';

assert_access(SECTION_SYNTHESIZE);

$issue_id = isset($_REQUEST['issue']) ? $_REQUEST['issue'] : null;
$max_statement_id = isset($_REQUEST['statement']) ? intval($_REQUEST['statement']) : 0;

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

function check_issue_exists($issue_id) {
	return (!is_null($issue_id)
		&& !is_null(Utils::get_issue($issue_id)));
}

$reply = (object) array(
	'success' => true,
	'message' => 'OK',
);
try {
	switch ($action) {
		case 'get_issues':
			// get issues for frontpage
			$stmt = db()->preparedStatement("SELECT IssueId, Title FROM `%table` WHERE Frontpage = 1 ORDER BY Title", array('%table' => TABLE_ISSUES));
			if ($stmt->success) {
				$issues = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
				$reply->data = array_map('reset', $issues);
			} else {
				$reply->success = false;
				$reply->message = 'Could not get Issues';
			}
			break;
		case 'get_statements':
			if (check_issue_exists($issue_id)) {
				$stmt = db()->preparedStatement(
					'SELECT StatementId, Statement FROM `%table` WHERE IssueId = :issue_id AND StatementId > :statement_id ORDER BY StatementId ASC',
					array('%table' => TABLE_STATEMENTS, ':issue_id' => $issue_id, ':statement_id' => $max_statement_id)
				);
				if ($stmt->success) {
					$statements = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
					$reply->data = array_map('reset', $statements);
				} else {
					$reply->success = false;
					$reply->message = 'Could not get Statements';
				}
				break;
			} else {
				$reply->success = false;
				$reply->message = 'Prerequisites not met';
			}
			break;
		default:
			throw new Exception('action not found');
			break;
	}
}
catch (Exception $e) {
    header('HTTP/1.0 400 Bad Request');
    print($e->getMessage());
    exit(0);
}
header('Expires: Sun, 01 Jan 2010 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: application/json');
print(json_encode($reply));