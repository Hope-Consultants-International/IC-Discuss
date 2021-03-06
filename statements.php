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

$statement_id = Utils::requestOrDefault('id', NEW_ENTRY_ID);
$action = Utils::requestOrDefault('action', 'list');
$page_url = strtok($_SERVER["REQUEST_URI"], '?');

// new statement?
$new_statement = false;
if ($statement_id == NEW_ENTRY_ID) {
	$new_statement = true;
}

switch ($action) {
	case 'delete':
		// check if there are child statements
		$q_check = "SELECT COUNT(*) FROM `%table` WHERE ParentStatementId = :id";
		$values = array(
			'%table' => TABLE_STATEMENTS,
			':id' => $statement_id,
		);
		$stmt_check = db()->preparedStatement($q_check, $values);
		if ($stmt_check->fetchColumn(0) > 0) {
			$query = "DELETE FROM `%table` WHERE ParentStatementId = :id LIMIT 1";
		} else {
			$query = "DELETE FROM `%table` WHERE StatementId = :id";
		}
		$stmt = db()->preparedStatement($query, $values);
		if (!$stmt->success) {
			die('Database delete fail: ' . $stmt->error);
		}
		header('Location: ' . $page_url . '?action=list', true, 302);
		break;
	case 'save':
		// get values for statement table
		$values = array(
			'%table' => TABLE_STATEMENTS,
			':statement' => Utils::requestOrDefault('Statement'),
			':group_id' => Utils::requestOrDefault('GroupId'),
			':issue_id' => Utils::requestOrDefault('IssueId'),
			':weight' => Utils::requestOrDefault('Weight', 0),
		);
		if ($new_statement) {
			$query = "
                INSERT INTO `%table` SET
                    Statement = :statement,
                    GroupId = :group_id,
                    IssueId = :issue_id,
                    Weight = :weight";
		} else {
			$query = "
                UPDATE `%table` SET
                    Statement = :statement,
                    GroupId = :group_id,
                    IssueId = :issue_id,
                    Weight = :weight
                WHERE (StatementId = :id) OR (ParentStatementId = :id)";
			$values[':id'] = $statement_id;
		}
		$stmt = db()->preparedStatement($query, $values);
		if (!$stmt->success) {
			die('Database update fail: ' . $stmt->error);
		} elseif ($new_statement) {
			$statement_id = $stmt->lastInsertId;
		}
		header('Location: ' . $page_url . '?action=list', true, 302);
		break;
	case 'edit':
		$s_groups = db()->preparedStatement(
			"SELECT GroupId, Name FROM `%table` ORDER BY Name",
			array('%table' => TABLE_GROUPS)
		);
		$groups = $s_groups->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
		$groups = array_map('reset', $groups);

		$s_issues = db()->preparedStatement(
			"SELECT IssueId, Title FROM `%table` ORDER BY Title",
			array('%table' => TABLE_ISSUES)
		);
		$issues = $s_issues->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
		$issues = array_map('reset', $issues);

		if ($new_statement) {
			$title = 'New Statement';
			$statement = '';
			$group_id = null;
			$issue_id = null;
			$weight = 0;
		} else {
			$query = "SELECT Statement, GroupId, IssueId, Weight FROM `%table` WHERE StatementId = :id";
			$values = array('%table' => TABLE_STATEMENTS, ':id' => $statement_id);
			$stmt = db()->preparedStatement($query, $values);
			if ($stmt->foundRows == 1) {
				$obj = $stmt->fetchObject();
				$statement = $obj->Statement;
				$group_id = $obj->GroupId;
				$issue_id = $obj->IssueId;
				$weight = $obj->Weight;
			} else {
				die('Statement not found: ' . $statement_id);
			}
			$title = 'Edit Statement ID ' . htmlentities($statement_id);
		}
		$vars = array(
			'statement_id' => $statement_id,
			'statement' => $statement,
			'group_id' => $group_id,
			'groups' => $groups,
			'issue_id' => $issue_id,
			'issues' => $issues,
			'weight' => $weight,
			'page_url' => $page_url,
			'title' => $title,
		);
		display(APP_TITLE, 'Manage|Statements', 'statements_edit.tpl.php', $vars);
		break;
	case 'list':
		$statements = array();
		$values = array('%table' => TABLE_ISSUES);
		$stmt = db()->preparedStatement(
			"SELECT
                s.StatementId,
                s.GroupId,
                s.IssueId,
                i.Title AS IssueTitle,
                g.Name AS GroupName,
                s.Statement,
                s.Weight,
				(SELECT COUNT(*) FROM `%stable` s2 WHERE s2.ParentStatementId = s.StatementId) AS ChildStatements
			FROM `%stable` s
					JOIN `%gtable` g ON s.GroupId = g.GroupId
					JOIN `%itable` i ON s.IssueId = i.IssueId
			WHERE ISNULL(s.ParentStatementId)
			ORDER BY i.Title, g.Name, s.Statement",
			array(
				'%stable' => TABLE_STATEMENTS,
				'%gtable' => TABLE_GROUPS,
				'%itable' => TABLE_ISSUES,
			)
		);
		if ($stmt->success) {
			while ($statement = $stmt->fetchObject()) {
				$statements[$statement->StatementId] = $statement;
			}
		} else {
			die('Database query failed: ' . $stmt->error);
		}
		$vars = array(
			'statements' => $statements,
			'page_url' => $page_url,
		);
		display(APP_TITLE, 'Manage|Statements', 'statements_list.tpl.php', $vars);
		break;
	default:
		die('Unknown action: ' . htmlentities($action));
		break;
}
