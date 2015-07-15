<?php
require_once 'includes/bootstrap.php';

assertAccess(array(SECTION_SYNTHESIZE, SECTION_TICKER));

$issue_id = Utils::requestOrDefault('issue', 0);

$s_issues = db()->preparedStatement(
	"SELECT IssueId, Title FROM `%table` WHERE Frontpage = 1 ORDER BY Title",
	array('%table' => TABLE_ISSUES)
);
$issues = $s_issues->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
$issues = array_map('reset', $issues);

if (count($issues) > 0) {
	if (!array_key_exists($issue_id, $issues) && $issue_id != 0) {
		$issue_id = key($issues);
	}
} else {
	$issue_id = 0;
}

$script = <<<EOJS
	const debug = %s;
	const ajaxHandlerURL = '%s';
	
	var issue_id = %s;
EOJS;
$script = sprintf(
    $script,
    ((DEBUG) ? 'true' : 'false' ),
    htmlentities(BASE_URL . 'liveticker_callback.php'),
    $issue_id
);

$vars = array(
	'issue_id' => $issue_id,
	'script' => $script,
	'issues' => $issues,
);
display(APP_TITLE, 'Live-Ticker', 'liveticker.tpl.php', $vars);
