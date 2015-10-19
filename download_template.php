<?php
/**
IC-Discuss (c) 2015 by Hope Consultants International Ltd.

IC-Discuss is licensed under a
Creative Commons Attribution-ShareAlike 4.0 International License.

You should have received a copy of the license along with this
work.  If not, see <http://creativecommons.org/licenses/by-sa/4.0/>.
**/
require_once 'includes/bootstrap.php';
require_once 'php-excel/Classes/PHPExcel.php';

assertAccess(SECTION_MANAGE);

ignore_user_abort(true);

$group_id = Utils::requestOrDefault('group', null);
$issue_id = Utils::requestOrDefault('issue', null);
$format = Utils::requestOrDefault('format');

/**
 * Abort with Error message
 *
 * @param string $message the message to print
 *
 * @return nothing
 */
function abort($message) {
	header('HTTP/1.0 400 Bad Request');
	print($message);
	exit(0);
}

/**
 * Create a XLS template
 *
 * @param string  $group     Group Name to include in template
 * @param string  $issue     Issue title to include in template
 * @param boolean $to_string return template as string or write to file
 *
 * @return list($xlsname, $xlsfile) $xlsname: name of xls file for client /
 *                                  $xlsfile: filepath to xls file or string containing xls file
 */
function createTemplate($group, $issue, $to_string = false, $format = "Excel5") {
	global $tmpfiles;

	// get extension and make sure it's a supported type
	switch ($format) {
		case 'Excel2007':
			$ext = 'xlsx';
			break;
		case 'Excel5':
		default:
			$ext = 'xls';
			$format = 'Excel5';
			break;
	}
	
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
	
	$xlsname = Utils::sanitizeFilename(APP_TITLE . ' - ' . $group->Name . ' - ' . $issue->Title) . '.' . $ext;
		
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $format);
	if ($to_string) {
		ob_start();
		$objWriter->save('php://output');
		$xlsfile = ob_get_clean();
	} else {
		$xlsfile = tempnam("tmp", $ext);
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
	$issue = Utils::getIssue($issue_id);
	if (is_null($issue)) {
		abort('Issue not found.');
	}
	
	// got through all groups
	$tmpfile = tempnam("tmp", "zip");
	$tmpfiles[] = $tmpfile;
	$filename = Utils::sanitizeFilename(APP_TITLE . ' - All - ' . $issue->Title . ' [' . $format . ']') . '.zip';
	$zip = new ZipArchive;
	$res = $zip->open($tmpfile, ZipArchive::CREATE);
	if ($res === true) {
		
		$s = db()->preparedStatement(
			"SELECT GroupId, Name FROM `%table` ORDER BY Name",
			array( '%table' => TABLE_GROUPS )
		);
		if ($s->success) {
			while ($group = $s->fetchObject()) {
				list ($xlsname, $xlsfile) = createTemplate($group, $issue, true, $format);
				if (!empty($issue->Folder)) {
					$xlsname = Utils::sanitizeFilename($issue->Folder) . DIRECTORY_SEPARATOR . $xlsname;
				}
				$zip->addFromString($xlsname, $xlsfile);
			}
			
		}
		$zip->close();
	} else {
		abort('Could not open temporary file.');
	}
} elseif (is_null($issue_id) && !is_null($group_id)) {
	$group = Utils::getGroup($group_id);
	if (is_null($group)) {
		abort('Group not found.');
	}
	
	// got through all issues
	$tmpfile = tempnam("tmp", "zip");
	$tmpfiles[] = $tmpfile;
	$filename = Utils::sanitizeFilename(APP_TITLE . ' - ' . $group->Name . ' - All [' . $format . ']') . '.zip';
	$zip = new ZipArchive;
	$res = $zip->open($tmpfile, ZipArchive::CREATE);
	if ($res === true) {
		
		$s = db()->preparedStatement(
			"SELECT IssueId, Title, Description, Folder, AllowUpload FROM `%table` ORDER BY Title",
			array( '%table' => TABLE_ISSUES )
		);
		if ($s->success) {
			while ($issue = $s->fetchObject()) {
				list ($xlsname, $xlsfile) = createTemplate($group, $issue, true, $format);
				if (!empty($issue->Folder)) {
					$xlsname = Utils::sanitizeFilename($issue->Folder) . DIRECTORY_SEPARATOR . $xlsname;
				}
				$zip->addFromString($xlsname, $xlsfile);
			}
			
		}
		$zip->close();
	} else {
		abort('Could not open temporary file.');
	}
} elseif (!is_null($issue_id) && !is_null($group_id)) {
	$group = Utils::getGroup($group_id);
	if (is_null($group)) {
		abort('Group not found.');
	}
	
	$issue = Utils::getIssue($issue_id);
	if (is_null($issue)) {
		abort('Issue not found.');
	}	
	
	list ($filename, $tmpfile) = createTemplate($group, $issue, false, $format);
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