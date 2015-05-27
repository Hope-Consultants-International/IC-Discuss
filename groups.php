<?php
require_once 'includes/bootstrap.php';

function _requestOrDefault($parameter, $default = '', $null_value = null) {
	$value = (isset($_REQUEST[$parameter])) ? $_REQUEST[$parameter] : $default;
	if ($value === $null_value) {
		$value = null;
	}
	return $value;
}

$group_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : NEW_ENTRY_ID;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';
$page_url = strtok($_SERVER["REQUEST_URI"], '?');

// new group?
$new_group = false;
if ($group_id == NEW_ENTRY_ID) {
	$new_group = true;
}

switch ($action) {
	case 'delete':
		$query = "DELETE FROM `%table` WHERE GroupId = :id";
		$values = array(
			'%table' => TABLE_GROUPS,
			':id' => $group_id,
		);
		$stmt = db()->preparedStatement($query, $values);
		if (!$stmt->success) {
			die('Database delete fail: ' . $stmt->error);
		}
		header('Location: ' . $page_url . '?action=list', true, 302);
		break;
	case 'save':
		// get values for groups table
		$values = array(
			'%table' => TABLE_GROUPS,
			':name' => _requestOrDefault('GroupName'),
		);
		if ($new_group) {
			$query = "INSERT INTO `%table` SET Name = :name";
		} else {
			$query = "UPDATE `%table` SET Name = :name WHERE GroupId = :id";
			$values[':id'] = $group_id;
		}
		$stmt = db()->preparedStatement($query, $values);
		if (!$stmt->success) {
			die('Database update fail: ' . $stmt->error);
		} elseif ($new_group) {
			$group_id = $stmt->lastInsertId;
		}
		header('Location: ' . $page_url . '?action=list', true, 302);
		break;
	case 'edit':
			if ($new_group) {
				$title = 'New Group';
				$group_name = '';
			} else {
				$query = "SELECT Name FROM `%table` WHERE GroupId = :id";
				$values = array('%table' => TABLE_GROUPS, ':id' => $group_id);
				$stmt = db()->preparedStatement($query, $values);
				if ($stmt->foundRows == 1) {
					$group = $stmt->fetchObject();
					$group_name = $group->Name;
				} else {
					die('Group not found: ' . $group_id);
				}
				$title = 'Edit Group "' . htmlentities($group_name) . '"';
			}
			$vars = array(
				'group_id' => $group_id,
				'group_name' => $group_name,
				'page_url' => $page_url,
				'title' => $title,
			);
			display(APP_TITLE, 'Manage|Groups', 'groups_edit.tpl.php', $vars);
		break;
	case 'list':
			$groups = array();
			$query = "SELECT GroupId, Name FROM `%table`";
			$values = array('%table' => TABLE_GROUPS);
			$stmt = db()->preparedStatement($query, $values);
			while ($group = $stmt->fetchObject()) {
				$groups[$group->GroupId] = $group;
			}
			$vars = array(
				'groups' => $groups,
				'page_url' => $page_url,
			);
			display(APP_TITLE, 'Manage|Groups', 'groups_list.tpl.php', $vars);
		break;
	default:
		die('Unknown action: ' . htmlentities($action));
		break;
}