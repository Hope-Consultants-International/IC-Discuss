<?php
/**
 * Initialize the system for use.
 *
 * Load commonly used libraries and initialize constants
 */

 /* 
  * We need to set a version number that changes every time css or js files change,
  * to make sure they get reloaded and not loaded from cache.
  */
 define('RESOURCE_VERSION', '1.3');
 
 
/*
 * Set some important path constants
 */
$path = dirname(__FILE__);
define('CLIENT_PATH', $path . '/../');
define('TEMPLATE_PATH', CLIENT_PATH . 'templates/');
unset($path);

/*
 * Parse the INI file, which will contain general parameters.
 */
$conf = CLIENT_PATH . 'includes/config.ini';
if (!is_readable($conf)) {
    print('FATAL: There is no configuration file.');
    exit();
}
$ini = parse_ini_file($conf, true);
unset($conf);

// our own libraries
require_once CLIENT_PATH . 'includes/db.php';
require_once CLIENT_PATH . 'includes/template.php';
require_once CLIENT_PATH . 'includes/utils.php';

// debugging
define('DEBUG', $ini['debug']);

// app title
define('APP_TITLE', $ini['app_title']);

define('NEW_ENTRY_ID', 'add');

// database constants
define('DB_HOSTNAME', $ini['DB']['hostname']);
define('DB_DATABASE', $ini['DB']['database']);
define('DB_USERNAME', $ini['DB']['username']);
define('DB_PASSWORD', $ini['DB']['password']);

// tables
define('TABLE_GROUPS', 'groups');
define('TABLE_ISSUES', 'issues');
define('TABLE_STATEMENTS', 'statements');
define('TABLE_SUMMARIES', 'summaries');

// Access
define('SECTION_UPLOAD', 'upload');
define('SECTION_SYNTHESIZE', 'synthesize');
define('SECTION_MANAGE', 'manage');
define('ACCESS_ENABLED', isset($ini['Access']['enabled']) ? $ini['Access']['enabled'] : false);
if (ACCESS_ENABLED && !isset($_SERVER['REMOTE_USER'])) {
	die('Access Control Not Possible.');
}

// XLS Template stuff
define('XLS_TEMPLATE', 'templates/form.xls');
define('GROUP_TAG', 'Group: ');
define('GROUP_CELL', 'A2');
define('ISSUE_TAG', 'Topic/Issue: ');
define('ISSUE_CELL', 'A3');
define('DESCRIPTION_CELL', 'A4');
define('DATA_COLUMN_STATEMENT', 'A');
define('DATA_COLUMN_WEIGHT', 'B');
define('DATA_ROW_MIN', '7');
define('DATA_ROW_MAX', '200');

 function currentdir($url) {
    // note: anything without a scheme ("example.com", "example.com:80/", etc.) is a folder
    // remove query (protection against "?url=http://example.com/")
    if ($first_query = strpos($url, '?')) $url = substr($url, 0, $first_query);
    // remove fragment (protection against "#http://example.com/")
    if ($first_fragment = strpos($url, '#')) $url = substr($url, 0, $first_fragment);
    // folder only
    $last_slash = strrpos($url, '/');
    if (!$last_slash) {
        return '/';
    }
    // add ending slash to "http://example.com"
    if (($first_colon = strpos($url, '://')) !== false && $first_colon + 2 == $last_slash) {
        return $url . '/';
    }
    return substr($url, 0, $last_slash + 1);
}

define('BASE_URL', currentdir($_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]));

/**
 * Get a Template instance.
 *
 * This returns an {@link Template} object
 *
 * @return Template An initialized Template object
 */
function template_engine() {
    static $_template_engine = null;
    if (is_null($_template_engine)) {
        $_template_engine = new Template(TEMPLATE_PATH, array('en', 'es'));
    }
    return $_template_engine;
}

/**
 * Get a Database instance
 */
function db() {
    static $_db = null;
    if (is_null($_db)) {
        $_db = new DB(DB_HOSTNAME, DB_DATABASE, DB_USERNAME, DB_PASSWORD);
    }
    return $_db;
}

/**#@+
 * Message type.
 * @see set_message()
 */
define('MSG_TYPE_INFO', 6); // syslog.h: LOG_INFO
define('MSG_TYPE_WARN', 4); // syslog.h: LOG_WARNING
define('MSG_TYPE_ERR', 3); // syslog.h: LOG_ERR
define('MSG_TYPE_DEBUG', 7); // syslog.h: LOG_DEBUG

$_msg_store = array();
/**
 * Set a message to be displayed to the user.
 *
 * @param string $msg      the message
 * @param int    $msg_type One of the MSG_TYPE_* constants defined in {@link bootstrap.php}.
 *
 * @return void nothing
 * @see get_messages()
 * @see MSG_TYPE_INFO
 */
function set_message($msg, $msg_type = MSG_TYPE_INFO) {
    global $_msg_store;
    $_msg_store[$msg_type][] = $msg;
}

/**
 * Get stored error/warning/info messages.
 * 
 * If $type is set to one of the MSG_TYPE_* constants, then
 * just messages of that type will be returned.
 * 
 * Otherwise, all messages will be returned, organized in subarrrays
 * by MSG_TYPE_*.
 *
 * @param int $type One of the MSG_TYPE_* constants defined in {@link bootstrap.php} (default: null).
 *
 * @return array Array of messages.
 * @see set_message()
 * @see MSG_TYPE_INFO
 */
function get_messages($type = null) {
    global $_msg_store;
    if (isset($type)) {
        return (isset($_msg_store[$type])) ? $_msg_store[$type] : array();
    }
    return $_msg_store;
}

function display($title, $current_page, $template, $vars = array()) {
	$s_issues = db()->preparedStatement(
		"SELECT IssueId, Title FROM `%table` ORDER BY Title",
		array('%table' => TABLE_ISSUES)
	);
	$issues = $s_issues->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_COLUMN);
	$issues = array_map('reset', $issues);
	
	$main_vars = array(
		'page_title' => $title,
		'current_page' => $current_page,
		'issues' => $issues,
		'contents' => template_engine()->render($template, $vars),
	);
	print(template_engine()->render('main.tpl.php', $main_vars));
}

/**
 * Check if user is allowed access
 *
 * Returns true if read control is not active or if the user is allowed.
 * Returns false if the user is not allowed
 *
 * @params $section string access to which section? 
 *
 * @return boolean true if allowed
 */
function check_access($section) {
	global $ini;
	static $users = null;
	if (is_null($users)) {
		$sections = array(SECTION_UPLOAD, SECTION_SYNTHESIZE, SECTION_MANAGE);
		foreach ($sections as $sect) {
			$list = (isset($ini['Access'][$sect]) && is_array($ini['Access'][$sect])) ? $ini['Access'][$sect] : array();
			$list = array_map('strtolower', $list);
			$users[$sect] = $list;
		}
	}
	if (ACCESS_ENABLED) {
		if (!isset($users[$section])
		  || !in_array(strtolower($_SERVER['REMOTE_USER']), $users[$section])) {
			return false;
		}
	}
	return true;
}

function is_logged_in() {
    if (ACCESS_ENABLED) {
        return (!empty($_SERVER['REMOTE_USER']));
    } else {
        return true;
    }
}

function assert_access($section) {
	if (!check_access($section)) {
		header('HTTP/1.0 401 Unauthorized');
		print('Unauthorized');
		exit(0);
	}
}
