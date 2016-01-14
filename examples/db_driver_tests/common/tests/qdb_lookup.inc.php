<?php

//***********************************************************************
// Test qdb_lookup() Function without Bind Parameters
//***********************************************************************
print_header('Test qdb_lookup() Function WITHOUT Bind Parameters');

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
		$col = 'FIRST_NAME';
		$setup = true;
		break;

	default:
		$col = 'first_name';
		$setup = true;
		break;

}

//*****************************************************
// Are We Setup? Run Tests.	
//*****************************************************
if ($setup) {
	$first_name = qdb_lookup($data_source, $strsql0, $col);
	print "{$strsql0}<br/>\n First Name = '$first_name'<br/>\n";
}
else {
	$tmp_msg = 'Invalid Setup.';
	print div($tmp_msg, array('class' => 'message_box no_setup'));
}

//***********************************************************************
// Test qdb_lookup() Function with Bind Parameters
//***********************************************************************
print_header('Test qdb_lookup() Function WITH Bind Parameters');

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
			$col = 'first_name';
			$setup = true;
			break;

		case 'pgsql':
			$strsql0 = 'select * from contacts where id IN ($1, $2, $3, $4)';
			$params = array(1, 2, 3, 4);
			$col = 'first_name';
			$setup = true;
			break;

		case 'oracle':
			$strsql0 = 'select * from contacts where id IN (:p1, :p2, :p3, :p4)';
			$params = array('p1' => 1, 'p2' => 2, 'p3' => 3, 'p4' => 4);
			$col = 'FIRST_NAME';
			$setup = true;
			break;

		case 'db2':
			$strsql0 = 'select * from contacts where id IN (?, ?, ?, ?)';
			$params = array(1, 2, 3, 4);
			$col = 'FIRST_NAME';
			$setup = true;
			break;

		case 'sqlsrv':
			$strsql0 = 'select * from contacts where id IN (?, ?, ?, ?)';
			$params = array(1, 2, 3, 4);
			$col = 'first_name';
			$setup = true;
			break;

	}

	//*****************************************************
	// Are We Setup? Run Tests.	
	//*****************************************************
	if ($setup) {
		$first_name = qdb_lookup($data_source, $strsql0, $col, $params, $qdba_opts);
		print "{$strsql0}<br/>\n First Name = '$first_name'<br/>\n";
	}
	else {
		print div($no_setup_msg, array('class' => 'message_box no_setup'));
	}
}
else {
	print div($no_bind_msg, array('class' => 'message_box notice'));
}

