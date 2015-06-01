<?php
require_once 'includes/bootstrap.php';

$issue_id = isset($_REQUEST['issue']) ? $_REQUEST['issue'] : null;
$statement_id = isset($_REQUEST['statement']) ? $_REQUEST['statement'] : null;
$summary_id = isset($_REQUEST['summary']) ? $_REQUEST['summary'] : null;
$summary_id_old = isset($_REQUEST['summary_old']) ? $_REQUEST['summary_old'] : null;
$summary_text = isset($_REQUEST['summary_text']) ? $_REQUEST['summary_text'] : null;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;

function get_statement($statement_id) {
	static $cache = array();
	if (is_null($statement_id)) {
		return null;
	} elseif (!isset($cache[$statement_id])) {
		$s = db()->preparedStatement(
			"SELECT StatementId, SummaryId, GroupId, IssueId, Statement FROM `%table` WHERE StatementId = :id",
			array( '%table' => TABLE_STATEMENTS, ':id' => $statement_id)
		);
		if ($s->foundRows == 1) {
			$cache[$statement_id] = $s->fetchObject();
		} else {
			$cache[$statement_id] = null;
		}
	}
	return $cache[$statement_id];
}
function get_summary($summary_id) {
	static $cache = array();
	if (is_null($summary_id)) {
		return null;
	} elseif (!isset($cache[$summary_id])) {
		$s = db()->preparedStatement(
			"SELECT SummaryId, IssueId, Summary FROM `%table` WHERE SummaryId = :id",
			array( '%table' => TABLE_SUMMARIES, ':id' => $summary_id)
		);
		if ($s->foundRows == 1) {
			$cache[$summary_id] = $s->fetchObject();
		} else {
			$cache[$summary_id] = null;
		}
	}
	return $cache[$summary_id];
}

function check_statement_exists($statement_id) {
	return (!is_null($statement_id)
		&& !is_null(get_statement($statement_id)));
}

function check_summary_exists($summary_id) {
	return (!is_null($summary_id)
	  && !is_null(get_summary($summary_id)));
}

function check_statement_link($statement_id, $summary_id) {
	$statement = get_statement($statement_id);
	if (!is_null($statement)) {
		header('X-Debug', (is_null($statement->SummaryId)) ? 'null' : $statement->SummaryId);
	}
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
				$statement = get_statement($statement_id);
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
			if (check_summary_exists($summary_id)
			  && !is_null($summary_text)) {
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
header('Content-Type: application/json');
print(json_encode($reply));