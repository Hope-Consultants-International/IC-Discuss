<?php

class DB {
    protected $_dbo = null;

    public function __construct($host, $database, $user, $password) {
        $pdostr = 'mysql:host=' . $host . ';dbname=' . $database;
        $conn = null;
        try {
            $conn = new PDO($pdostr, $user, $password);
            $conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        } catch (PDOException $e) {
            error_log(__FILE__ . ': ' . $e->getMessage());
            die('Database Error');
        }
        $this->_dbo = $conn;
		// $this->_dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        return true;
    }

    public function getConnection() {
        return $this->_dbo;
    }

    public function preparedStatement($sql, $vars = array(), $buffer_results = true) {
        // Split $vars into literals and values.
        $literals = array();
        $values = array();
        foreach($vars as $p => $v) {
            if (strpos($p,'%') === 0) {
                $literals[$p] = $v;
            } elseif (strpos($p,':') === 0) {
                $values[$p] = $v;
            }
        }

        $query_settings = array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true);
        if ($buffer_results) {
            $query_settings[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
        }

        // Do the query:
        $q = strtr($sql, $literals);
        $stmt = $this->_dbo->prepare($q, $query_settings);
        if($stmt === false) {
            if(DEBUG) {
                printf('Statement prep failed for: %s<br/>', $sql);
                printf("Reason: %s<br/>", implode(' ', $this->_dbo->errorInfo()));
                printf("Query settings: %s<br/>", implode(',', $query_settings));
            }
            error_log(__FILE__ . ': ' . $this->_dbo->errorInfo());
            die('Database Error');
        }

        $r = $stmt->execute($values);

        if ($r === false) {
            $stmt->foundRows = 0;
            $stmt->success = false;
			$stmt->error = implode(' / ', $stmt->errorInfo());
			$stmt->lastInsertId = null;
        } else {
            $stmt->success = true;
			$stmt->error = '';
			$stmt->lastInsertId = $this->_dbo->lastInsertId();
            $rr = $this->_dbo->query('SELECT FOUND_ROWS()');
            if ($rr) {
                $foundRows = $rr->fetchColumn();
                $stmt->foundRows = $foundRows;
            }
        }
        return $stmt;
    }
	
	/**
     * Don't let user input anywhere near this!
     * Since the PDO::bind* methods escape a STR with 's I couldn't use them. :(
     */
    public function getEnum($table_name, $field_name, $null_value = null) {
        $sql = "SHOW COLUMNS FROM `%table` LIKE :field";
        $st = $this->preparedStatement($sql, array('%table' => $table_name, ':field' => $field_name));

        if ($st->success) {
            $row = $st->fetchObject();
            if ($row === false)
                return false;

            $type_dec = $row->Type;
            if (strtolower(substr($type_dec, 0, 5)) !== 'enum(') {
                return false;
			}

            $values = array();
            foreach(explode(',', substr($type_dec, 5, (strlen($type_dec) - 6))) AS $v) {
                array_push($values, trim($v, "'"));
            }
			
			if ($row->Null = 'YES') {
				array_push($values, $null_value);
			}

            return $values;
        }
        return false;
    }
}
