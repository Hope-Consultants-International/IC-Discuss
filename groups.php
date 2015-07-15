<?php
require_once 'includes/bootstrap.php';

assert_access(SECTION_MANAGE);

$group_id = Utils::requestOrDefault('id', NEW_ENTRY_ID);
$action = Utils::requestOrDefault('action', 'list');
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
			':name' => Utils::requestOrDefault('GroupName'),
			':frontpage' => (Utils::requestOrDefault('Frontpage', false) ? 1 : 0),
		);
		if ($new_group) {
			$query = "INSERT INTO `%table` SET Name = :name, Frontpage = :frontpage";
		} else {
			$query = "UPDATE `%table` SET Name = :name, Frontpage = :frontpage WHERE GroupId = :id";
			$values[':id'] = $group_id;
		}
		$stmt = db()->preparedStatement($query, $values);
		if (!$stmt->success) {
			die('Database update fail: ' . $stmt->error);
		} elseif ($new_group) {
			$group_id = $stmt->lastInsertId;
		}
		
		if ($values[':frontpage']) {
			db()->preparedStatement(
				"UPDATE `%table` SET Frontpage = 0 WHERE GroupId != :id",
				array(
					'%table' => TABLE_GROUPS,
					':id' => $group_id,
				)
			);
		}
		
		header('Location: ' . $page_url . '?action=list', true, 302);
		break;
	case 'edit':
			if ($new_group) {
				$title = 'New Group';
				$group_name = '';
				$group_frontpage = false;
			} else {
				$query = "SELECT Name, Frontpage FROM `%table` WHERE GroupId = :id";
				$values = array('%table' => TABLE_GROUPS, ':id' => $group_id);
				$stmt = db()->preparedStatement($query, $values);
				if ($stmt->foundRows == 1) {
					$group = $stmt->fetchObject();
					$group_name = $group->Name;
					$group_frontpage = $group->Frontpage;
				} else {
					die('Group not found: ' . $group_id);
				}
				$title = 'Edit Group "' . htmlentities($group_name) . '"';
			}
			$vars = array(
				'group_id' => $group_id,
				'group_name' => $group_name,
				'group_frontpage' => $group_frontpage,
				'page_url' => $page_url,
				'title' => $title,
			);
			display(APP_TITLE, 'Manage|Groups', 'groups_edit.tpl.php', $vars);
		break;
	case 'list':
			$groups = array();
			$got_default_group = false;
			$query = "SELECT GroupId, Name, Frontpage FROM `%table`";
			$values = array('%table' => TABLE_GROUPS);
			$stmt = db()->preparedStatement($query, $values);
			while ($group = $stmt->fetchObject()) {
				$groups[$group->GroupId] = $group;
				if ($group->Frontpage) {
					$got_default_group = true;
				}
			}
			if (!$got_default_group) {
				set_message("No Group for Frontpage set!", MSG_TYPE_WARN);
			}
			$vars = array(
				'groups' => $groups,
				'page_url' => $page_url,
				'download_url' => BASE_URL . 'download_template.php',
			);
			display(APP_TITLE, 'Manage|Groups', 'groups_list.tpl.php', $vars);
		break;
	default:
		die('Unknown action: ' . htmlentities($action));
		break;
}