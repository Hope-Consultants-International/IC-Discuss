<?php
require_once 'includes/bootstrap.php';

assert_access(SECTION_SYNTHESIZE);

$issue_id = Utils::requestOrDefault('issue', null);
$statement_id = Utils::requestOrDefault('statement', null);
$summary_id = Utils::requestOrDefault('summary', null);
$summary_id_old = Utils::requestOrDefault('summary_old', null);

$action = Utils::requestOrDefault('action', null);

function check_statement_exists($statement_id) {
	return (!is_null($statement_id)
		&& !is_null(Utils::get_statement($statement_id)));
}

function check_summary_exists($summary_id) {
	return (!is_null($summary_id)
	  && !is_null(Utils::get_summary($summary_id)));
}

function check_statement_link($statement_id, $summary_id) {
	$statement = Utils::get_statement($statement_id);
	return (!is_null($statement)
	  && $statement->SummaryId == $summary_id);
}

$reply = (object) array(
	'success' => true,
	'message' => 'OK',
);
try {
	switch ($action) {
		case 'link':
			if (check_statement_exists($statement_id)
			  && check_summary_exists($summary_id)
			  && check_statement_link($statement_id, $summary_id_old)) {
				$s = db()->preparedStatement(
					'UPDATE `%table` SET SummaryId = :summary_id WHERE StatementId = :statement_id',
					array('%table' => TABLE_STATEMENTS, ':summary_id' => $summary_id, ':statement_id' => $statement_id)
				);
				if (!$s->success) {
					$reply->success = false;
					$reply->message = 'Could not link Statement';
				}
			} else {
				$reply->success = false;
				$reply->message = 'Prerequisites not met';
			}
			break;
		case 'unlink':
			if (check_statement_exists($statement_id)
			  && check_statement_link($statement_id, $summary_id_old)) {
				$s = db()->preparedStatement(
					'UPDATE `%table` SET SummaryId = NULL WHERE StatementId = :statement_id',
					array('%table' => TABLE_STATEMENTS, ':statement_id' => $statement_id)
				);
				if (!$s->success) {
					$reply->success = false;
					$reply->message = 'Could not unlink Statement';
				}
			} else {
				$reply->success = false;
				$reply->message = 'Prerequisites not met';
			}
			break;
		case 'delete_summary':
			if (check_summary_exists($summary_id)) {
				$s_update = db()->preparedStatement(
					'UPDATE `%table` SET SummaryId = NULL WHERE SummaryId = :summary_id',
					array('%table' => TABLE_STATEMENTS, ':summary_id' => $summary_id)
				);
				if ($s_update->success) {
					$s_delete = db()->preparedStatement(
						'DELETE FROM `%table` WHERE SummaryId = :summary_id',
						array('%table' => TABLE_SUMMARIES, ':summary_id' => $summary_id)
					);
					if (!$s_delete->success) {
						$reply->success = false;
						$reply->message = 'Could not delete Summary';
					}
				} else {
					$reply->success = false;
					$reply->message = 'Could not unlink Statements';
				}
			} else {
				$reply->success = false;
				$reply->message = 'Prerequisites not met';
			}
			break;
		case 'new_summary':
			if (check_statement_exists($statement_id)
			  && check_statement_link($statement_id, null)) {
				$statement = Utils::get_statement($statement_id);
				$s = db()->preparedStatement(
					'INSERT INTO `%table` SET Summary = :statement, IssueId = :issue',
					array(
						'%table' => TABLE_SUMMARIES,
						':statement' => $statement->Statement,
						':issue' => $statement->IssueId
					)
				);
				if ($s->success) {
					$summary_id = $s->lastInsertId;
					$reply->summary_id = $summary_id;
					
					// link source statement to the new one
					$s_link = db()->preparedStatement(
						'UPDATE `%table` SET SummaryId = :summary_id WHERE StatementId = :statement_id',
						array('%table' => TABLE_STATEMENTS, ':summary_id' => $summary_id, ':statement_id' => $statement_id)
					);
					if (!$s_link->success) {
						$reply->success = false;
						$reply->message = 'Could not link Statement';
					}
				} else {
					$reply->success = false;
					$reply->message = 'Could not insert new Summary';
				}
			} else {
				$reply->success = false;
				$reply->message = 'Prerequisites not met';
			}
			break;
		case 'update_summary':
			if (check_summary_exists($summary_id)) {
				$summary_text = Utils::requestOrDefault('summary_text', null);
				if (!is_null($summary_text)) {
					$s_update = db()->preparedStatement(
						'UPDATE `%table` SET Summary = :summary WHERE SummaryId = :summary_id',
						array(
							'%table' => TABLE_SUMMARIES,
							':summary' => $summary_text,
							':summary_id' => $summary_id
						)
					);
					if (!$s_update->success) {
						$reply->success = false;
						$reply->message = 'Could not update Summary';
					}
				} else {
					$reply->success = false;
					$reply->message = 'New Summary text not set';
				}
			} else {
				$reply->success = false;
				$reply->message = 'Summary does not exist';
			}
			break;
		case 'highlight_statement':
			if (check_statement_exists($statement_id)) {
				$highlight = Utils::requestOrDefault('highlight', null);
				if (is_null($highlight)) {
					$reply->success = false;
					$reply->message = 'Highlight value not set';
					break;
				}
				$highlight = ($highlight == 'true') ? 1 : 0;
				if (Utils::get_statement($statement_id)->Highlight != $highlight) {
					$s_update = db()->preparedStatement(
						'UPDATE `%table` SET Highlight = :highlight WHERE StatementId = :statement_id',
						array(
							'%table' => TABLE_STATEMENTS,
							':highlight' => ($highlight ? 1 : 0),
							':statement_id' => $statement_id
						)
					);
					if (!$s_update->success) {
						$reply->success = false;
						$reply->message = 'Could not update Statement';
					}
				} else {
					$reply->success = false;
					$reply->message = 'Highlight already ' . ($highlight ? 'true' : 'false');
				}
			} else {
				$reply->success = false;
				$reply->message = 'Statement does not exist';
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