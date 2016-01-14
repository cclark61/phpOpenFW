<?php

//***********************************************************************
// Test qdb_row() Function without Bind Parameters
//***********************************************************************
print_header('Test qdb_row() Function WITHOUT Bind Parameters');

//*****************************************************
// Set the setup flag to false
//*****************************************************
$setup = false;
$strsql0 = 'select * from contacts where id IN (1, 2, 3, 4)';

//*****************************************************
// Test Parameters based on Data Source Type
//*****************************************************
switch ($data_source_type) {

	case 'oracle':
	case 'db2':
		$cols = array(0, 'ID:FIRST_NAME', 'ID', 'FIRST_NAME:CITY', 'FIRST_NAME');
		$cols = array(0, 'ID:FIRST_NAME', 'ID', 'FIRST_NAME:CITY', 'FIRST_NAME');
		$setup = true;
		break;

	default:
		$cols = array(0, 'id:first_name', 'id', 'first_name:city', 'first_name');
		$setup = true;
		break;

}

//*****************************************************
// Are We Setup? Run Tests.	
//*****************************************************
if ($setup) {
	//print_array($cols);
	print_array(qdb_row($data_source, $strsql0, $cols[0]));
	print qdb_row($data_source, $strsql0, '3', $cols[1]);
	print_array(qdb_row($data_source, $strsql0, '3', $cols[2]));
	print qdb_row($data_source, $strsql0, 'Chris', $cols[3]);
	print_array(qdb_row($data_source, $strsql0, 'Chris', $cols[4]));
}
else {
	$tmp_msg = 'Invalid Setup.';
	print div($tmp_msg, array('class' => 'message_box no_setup'));
}

//***********************************************************************
// Test qdb_row() Function with Bind Parameters
//***********************************************************************
print_header('Test qdb_row() Function WITH Bind Parameters');

if (in_array($data_source_type, $bind_ds_types)) {

	//*****************************************************
	// Set the setup flag to false
	//*****************************************************
	$setup = false;

	//*****************************************************
	// Test Parameters based on Data Source Type
	//*****************************************************
	switch ($data_source_type) {

		case 'mysqli':
			$strsql0 = 'select * from contacts where id IN (?, ?, ?, ?)';
			$params = array('iiii', 1, 2, 3, 4);
			$cols = array(0, 'id:first_name', 'id', 'first_name:city', 'first_name');
			$setup = true;
			break;

		case 'pgsql':
			$strsql0 = 'select * from contacts where id IN ($1, $2, $3, $4)';
			$params = array(1, 2, 3, 4);
			$cols = array(0, 'id:first_name', 'id', 'first_name:city', 'first_name');
			$setup = true;
			break;

		case 'oracle':
			$strsql0 = 'select * from contacts where id IN (:p1, :p2, :p3, :p4)';
			$params = array('p1' => 1, 'p2' => 2, 'p3' => 3, 'p4' => 4);
			$cols = array(0, 'ID:FIRST_NAME', 'ID', 'FIRST_NAME:CITY', 'FIRST_NAME');
			$setup = true;
			break;

		case 'db2':
			$strsql0 = 'select * from contacts where id IN (?, ?, ?, ?)';
			$params = array(1, 2, 3, 4);
			$cols = array(0, 'ID:FIRST_NAME', 'ID', 'FIRST_NAME:CITY', 'FIRST_NAME');
			$setup = true;
			break;

		case 'sqlsrv':
			$strsql0 = 'select * from contacts where id IN (?, ?, ?, ?)';
			$params = array(1, 2, 3, 4);
			$cols = array(0, 'id:first_name', 'id', 'first_name:city', 'first_name');
			$setup = true;
			break;

	}

	//*****************************************************
	// Are We Setup? Run Tests.	
	//*****************************************************
	if ($setup) {
		//print_array($cols);
		print_array(qdb_row($data_source, $strsql0, $cols[0], false, $params));
		print qdb_row($data_source, $strsql0, '3', $cols[1], $params);
		print_array(qdb_row($data_source, $strsql0, '3', $cols[2], $params));
		print qdb_row($data_source, $strsql0, 'Chris', $cols[3], $params);
		print_array(qdb_row($data_source, $strsql0, 'Chris', $cols[4], $params));
	}
	else {
		print div($no_setup_msg, array('class' => 'message_box no_setup'));
	}
}
else {
	print div($no_bind_msg, array('class' => 'message_box notice'));
}

