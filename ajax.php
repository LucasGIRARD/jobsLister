<?php
include 'SQL.php';
$connection = openSQLConnexion();

switch ($_GET['action']) {
	case 'updateCompanyType':
	$type = (empty($_GET['type'])?NULL:$_GET['type']);
	$queryOK = insertUpdate($connection, 'UPDATE COMPANIES SET TYPES_id=? WHERE id=?', array(array($type,$_GET['id'])));
	break;
	case 'insertFilter':
	$filter = (empty($_GET['filter'])?NULL:$_GET['filter']);
	$queryOK = insertUpdate($connection, 'INSERT INTO FILTERS (value) VALUES (?)', array(array($filter)));

	$queryOK = insertUpdate($connection, 'UPDATE JOBS SET filtered=? WHERE name=?', array(array($type,$_GET['id'])));



	break;
	default:
	break;
}

if (isset($return) && !empty($return)) {
	echo json_encode($return);
} else {
	echo ' ';
}


closeSQLConnexion($connection);
?>