<?php
require_once 'includes/bootstrap.php';
require_once 'php-excel/Classes/PHPExcel.php';

assert_access(SECTION_MANAGE);

ignore_user_abort(true);

$group_id = isset($_REQUEST['group']) ? $_REQUEST['group'] : null;
$issue_id = isset($_REQUEST['issue']) ? $_REQUEST['issue'] : null;

function abort($message) {
	header('HTTP/1.0 400 Bad Request');
	print($message);
	exit(0);
}

function create_template($group, $issue, $to_string = false) {
	global $tmpfiles;
	
	// load template
	$inputFileType = PHPExcel_IOFactory::identify(XLS_TEMPLATE);
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	$objPHPExcel = $objReader->load(XLS_TEMPLATE);
	
	$sheet = $objPHPExcel->getSheet(0);
	
	$objRichText = new PHPExcel_RichText();
	$objBold = $objRichText->createTextRun(ISSUE_TAG);
	$objBold->getFont()->setBold(true);
	$objRichText->createText($issue->Title);
	$sheet->getCell(ISSUE_CELL)->setValue($objRichText);
	
	$objRichText = new PHPExcel_RichText();
	$objBold = $objRichText->createTextRun(GROUP_TAG);
	$objBold->getFont()->setBold(true);
	$objRichText->createText($group->Name);
	$sheet->getCell(GROUP_CELL)->setValue($objRichText);
	
	$objRichText = new PHPExcel_RichText();
	$objRichText->createText($issue->Description);
	$sheet->setCellValue(DESCRIPTION_CELL, $objRichText);
	
	$sheet->setSelectedCells(DATA_COLUMN_STATEMENT . DATA_ROW_MIN);
	
	$objPHPExcel->getProperties()->setCreator("IC-Discuss");
	
	$xlsname = Utils::sanitize_filename(APP_TITLE . ' - ' . $group->Name . ' - ' . $issue->Title) . '.xls';
		
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
	if ($to_string) {
		ob_start();
		$objWriter->save('php://output');
		$xlsfile = ob_get_clean();
	} else {
		$xlsfile = tempnam("tmp", "xls");
		$tmpfiles[] = $xlsfile;
		$objWriter->save($xlsfile);
	}
	
	return array(
		$xlsname,
		$xlsfile
	);
}

$filename = '';
$tmpfile = '';
$tmpfiles = array();

if (!is_null($issue_id) && is_null($group_id)) {
	$issue = Utils::get_issue($issue_id);
	if (is_null($issue)) {
		abort('Issue not found.');
	}
	
	// got through all groups
	$tmpfile = tempnam("tmp", "zip");
	$tmpfiles[] = $tmpfile;
	$filename = Utils::sanitize_filename(APP_TITLE . ' - All - ' . $issue->Title) . '.zip';
	$zip = new ZipArchive;
	$res = $zip->open($tmpfile, ZipArchive::CREATE);
	if ($res === true) {
		
		$s = db()->preparedStatement(
			"SELECT GroupId, Name FROM `%table` ORDER BY Name",
			array( '%table' => TABLE_GROUPS )
		);
		if ($s->success) {
			while ($group = $s->fetchObject()) {
				list ($xlsname, $xlsfile) = create_template($group, $issue, true);
				if (!empty($issue->Folder)) {
					$xlsname = Utils::sanitize_filename($issue->Folder) . DIRECTORY_SEPARATOR . $xlsname;
				}
				$zip->addFromString($xlsname, $xlsfile);
			}
			
		}
		$zip->close();
	} else {
		abort('Could not open temporary file.');
	}
} elseif (is_null($issue_id) && !is_null($group_id)) {
	$group = Utils::get_group($group_id);
	if (is_null($group)) {
		abort('Group not found.');
	}
	
	// got through all issues
	$tmpfile = tempnam("tmp", "zip");
	$tmpfiles[] = $tmpfile;
	$filename = Utils::sanitize_filename(APP_TITLE . ' - ' . $group->Name . ' - All') . '.zip';
	$zip = new ZipArchive;
	$res = $zip->open($tmpfile, ZipArchive::CREATE);
	if ($res === true) {
		
		$s = db()->preparedStatement(
			"SELECT IssueId, Title, Description, Folder, AllowUpload FROM `%table` ORDER BY Title",
			array( '%table' => TABLE_ISSUES )
		);
		if ($s->success) {
			while ($issue = $s->fetchObject()) {
				list ($xlsname, $xlsfile) = create_template($group, $issue, true);
				if (!empty($issue->Folder)) {
					$xlsname = Utils::sanitize_filename($issue->Folder) . DIRECTORY_SEPARATOR . $xlsname;
				}
				$zip->addFromString($xlsname, $xlsfile);
			}
			
		}
		$zip->close();
	} else {
		abort('Could not open temporary file.');
	}
} elseif (!is_null($issue_id) && !is_null($group_id)) {
	$group = Utils::get_group($group_id);
	if (is_null($group)) {
		abort('Group not found.');
	}
	
	$issue = Utils::get_issue($issue_id);
	if (is_null($issue)) {
		abort('Issue not found.');
	}	
	
	list ($filename, $tmpfile) = create_template($group, $issue);
} else {
	abort('No group and no issue.');
}

if (substr($filename, -3) == 'xls') {
	$mime_type = 'application/vnd.ms-excel';
} else {
	$mime_type = 'application/zip';
}

header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($tmpfile));
readfile($tmpfile);

foreach ($tmpfiles as $file) {
	unlink($file);
}