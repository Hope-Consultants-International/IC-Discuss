<?php
/**
 * Initialize the system for use.
 *
 * Load commonly used libraries and initialize constants
 */

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

// debugging
define('DEBUG', $ini['debug']);

// app title
define('APP_TITLE', $ini['app_title']);

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
	$main_vars = array(
		'page_title' => $title,
		'current_page' => $current_page,
		'inhalt' => template_engine()->render($template, $vars),
	);
	print(template_engine()->render('main.tpl.php', $main_vars));
}