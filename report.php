<?php
require_once 'includes/bootstrap.php';

assert_access(SECTION_SYNTHESIZE);

$report_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'normal';

$vars=array();
switch ($report_type) {
	case 'issues_detail':
	case 'issues_short':
		$issues = array();
		$s_issues = db()->preparedStatement(
			"SELECT IssueId, Title, Description FROM `%table` ORDER BY Title",
			array('%table' => TABLE_ISSUES)
		);
		while ($issue = $s_issues->fetchObject()) {
			
			// get summaries
			$summaries = array();
			$s_summaries = db()->preparedStatement(
				"SELECT su.SummaryId, su.Summary, COUNT(DISTINCT st.GroupId) as NumGroups
					FROM `%sutable` su
						JOIN `%sttable` st ON su.SummaryId = st.SummaryId
					WHERE su.IssueId = :id
					GROUP BY su.SummaryId
					ORDER BY COUNT(DISTINCT st.GroupId) DESC, COUNT(*) DESC, su.Summary",
				array('%sutable' => TABLE_SUMMARIES, '%sttable' => TABLE_STATEMENTS, ':id' => $issue->IssueId)
			);
			while ($summary = $s_summaries->fetchObject()) {
				$statements = array();
				$s_statements = db()->preparedStatement(
					"SELECT s.StatementId, s.Statement, s.GroupId, g.Name as GroupName
						FROM `%stable` s JOIN `%gtable` g ON s.GroupId = g.GroupId
						WHERE SummaryId = :id
						ORDER BY g.Name, s.Statement",
					array('%stable' => TABLE_STATEMENTS, '%gtable' => TABLE_GROUPS, ':id' => $summary->SummaryId)
				);
				while ($statement = $s_statements->fetchObject()) {
					$statements[] = $statement;
				}
				$summary->statements = $statements;
				$summaries[] = $summary;
			}
			
			// get unassigned statements
			$s_statements = db()->preparedStatement(
				"SELECT s.StatementId, s.Statement, s.GroupId, g.Name as GroupName
					FROM `%stable` s JOIN `%gtable` g ON s.GroupId = g.GroupId
					WHERE IssueId = :id AND s.SummaryId IS NULL
					ORDER BY g.Name, s.Statement",
				array('%stable' => TABLE_STATEMENTS, '%gtable' => TABLE_GROUPS, ':id' => $issue->IssueId)
			);
			while ($statement = $s_statements->fetchObject()) {
				$summary = (object) array(
					'SummaryId' => null,
					'Summary' => $statement->Statement,
					'statements' => array($statement),
					'NumGroups' => 1,
				);
				$summaries[] = $summary;
			}
			
			$issue->summaries = $summaries;
			$issues[] = $issue;
		}
		$vars['issues'] = $issues;
		break;
	case 'groups':
		$s_issues = db()->preparedStatement(
			"SELECT IssueId, Title, Description FROM `%table` ORDER BY Title",
			array('%table' => TABLE_ISSUES)
		);
		$issues = $s_issues->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_CLASS);
		$issues = array_map('reset', $issues);
	
		$groups = array();
		$s_groups = db()->preparedStatement(
			"SELECT GroupId, Name FROM `%table`
				ORDER BY Name",
			array('%table' => TABLE_GROUPS)
		);
		while ($group = $s_groups->fetchObject()) {
			$group_issues = array();
			foreach ($issues as $issue_id => $issue) {
				$group_issue = clone $issue;
				$statements = array();
				$s_statements = db()->preparedStatement(
					"SELECT StatementId, Statement FROM `%table`
						WHERE IssueId = :iid AND GroupId = :gid
						ORDER BY Statement",
					array('%table' => TABLE_STATEMENTS, ':iid' => $issue_id, ':gid' => $group->GroupId)
				);
				while ($statement = $s_statements->fetchObject()) {
					$statements[] = $statement;
				}
				$group_issue->statements = $statements;
				$group_issues[] = $group_issue;
			}
			$group->issues = $group_issues;
			$groups[] = $group;
		}
		$vars['groups'] = $groups;
		break;
	default:
		die('Unknown Report Type');
		break;
}
display(APP_TITLE . ' - Report', 'Report|' . $report_type, 'report_' . $report_type . '.tpl.php', $vars);