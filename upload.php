<?php

require_once 'includes/bootstrap.php';
require_once 'php-excel/Classes/PHPExcel.php';

define('FIRST_COLUMN', 'B');
define('FIRST_ROW', '2');

if (!IMPORT_ACTIVE) {
	die('Import deaktiviert.');
}

function _dateOrWarning($data, $row = 0, $column = '') {
	if (empty($data)) {
		return null;
	} elseif (is_numeric($data)) {
		$exceldatestamp = PHPExcel_Shared_Date::ExcelToPHP($data);
        return date('Y-m-d', $exceldatestamp);
	} else {
		set_message("Kein Datumswert in Zeile {$row}, Spalte '${column}': {$data}", MSG_TYPE_WARN);
		return null;
	}
}

function _dataOrDefault($data, $default = '') {
	return empty($data) ? $default : $data;
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'nothing';

if ($action == 'empty') {
	$s = db()->preparedStatement('DELETE FROM mitarbeiter');
	if (!$s->success) {
		set_message('Could not empty tables.', MSG_TYPE_ERR);
	} else {
		$s = db()->preparedStatement('ALTER TABLE mitarbeiter AUTO_INCREMENT = 0');
		if (!$s->success) {
			set_message('Could not reset auto_increment.', MSG_TYPE_ERR);
		}
	}
}

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
				$highestRow = $sheet->getHighestDataRow();
				$highestColumn = $sheet->getHighestDataColumn();

				$mitarbeiter_query = "INSERT INTO `mitarbeiter` SET
					Nachname = :nachname,
					Vorname = :vorname,
					Geburtsdatum = :geburtsdatum,
					Dienstbeginn = :dienstbeginn,
					Anstellung = :anstellung,
					G35notwendig = :g35,
					Lebensversicherung = :lebensversicherung,
					Notizen = :notizen,
					Archiviert = :archiv,
					Kandidatenzeit = :kandidatenzeit,
					TeamStatus = :status,
					Geschlecht = :geschlecht,
					Rueckholversicherung = :rueckhol,
					RueckholversNummer = :rueckholnum,
					RueckholversMerkblatt = :rueckholmb,
					Drittlandversicherung = :drittland,
					Haftpflichtversicherung = :haftpflicht,
					KindergeldBeantragt = :kindergeld,
					E101Beantragt = :e101
				";
				
                //  Read Data into Array
                $data = $sheet->rangeToArray(FIRST_COLUMN . FIRST_ROW . ':' . $highestColumn . $highestRow, NULL, TRUE, FALSE);
				$row_num = 1;
				foreach ($data as $row) {
					$row_num++;
					//set_message(print_r($row, true), MSG_TYPE_INFO);
					
					$in_deutschland_bis = _dateOrWarning($row[3], $row_num, 'in Deutschland');
					$e101_gueltig_bis = _dateOrWarning($row[27], $row_num, 'E101 gültig bis');
					$kindergeld_gueltig_bis = _dateOrWarning($row[29], $row_num, 'Kindergeld genehmigt bis');
					$letzte_g35 = _dateOrWarning($row[13], $row_num, 'letzte G35');
					
					$debr_versandt = _dateOrWarning($row[15], $row_num, 'DEBR Bogen');
					$debr_wer = $row[18];
					$debr_wann = _dateOrWarning($row[17], $row_num, 'DEBR Gespräch');
					$debr_kommentar = $row[16];
					
					$mitarbeiter = array(
						':nachname' => $row[0],
						':vorname' => $row[1],
						':geburtsdatum' => _dateOrWarning($row[4], $row_num, 'Geburtsdatum'),
						':dienstbeginn' => _dateOrWarning($row[7], $row_num, 'Dienstbeginn'),
						':anstellung' => $row[2],
						':g35' => (strtolower($row[12]) == 'j') ? 1 : 0,
						':lebensversicherung' => _dataOrDefault($row[40]),
						':notizen' => _dataOrDefault($row[8]),
						':archiv' => 0,
						':kandidatenzeit' => _dateOrWarning($row[6], $row_num, 'Kandidatenzeit'),
						':status' => $row[10],
						':geschlecht' => (strtolower($row[11]) == 'm') ? 'M' : 'W',
						':rueckhol' => (!empty($row[30])) ? 1 : 0,
						':rueckholnum' => _dataOrDefault($row[31]),
						':rueckholmb' => 0,
						':drittland' => _dataOrDefault($row[34]),
						':haftpflicht' => _dataOrDefault($row[35]),
						':kindergeld' => (!empty($row[28])) ? 1 : 0,
						':e101' => (!empty($row[26])) ? 1 : 0,
					);
					if ($mitarbeiter[':anstellung'] == 'F') {
						$mitarbeiter[':anstellung'] = 'Frontiers';
					}
					if ($mitarbeiter[':status'] == 'OFFICE') {
						$mitarbeiter[':status'] = 'Office';
					}
					// set_message(print_r($mitarbeiter, true), MSG_TYPE_INFO);
					
					$s = db()->preparedStatement($mitarbeiter_query, $mitarbeiter);
					if (!$s->success) {
						set_message("Konnte Mitarbeiter in Zeile {$row_num} nicht speichern: " . $s->error, MSG_TYPE_ERR);
					} else {
						$mitarbeiter_id = $s->lastInsertId;
						
						if (!empty($in_deutschland_bis)) {
							$stmt = db()->preparedStatement(
								"INSERT INTO mitarbeiter_deutschland
								SET MitarbeiterID = :id, BisDatum = :bis
								ON DUPLICATE KEY UPDATE MitarbeiterID = :id",
								array(
									':id' => $mitarbeiter_id,
									':bis' => $in_deutschland_bis,
								)
							);
							if (!$stmt->success) {
								die('Database update fail (In Deutschland): ' . $stmt->error);
							}
						}
						if (!empty($e101_gueltig_bis)) {
							$stmt = db()->preparedStatement(
								"INSERT INTO mitarbeiter_e101
								SET MitarbeiterID = :id, GenehmigtBis = :date
								ON DUPLICATE KEY UPDATE MitarbeiterID = :id",
								array(':id' => $mitarbeiter_id, ':date' => $e101_gueltig_bis)
							);
							if (!$stmt->success) {
								die('Database update fail (E101): ' . $stmt->error);
							}
						}
						if (!empty($kindergeld_gueltig_bis)) {
							$stmt = db()->preparedStatement(
								"INSERT INTO mitarbeiter_kindergeld
								SET MitarbeiterID = :id, GenehmigtBis = :date
								ON DUPLICATE KEY UPDATE MitarbeiterID = :id",
								array(':id' => $mitarbeiter_id, ':date' => $kindergeld_gueltig_bis)
							);
							if (!$stmt->success) {
								die('Database update fail (Kindergeld): ' . $stmt->error);
							}
						}
						if (!empty($letzte_g35)) {
							$stmt = db()->preparedStatement(
								"INSERT INTO mitarbeiter_g35
								SET MitarbeiterID = :id, G35Datum = :date
								ON DUPLICATE KEY UPDATE MitarbeiterID = :id",
								array(':id' => $mitarbeiter_id, ':date' => $letzte_g35)
							);
							if (!$stmt->success) {
								die('Database update fail (G35): ' . $stmt->error);
							}
						}
						if (!empty($debr_versandt) || !empty($debr_wann)) {
							$stmt = db()->preparedStatement(
								"INSERT INTO mitarbeiter_debriefing	SET
									MitarbeiterID = :id,
									BogenVersandt = :versandt,
									DurchfuehrungWann = :wann,
									DurchfuehrungWer = :wer,
									Kommentar = :kommentar
								ON DUPLICATE KEY UPDATE MitarbeiterID = :id",
								array(
									':id' => $mitarbeiter_id,
									':versandt' => _dataOrDefault($debr_versandt, '0000-00-00'),
									':wann' => _dataOrDefault($debr_wann, '0000-00-00'),
									':wer' => _dataOrDefault($debr_wer),
									':kommentar' => _dataOrDefault($debr_kommentar),
								)
							);
							if (!$stmt->success) {
								die('Database update fail (Debriefing): ' . $stmt->error);
							}
						}
					}
				}
			} else {
				set_message("Bitte XLS, XLSX oder ODS importieren.", MSG_TYPE_ERR);
			}
		} else{ 
			set_message($_FILES['spreadsheet']['error'], MSG_TYPE_ERR);
		}
	} else {
		print_r($_FILES);
		die('blah');
	}
}

display(APP_TITLE . ' - Upload', 'Upload', 'upload.tpl.php');