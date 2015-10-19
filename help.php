<?php
/**
IC-Discuss (c) 2015 by Hope Consultants International Ltd.

IC-Discuss is licensed under a
Creative Commons Attribution-ShareAlike 4.0 International License.

You should have received a copy of the license along with this
work.  If not, see <http://creativecommons.org/licenses/by-sa/4.0/>.
**/
require_once 'includes/bootstrap.php';

$title = 'Help';
$vars = array(
	'title' => $title,
);
display(APP_TITLE . ' - Help', 'Help', 'help.tpl.php', $vars);
