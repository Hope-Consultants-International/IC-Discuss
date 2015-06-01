<?php
require_once 'includes/bootstrap.php';

assert_access(SECTION_MANAGE);

function _requestOrDefault($parameter, $default = '', $null_value = null) {
	$value = (isset($_REQUEST[$parameter])) ? $_REQUEST[$parameter] : $default;
	if ($value === $null_value) {
		$value = null;
	}
	return $value;
}

$issue_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : NEW_ENTRY_ID;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';
$page_url = strtok($_SERVER["REQUEST_URI"], '?');

// new issue?
$new_issue = false;
if ($issue_id == NEW_ENTRY_ID) {
	$new_issue = true;
}

switch ($action) {
	case 'delete':
		$query = "DELETE FROM `%table` WHERE IssueId = :id";
		$values = array(
			'%table' => TABLE_ISSUES,
			':id' => $issue_id,
		);
		$stmt = db()->preparedStatement($query, $values);
		if (!$stmt->success) {
			die('Database delete fail: ' . $stmt->error);
		}
		header('Location: ' . $page_url . '?action=list', true, 302);
		break;
	case 'save':
		// get values for issue table
		$values = array(
			'%table' => TABLE_ISSUES,
			':title' => _requestOrDefault('IssueTitle'),
			':description' => _requestOrDefault('IssueDescription'),
			':upload' => (_requestOrDefault('AllowUpload', false) ? 1 : 0),
		);
		if ($new_issue) {
			$query = "INSERT INTO `%table` SET Title = :title, Description = :description, AllowUpload = :upload";
		} else {
			$query = "UPDATE `%table` SET Title = :title, Description = :description, AllowUpload = :upload WHERE IssueId = :id";
			$values[':id'] = $issue_id;
		}
		$stmt = db()->preparedStatement($query, $values);
		if (!$stmt->success) {
			die('Database update fail: ' . $stmt->error);
		} elseif ($new_issue) {
			$issue_id = $stmt->lastInsertId;
		}
		header('Location: ' . $page_url . '?action=list', true, 302);
		break;
	case 'edit':
			if ($new_issue) {
				$title = 'New Issue';
				$issue_title = '';
				$issue_description= '';
				$issue_allow_upload = true;
			} else {
				$query = "SELECT Title, Description, AllowUpload FROM `%table` WHERE IssueId = :id";
				$values = array('%table' => TABLE_ISSUES, ':id' => $issue_id);
				$stmt = db()->preparedStatement($query, $values);
				if ($stmt->foundRows == 1) {
					$issue = $stmt->fetchObject();
					$issue_title = $issue->Title;
					$issue_description = $issue->Description;
					$issue_allow_upload = $issue->AllowUpload;
				} else {
					die('Issue not found: ' . $issue_id);
				}
				$title = 'Edit Issue "' . htmlentities($issue_title) . '"';
			}
			$vars = array(
				'issue_id' => $issue_id,
				'issue_title' => $issue_title,
				'issue_description' => $issue_description,
				'issue_upload' => $issue_allow_upload,
				'page_url' => $page_url,
				'title' => $title,
			);
			display(APP_TITLE, 'Manage|Issues', 'issues_edit.tpl.php', $vars);
		break;
	case 'list':
			$issues = array();
			$query = "SELECT IssueId, Title, Description, AllowUpload FROM `%table`";
			$values = array('%table' => TABLE_ISSUES);
			$stmt = db()->preparedStatement($query, $values);
			while ($issue = $stmt->fetchObject()) {
				$issues[$issue->IssueId] = $issue;
			}
			$vars = array(
				'issues' => $issues,
				'page_url' => $page_url,
			);
			display(APP_TITLE, 'Manage|Issues', 'issues_list.tpl.php', $vars);
		break;
	default:
		die('Unknown action: ' . htmlentities($action));
		break;
}