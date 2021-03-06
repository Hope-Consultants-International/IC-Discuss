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

$issue_id = Utils::requestOrDefault('id', NEW_ENTRY_ID);
$action = Utils::requestOrDefault('action', 'list');
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
			':title' => Utils::requestOrDefault('IssueTitle'),
			':description' => Utils::requestOrDefault('IssueDescription'),
			':upload' => (Utils::requestOrDefault('AllowUpload', false) ? 1 : 0),
			':frontpage' => (Utils::requestOrDefault('Frontpage', false) ? 1 : 0),
			':folder' => Utils::requestOrDefault('Folder'),
		);
		if ($new_issue) {
			$query = "
                INSERT INTO `%table` SET
                    Title = :title,
                    Description = :description,
                    AllowUpload = :upload,
                    Frontpage = :frontpage,
                    Folder = :folder";
		} else {
			$query = "
                UPDATE `%table` SET
                    Title = :title,
                    Description = :description,
                    AllowUpload = :upload,
                    Frontpage = :frontpage,
                    Folder = :folder
                WHERE IssueId = :id";
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
			$issue_frontpage = false;
			$issue_folder = '';
		} else {
			$query = "SELECT Title, Description, AllowUpload, Frontpage, Folder FROM `%table` WHERE IssueId = :id";
			$values = array('%table' => TABLE_ISSUES, ':id' => $issue_id);
			$stmt = db()->preparedStatement($query, $values);
			if ($stmt->foundRows == 1) {
				$issue = $stmt->fetchObject();
				$issue_title = $issue->Title;
				$issue_description = $issue->Description;
				$issue_allow_upload = $issue->AllowUpload;
				$issue_frontpage = $issue->Frontpage;
				$issue_folder = $issue->Folder;
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
			'issue_frontpage' => $issue_frontpage,
			'issue_folder' => $issue_folder,
			'page_url' => $page_url,
			'title' => $title,
		);
		display(APP_TITLE, 'Manage|Issues', 'issues_edit.tpl.php', $vars);
		break;
	case 'list':
		$issues = array();
		$query = "SELECT COUNT(s.StatementId) AS StatementCount, i.IssueId, i.Title, i.Description, i.AllowUpload, i.Frontpage
			FROM `%itable` i LEFT JOIN `%stable` s ON i.IssueId = s.IssueId
			GROUP BY i.IssueId
			ORDER BY i.Title";
		$values = array('%itable' => TABLE_ISSUES, '%stable' => TABLE_STATEMENTS);
		$stmt = db()->preparedStatement($query, $values);
		while ($issue = $stmt->fetchObject()) {
			$issues[$issue->IssueId] = $issue;
		}
		$vars = array(
			'issues' => $issues,
			'page_url' => $page_url,
			'download_url' => BASE_URL . 'download_template.php',
		);
		display(APP_TITLE, 'Manage|Issues', 'issues_list.tpl.php', $vars);
		break;
	case 'clear-statements':
		$s = db()->preparedStatement(
			"DELETE FROM `%table` WHERE IssueId = :issue_id",
			array('%table' => TABLE_STATEMENTS, ':issue_id' => $issue_id)
		);
		if (!$s->success) {
			die('Could not clear statements: ' . $s->error);
		}
		header('Location: ' . $page_url . '?action=list', true, 302);
		break;
	default:
		die('Unknown action: ' . htmlentities($action));
		break;
}