<?php
/**
 * This class provides utilities for IC-Discuss
 *
 * PHP version 5
 *
 * @package    IC-Discuss
 * @subpackage TemplateLib
 */

/**
 * Utility functions
 *
 * @package    IC-Discuss
 * @subpackage Utils
 */
class Utils {

    /**
     * Retrieve a Statement from the database
     *
     * @param string $statement_id id of statement to retrieve
     *
     * @return object Statement or null if not found
     */
	static function getStatement($statement_id) {
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
					Highlight,
					Weight
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
	
    /**
     * Retrieve a Summary from the database
     *
     * @param string $summary_id id of summary to retrieve
     *
     * @return object Summary or null if not found
     */
	static function getSummary($summary_id) {
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
	
    /**
     * Retrieve an Issue from the database
     *
     * @param string $issue_id id of issue to retrieve
     *
     * @return object Issue or null if not found
     */
	static function getIssue($issue_id) {
		static $cache = array();
		if (is_null($issue_id)) {
			return null;
		} elseif (!isset($cache[$issue_id])) {
			$s = db()->preparedStatement(
				"SELECT IssueId, Title, Description, Folder, AllowUpload, Frontpage FROM `%table` WHERE IssueId = :id",
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
	
    /**
     * Retrieve a Group from the database
     *
     * @param string $group_id id of group to retrieve
     *
     * @return object Group or null if not found
     */
	static function getGroup($group_id) {
		static $cache = array();
		if (is_null($group_id)) {
			return null;
		} elseif (!isset($cache[$group_id])) {
			$s = db()->preparedStatement(
				"SELECT GroupId, Name, Frontpage FROM `%table` WHERE GroupId = :id",
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

    /**
     * Sanitize a Filename
     *
     * Removes problematic characters from a filename and replaces them with _
     *
     * @param string $filename filename to sanitize
     *
     * @return string sanitized filename
     */
	static function sanitizeFilename($filename) {
		$filename = preg_replace("/[^A-Za-z0-9-_\()\[\]. ]/", '_', $filename);
		$filename = preg_replace('/_+/', '_', $filename);
		return $filename;
	}

    /**
     * Escape a string to be included in javascript code
     *
     * @param string $string string to be escaped
     *
     * @return escaped string
     */
	static function javascriptString($string) {
		// htmlentities
		$string = htmlentities($string);
		
		// no single quotes
		$string = str_replace('\'', '\\\'', $string);
		
		// replace whitespaces
		$string = preg_replace('/\s+/', ' ', $string);
		
		return $string;
	}

    /**
     * Return Request parameter or Default value
     *
     * @param string $parameter  parameter name
     * @param mixed  $default    what should be returned if parameter was not given (default: '')
     * @param mixed  $null_value treat this value as being null (and return null if given) (default: null)
     *
     * @return returns named parameter or the default value
     */
	static function requestOrDefault($parameter, $default = '', $null_value = null) {
		$value = (isset($_REQUEST[$parameter])) ? $_REQUEST[$parameter] : $default;
		if ($value === $null_value) {
			$value = null;
		}
		return $value;
	}
}
