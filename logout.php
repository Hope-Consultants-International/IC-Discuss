<?php
/**
IC-Discuss (c) 2015 by Hope Consultants International Ltd.

IC-Discuss is licensed under a
Creative Commons Attribution-ShareAlike 4.0 International License.

You should have received a copy of the license along with this
work.  If not, see <http://creativecommons.org/licenses/by-sa/4.0/>.
**/
require_once 'includes/bootstrap.php';

$vars = array(
    'target' => BASE_URL . 'index.php',
    'temp_page' => BASE_URL . 'index.php',
);

display(APP_TITLE . ' - Logout', 'Logout', 'logout.tpl.php', $vars);
