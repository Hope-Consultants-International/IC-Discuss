<?php
require_once 'includes/bootstrap.php';

assertAccess(SECTION_SYNTHESIZE);

$issue_id = Utils::requestOrDefault('issue', null);

$s_issues = db()->preparedStatement(
	"SELECT IssueId, Title FROM `%table` ORDER BY Title",
	array('%table' => TABLE_ISSUES)
);
$issues = $s_issues->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
$issues = array_map('reset', $issues);

if (is_null($issue_id) || !array_key_exists($issue_id, $issues)) {
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
		"SELECT s.StatementId, s.StatementId, s.Statement, s.Highlight, g.Name as GroupName, s.Weight
			FROM `%stable` s JOIN `%gtable` g on s.GroupId = g.GroupId
			WHERE s.SummaryId = :id
			ORDER BY s.Highlight DESC, g.Name, s.Statement",
		array('%stable' => TABLE_STATEMENTS, '%gtable' => TABLE_GROUPS, ':id' => $summary->SummaryId)
	);
	$statements = $s_statements->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_CLASS);
	$summary->statements = array_map('reset', $statements);
	$summaries[] = $summary;
}

// unbound statements
$s_statements = db()->preparedStatement(
	"SELECT s.StatementId, s.StatementId, s.Statement, s.Highlight, g.Name as GroupName, s.Weight
		FROM `%stable` s JOIN `%gtable` g on s.GroupId = g.GroupId
		WHERE s.SummaryId IS NULL AND IssueId = :issue_id
		ORDER BY g.Name, s.Statement",
	array('%stable' => TABLE_STATEMENTS, '%gtable' => TABLE_GROUPS, ':issue_id' => $issue_id)
);
$statements = $s_statements->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_CLASS);
$statements = array_map('reset', $statements);

$script = <<<EOJS
	const debug = %s;
	const ajaxHandlerURL = '%s';
EOJS;
$script = sprintf($script, ((DEBUG) ? 'true' : 'false' ), htmlentities(BASE_URL . 'synthesize_callback.php'));

$vars = array(
	'issue_id' => $issue_id,
	'issue_title' => $issues[$issue_id],
	'summaries' => $summaries,
	'statements' => $statements,
	'script' => $script,
);
display(APP_TITLE, 'Synthesize|' . $issue_id, 'synthesize.tpl.php', $vars);
