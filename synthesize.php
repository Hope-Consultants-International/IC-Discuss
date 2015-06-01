<?php
require_once 'includes/bootstrap.php';

assert_access(SECTION_SYNTHESIZE);

$issue_id = isset($_REQUEST['issue']) ? $_REQUEST['issue'] : null;

$s_issues = db()->preparedStatement(
	"SELECT IssueId, Title FROM `%table` ORDER BY Title",
	array('%table' => TABLE_ISSUES)
);
$issues = $s_issues->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
$issues = array_map('reset', $issues);

if (is_null($issue_id)) {
	$issue_id = key($issues);
}

// Summaries
$summaries = array();
$s_summaries = db()->preparedStatement(
	"SELECT SummaryId, Summary	FROM `%table` WHERE IssueId = :issue_id ORDER BY Summary",
	array('%table' => TABLE_SUMMARIES, ':issue_id' => $issue_id)
);
while ($summary = $s_summaries->fetchObject()) {
	$s_statements = db()->preparedStatement(
		"SELECT s.StatementId, s.StatementId, s.Statement, g.Name as GroupName
			FROM `%stable` s JOIN `%gtable` g on s.GroupId = g.GroupId
			WHERE s.SummaryId = :id
			ORDER BY g.Name, s.Statement",
		array('%stable' => TABLE_STATEMENTS, '%gtable' => TABLE_GROUPS, ':id' => $summary->SummaryId)
	);
	$statements = $s_statements->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_CLASS);
	$summary->statements = array_map('reset', $statements);
	$summaries[] = $summary;
}

// unbound statements
$s_statements = db()->preparedStatement(
	"SELECT s.StatementId, s.StatementId, s.Statement, g.Name as GroupName
		FROM `%stable` s JOIN `%gtable` g on s.GroupId = g.GroupId
		WHERE s.SummaryId IS NULL AND IssueId = :issue_id
		ORDER BY g.Name, s.Statement",
	array('%stable' => TABLE_STATEMENTS, '%gtable' => TABLE_GROUPS, ':issue_id' => $issue_id)
);
$statements = $s_statements->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_CLASS);
$statements = array_map('reset', $statements);

$script_vars = array(
	'handler_url' => BASE_URL . 'synthesize_callback.php',
	'debug' => DEBUG,
);
$script = template_engine()->render('synthesize.tpl.js', $script_vars);

$vars = array(
	'issue_id' => $issue_id,
	'issue_title' => $issues[$issue_id],
	'summaries' => $summaries,
	'statements' => $statements,
	'script' => $script,
);
display(APP_TITLE, 'Synthesize|' . $issue_id, 'synthesize.tpl.php', $vars);