<?php
require_once 'includes/bootstrap.php';
require_once 'php-excel/Classes/PHPExcel.php';

assert_access(SECTION_UPLOAD);

define('ISSUE_TAG', 'Topic/Issue: ');
define('ISSUE_CELL', 'A2');
define('GROUP_TAG', 'Group: ');
define('GROUP_CELL', 'A3');
define('DATA_COLUMN', 'A');
define('DATA_ROW_MIN', '5');
define('DATA_ROW_MAX', '200');

function _dataOrDefault($data, $default = '') {
	return empty($data) ? $default : $data;
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'nothing';

if ($action == 'import') {
	if(isset($_FILES['spreadsheet']) &&
	  !empty($_FILES['spreadsheet']['name'])) {
		if(empty($_FILES['spreadsheet']['error'])) {
			$inputFile = $_FILES['spreadsheet']['name'];
			$extension = strtoupper(pathinfo($inputFile, PATHINFO_EXTENSION));
			if (in_array(strtolower($extension), array('xls', 'xlsx', 'ods'))) {

				//Read spreadsheeet workbook
				try {
					$inputFile = $_FILES['spreadsheet']['tmp_name'];
					$inputFileType = PHPExcel_IOFactory::identify($inputFile);
					$objReader = PHPExcel_IOFactory::createReader($inputFileType);
					$objPHPExcel = $objReader->load($inputFile);
				} catch(Exception $e) {
                    die($e->getMessage());
				}

				//Get worksheet dimensions
				$sheet = $objPHPExcel->getSheet(0);
				
				$issue_id = null;
				$issue = $sheet->getCell(ISSUE_CELL)->getvalue();
				$issue = str_replace(ISSUE_TAG, '', $issue);
				$stmt = db()->preparedStatement(
					"SELECT IssueId, AllowUpload FROM `%table` WHERE Title = :issue",
					array('%table' => TABLE_ISSUES, ':issue' => $issue)
				);
				if ($stmt->foundRows > 1) {
					set_message('Ambiguous Issue: ' . $issue, MSG_TYPE_ERR);
				} elseif ($stmt->foundRows != 1) {
					set_message('Issue not found: ' . $issue, MSG_TYPE_ERR);
				} else {
					$issue_obj = $stmt->fetchObject();
					if ($issue_obj->AllowUpload) {
						$issue_id = $issue_obj->IssueId;
					} else {
						set_message('Uploads for this Issue are disabled: ' . $issue, MSG_TYPE_WARN);
						$issue_id = false;
					}
				}
				
				$group_id = null;
				$group = $sheet->getCell(GROUP_CELL)->getvalue();
				$group = str_replace(GROUP_TAG, '', $group);
				$stmt = db()->preparedStatement(
					"SELECT GroupId FROM `%table` WHERE Name = :group",
					array('%table' => TABLE_GROUPS, ':group' => $group)
				);
				if ($stmt->foundRows > 1) {
					set_message('Ambiguous Group: ' . $group, MSG_TYPE_ERR);
				} elseif ($stmt->foundRows != 1) {
					set_message('Group not found: ' . $group, MSG_TYPE_ERR);
				} else {
					$group_id = $stmt->fetchColumn(0);
				}
				
				if (!is_null($group_id) && !is_null($issue_id) && ($issue_id !== false)) {
					// Delete current statements
					$s = db()->preparedStatement(
						"DELETE FROM `%table` WHERE GroupId = :group_id AND IssueId = :issue_id",
						array('%table' => TABLE_STATEMENTS, ':group_id' => $group_id, ':issue_id' => $issue_id)
					);
					
					$statement_query = "INSERT INTO `%table` SET
						GroupId = :group_id,
						IssueId = :issue_id,
						SummaryId = NULL,
						Statement = :statement
					";
					
					//  Read Data into Array
					$highestRow = min($sheet->getHighestDataRow(), DATA_ROW_MAX);
					$data = $sheet->rangeToArray(DATA_COLUMN . DATA_ROW_MIN . ':' . DATA_COLUMN . $highestRow, NULL, TRUE, FALSE);
					$row_num = DATA_ROW_MIN - 1;
					$statement_num = 0;
					foreach ($data as $row) {
						$row_num++;
						$statement = _dataOrDefault($row[0]);
						
						if (!empty($statement)) {
							$statement_num++;
							$values = array(
								'%table' => TABLE_STATEMENTS,
								':group_id' => $group_id,
								':issue_id' => $issue_id,
								':statement' => $statement,
							);					
						
							$s = db()->preparedStatement($statement_query, $values);
							if (!$s->success) {
								set_message("Problem with Statement in row {$row_num}: " . $s->error, MSG_TYPE_ERR);
							}
						}
					}
					set_message("{$statement_num} Statements imported. (Group: {$group} / Issue: ${issue})", MSG_TYPE_INFO);
				} else {
					if ($issue_id !== false) {
						set_message("Group ID or Issue ID not found.", MSG_TYPE_ERR);
					}
				}
			} else {
				set_message("Please upload XLS, XLSX or ODS.", MSG_TYPE_ERR);
			}
		} else{ 
			set_message($_FILES['spreadsheet']['error'], MSG_TYPE_ERR);
		}
	} else {
		set_message('Error Uploading: ' . print_r($_FILES, true), MSG_TYPE_ERR);
	}
}

display(APP_TITLE . ' - Upload', 'Upload', 'upload.tpl.php');