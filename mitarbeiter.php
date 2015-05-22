<?php
require_once 'includes/bootstrap.php';

$mitarbeiter = (object) array(
	'MitarbeiterID' => NEW_ENTRY_ID,
	'Nachname' => '', 
	'Vorname' => '',
	'Geburtsdatum' => '',
	'Dienstbeginn' => date('Y-m-d'),
	'Anstellung' => ENUM_NULL_OPTION,
	'G35notwendig' => 0,
	'Lebensversicherung' => '',
	'Archiviert' => 0,
	'Notizen' => '',
	'Kandidatenzeit' => '',
	'TeamStatus' => ENUM_NULL_OPTION,
	'Geschlecht' => 'm',
	'Rueckholversicherung' => 0,
	'RueckholversNummer' => '',
	'RueckholversMerkblatt' => '',
	'Drittlandversicherung' => '',
	'Haftpflichtversicherung' => '',
	'KindergeldBeantragt' => 0,
	'E101Beantragt' => 0,
);
$mitarbeiter_g35 = array();
$mitarbeiter_deutschland = array();
$mitarbeiter_debriefing = array();
$mitarbeiter_kindergeld = array();
$mitarbeiter_e101 = array();

function _requestOrDefault($parameter, $default = '', $null_value = null) {
	$value = (isset($_REQUEST[$parameter])) ? $_REQUEST[$parameter] : $default;
	if ($value === $null_value) {
		$value = null;
	}
	return $value;
}

$mitarbeiter_id = isset($_REQUEST['id']) ? $_REQUEST['id'] : NEW_ENTRY_ID;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'nothing';

// neuer mitarbeiter oder ein alter?
if ($mitarbeiter_id == NEW_ENTRY_ID) {
	$new_mitarbeiter = true;
} else {
	$new_mitarbeiter = false;
}

if ($action == 'save') {	
	// sammle daten fuer mitarbeiter tabelle update
    $values = array(
		':nachname' => _requestOrDefault('Nachname'),
		':vorname' => _requestOrDefault('Vorname'),
		':geburtsdatum' => _requestOrDefault('Geburtsdatum'),
		':dienstbeginn' => _requestOrDefault('Dienstbeginn'),
		':anstellung' => _requestOrDefault('Anstellung', null, ENUM_NULL_OPTION),
		':g35' => _requestOrDefault('G35notwendig', 0),
		':lebensversicherung' => _requestOrDefault('Lebensversicherung'),
		':notizen' => _requestOrDefault('Notizen', 0),
		':archiv' => _requestOrDefault('Archiviert', 0),
		':kandidatenzeit' => _requestOrDefault('Kandidatenzeit'),
		':status' => _requestOrDefault('TeamStatus', null, ENUM_NULL_OPTION),
		':geschlecht' => _requestOrDefault('Geschlecht', 'm'),
		':rueckhol' => _requestOrDefault('Rueckholversicherung', 0),
		':rueckholnum' => _requestOrDefault('RueckholversNummer'),
		':rueckholmb' => _requestOrDefault('RueckholversMerkblatt', 0),
		':drittland' => _requestOrDefault('Drittlandversicherung'),
		':haftpflicht' => _requestOrDefault('Haftpflichtversicherung'),
		':kindergeld' => _requestOrDefault('KindergeldBeantragt', 0),
		':e101' => _requestOrDefault('E101Beantragt', 0),
	);
	if (empty($values[':kandidatenzeit'])) {
		$values[':kandidatenzeit'] = null;
	}
	if (empty($values[':dienstbeginn'])) {
		$values[':dienstbeginn'] = null;
	}
	// entweder is das neu, oder ein update
	if ($new_mitarbeiter) {
		$query = "INSERT INTO `mitarbeiter` SET
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
	} else {
		$query = "UPDATE `mitarbeiter` SET
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
		  WHERE MitarbeiterID = :id
		";
		$values[':id'] = $mitarbeiter_id;
	}
	$stmt = db()->preparedStatement($query, $values);
	if (!$stmt->success) {
		die('Database update fail: ' . $stmt->error);
	} elseif ($new_mitarbeiter) {
		$mitarbeiter_id = $stmt->lastInsertId;
	}
	
	// G35
	// alte daten loeschen
	$stmt = db()->preparedStatement(
		"DELETE FROM mitarbeiter_g35 WHERE MitarbeiterID = :id",
		array(':id' => $mitarbeiter_id)
	);
	$g35_dates = _requestOrDefault('G35', array());
	// neue einfuegen
	foreach ($g35_dates as $date) {
		if (!empty($date)) {
			$stmt = db()->preparedStatement(
				"INSERT INTO mitarbeiter_g35
				SET MitarbeiterID = :id, G35Datum = :date
				ON DUPLICATE KEY UPDATE MitarbeiterID = :id",
				array(':id' => $mitarbeiter_id, ':date' => $date)
			);
			if (!$stmt->success) {
				die('Database update fail (G35): ' . $stmt->error);
			}
		}
	}
	
	// In Deutschland
	// alte daten loeschen
	$stmt = db()->preparedStatement(
		"DELETE FROM mitarbeiter_deutschland WHERE MitarbeiterID = :id",
		array(':id' => $mitarbeiter_id)
	);
	$ind = _requestOrDefault('deutschland', array());
	// neue einfuegen
	foreach ($ind as $data) {
		if (!empty($data['von']) || !empty($data['bis'])) {
			$stmt = db()->preparedStatement(
				"INSERT INTO mitarbeiter_deutschland
				SET MitarbeiterID = :id, VonDatum = :von, BisDatum = :bis, Kommentar = :kommentar
				ON DUPLICATE KEY UPDATE MitarbeiterID = :id",
				array(
					':id' => $mitarbeiter_id,
					':von' => isset($data['von']) ? $data['von'] : '',
					':bis' => isset($data['bis']) ? $data['bis'] : '',
					':kommentar' => isset($data['kommentar']) ? $data['kommentar'] : '',
				)
			);
			if (!$stmt->success) {
				die('Database update fail (In Deutschland): ' . $stmt->error);
			}
		}
	}
	
	// Debriefing
	// alte daten loeschen
	$stmt = db()->preparedStatement(
		"DELETE FROM mitarbeiter_debriefing WHERE MitarbeiterID = :id",
		array(':id' => $mitarbeiter_id)
	);
	$ind = _requestOrDefault('debriefing', array());
	// neue einfuegen
	foreach ($ind as $data) {
		if (!empty($data['versandt']) || !empty($data['wann'])) {
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
					':versandt' => (isset($data['versandt']) && !empty($data['versandt'])) ? $data['versandt'] : '0000-00-00',
					':wann' => (isset($data['wann']) && !empty($data['wann'])) ? $data['wann'] : '0000-00-00',
					':wer' => isset($data['wer']) ? $data['wer'] : '',
					':kommentar' => isset($data['kommentar']) ? $data['kommentar'] : '',
				)
			);
			if (!$stmt->success) {
				die('Database update fail (Debriefing): ' . $stmt->error);
			}
		}
	}
	
	// Kindergeld
	// alte daten loeschen
	$stmt = db()->preparedStatement(
		"DELETE FROM mitarbeiter_kindergeld WHERE MitarbeiterID = :id",
		array(':id' => $mitarbeiter_id)
	);
	$kindergeld_dates = _requestOrDefault('kindergeld', array());
	// neue einfuegen
	foreach ($kindergeld_dates as $date) {
		if (!empty($date)) {
			$stmt = db()->preparedStatement(
				"INSERT INTO mitarbeiter_kindergeld
				SET MitarbeiterID = :id, GenehmigtBis = :date
				ON DUPLICATE KEY UPDATE MitarbeiterID = :id",
				array(':id' => $mitarbeiter_id, ':date' => $date)
			);
			if (!$stmt->success) {
				die('Database update fail (Kindergeld): ' . $stmt->error);
			}
		}
	}
	
	// E101
	// alte daten loeschen
	$stmt = db()->preparedStatement(
		"DELETE FROM mitarbeiter_e101 WHERE MitarbeiterID = :id",
		array(':id' => $mitarbeiter_id)
	);
	$e101_dates = _requestOrDefault('E101', array());
	// neue einfuegen
	foreach ($e101_dates as $date) {
		if (!empty($date)) {
			$stmt = db()->preparedStatement(
				"INSERT INTO mitarbeiter_e101
				SET MitarbeiterID = :id, GenehmigtBis = :date
				ON DUPLICATE KEY UPDATE MitarbeiterID = :id",
				array(':id' => $mitarbeiter_id, ':date' => $date)
			);
			if (!$stmt->success) {
				die('Database update fail (E101): ' . $stmt->error);
			}
		}
	}
	
	if (_requestOrDefault('back', false)) {
		header('Location: ' . substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['REQUEST_URI'], "/")+1) . 'index.php', true, 302);
		exit;
	}
	
	// wenn das ein neuer Mitarbeiter war, dann aendern wir die URL
	if ($new_mitarbeiter) {
		header('Location: ' . strtok($_SERVER["REQUEST_URI"],'?') . '?id=' . $mitarbeiter_id, true, 302);
		exit;
	}
}

// lade daten aus det datenbank wenn das kein neuer Mitarbeiter ist.
if (!$new_mitarbeiter) {
	
	// "Stamm" daten
	$stmt = db()->preparedStatement("
		SELECT
			MitarbeiterID,
			Nachname,
			Vorname,
			Geburtsdatum,
			Dienstbeginn,
			COALESCE(Anstellung, :null_option) AS Anstellung,
			G35notwendig,
			Lebensversicherung,
			Notizen,
			Archiviert,
			Kandidatenzeit,
			COALESCE(TeamStatus, :null_option) AS TeamStatus,
			Geschlecht,
			Rueckholversicherung,
			RueckholversNummer,
			RueckholversMerkblatt,
			Drittlandversicherung,
			Haftpflichtversicherung,
			KindergeldBeantragt,
			E101Beantragt
		FROM mitarbeiter
		WHERE MitarbeiterID = :id",
		array(':id' => $mitarbeiter_id, ':null_option' => ENUM_NULL_OPTION));
	if ($obj = $stmt->fetchObject()) {
		$mitarbeiter = $obj;
	}
	
	// G35 daten
	$stmt = db()->preparedStatement("
		SELECT G35Datum FROM mitarbeiter_g35
		WHERE MitarbeiterID = :id
		ORDER BY G35Datum DESC",
		array(':id' => $mitarbeiter_id)
	);
	if ($stmt->success) {
		while ($obj = $stmt->fetchObject()) {
			$mitarbeiter_g35[] = $obj->G35Datum;
		}
	}
	
	// In Deutschland
	$stmt = db()->preparedStatement("
		SELECT VonDatum, BisDatum, Kommentar FROM mitarbeiter_deutschland
		WHERE MitarbeiterID = :id
		ORDER BY BisDatum, VonDatum DESC",
		array(':id' => $mitarbeiter_id)
	);
	if ($stmt->success) {
		while ($obj = $stmt->fetchObject()) {
			$mitarbeiter_deutschland[] = $obj;
		}
	}
	
	// Debriefing
	$stmt = db()->preparedStatement("
		SELECT BogenVersandt, DurchfuehrungWann, DurchfuehrungWer, Kommentar FROM mitarbeiter_debriefing
		WHERE MitarbeiterID = :id
		ORDER BY BogenVersandt DESC",
		array(':id' => $mitarbeiter_id)
	);
	if ($stmt->success) {
		while ($obj = $stmt->fetchObject()) {
			$mitarbeiter_debriefing[] = $obj;
		}
	}
	
	// Kindergeld daten
	$stmt = db()->preparedStatement("
		SELECT GenehmigtBis FROM mitarbeiter_kindergeld
		WHERE MitarbeiterID = :id
		ORDER BY GenehmigtBis DESC",
		array(':id' => $mitarbeiter_id)
	);
	if ($stmt->success) {
		while ($obj = $stmt->fetchObject()) {
			$mitarbeiter_kindergeld[] = $obj->GenehmigtBis;
		}
	}
	
	// E101 daten
	$stmt = db()->preparedStatement("
		SELECT GenehmigtBis FROM mitarbeiter_e101
		WHERE MitarbeiterID = :id
		ORDER BY GenehmigtBis DESC",
		array(':id' => $mitarbeiter_id)
	);
	if ($stmt->success) {
		while ($obj = $stmt->fetchObject()) {
			$mitarbeiter_e101[] = $obj->GenehmigtBis;
		}
	}
}

$vars = array(
	'new_mitarbeiter' => $new_mitarbeiter,
	'mitarbeiter' => $mitarbeiter,
	'mitarbeiter_g35' => $mitarbeiter_g35,
	'mitarbeiter_deutschland' => $mitarbeiter_deutschland,
	'mitarbeiter_debriefing' => $mitarbeiter_debriefing,
	'mitarbeiter_kindergeld' => $mitarbeiter_kindergeld,
	'mitarbeiter_e101' => $mitarbeiter_e101,
	'request' => $_REQUEST,
	'anstellungen' => db()->getEnum('mitarbeiter', 'Anstellung', ENUM_NULL_OPTION),
	'team_stati' => db()->getEnum('mitarbeiter', 'TeamStatus', ENUM_NULL_OPTION),
	'geschlechter' => db()->getEnum('mitarbeiter', 'Geschlecht'),
);
$current_page = ($new_mitarbeiter) ? 'Neu' : '';
display(APP_TITLE, $current_page, 'mitarbeiter.tpl.php', $vars);