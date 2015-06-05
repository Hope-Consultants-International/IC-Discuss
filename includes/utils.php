<?php

class Utils {
	
	static function get_statement($statement_id) {
		static $cache = array();
		if (is_null($statement_id)) {
			return null;
		} elseif (!isset($cache[$statement_id])) {
			$s = db()->preparedStatement(
				"SELECT
					StatementId,
					SummaryId,
					GroupId,
					IssueId,
					Statement,
					Highlight
				FROM `%table` WHERE StatementId = :id",
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
	
	static function get_summary($summary_id) {
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
	
	static function get_issue($issue_id) {
		static $cache = array();
		if (is_null($issue_id)) {
			return null;
		} elseif (!isset($cache[$issue_id])) {
			$s = db()->preparedStatement(
				"SELECT IssueId, Title, Description, AllowUpload FROM `%table` WHERE IssueId = :id",
				array( '%table' => TABLE_ISSUES, ':id' => $issue_id)
			);
			if ($s->foundRows == 1) {
				$cache[$issue_id] = $s->fetchObject();
			} else {
				$cache[$issue_id] = null;
			}
		}
		return $cache[$issue_id];
	}
	
	static function get_group($group_id) {
		static $cache = array();
		if (is_null($group_id)) {
			return null;
		} elseif (!isset($cache[$group_id])) {
			$s = db()->preparedStatement(
				"SELECT GroupId, Name FROM `%table` WHERE GroupId = :id",
				array( '%table' => TABLE_GROUPS, ':id' => $group_id)
			);
			if ($s->foundRows == 1) {
				$cache[$group_id] = $s->fetchObject();
			} else {
				$cache[$group_id] = null;
			}
		}
		return $cache[$group_id];
	}
	
	static function sanitize_filename($string) {
		$string = preg_replace("/[^A-Za-z0-9-_\.]/", '_', $string);
		$string = preg_replace('/_+/', '_', $string);
		return $string;
	}
}