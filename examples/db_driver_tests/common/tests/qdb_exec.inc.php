<?php

//***********************************************************************
// Test qdb_exec() Function with Bind Parameters
//***********************************************************************
print_header('Test qdb_exec() Function, REQUIRES Bind Parameters');

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
			$strsql0 = 'select * from contacts where id IN (?, ?)';
			$params = array('ii', 1, 3);
			$params2 = array('ii', 2, 4);
			$col = 'id';
			$setup = true;
			break;

		case 'pgsql':
			$strsql0 = 'select * from contacts where id IN ($1, $2)';
			$params = array(1, 3);
			$params2 = array(2, 4);
			$col = 'id';
			$setup = true;
			break;

		case 'oracle':
			$strsql0 = 'select * from contacts where id IN (:p1, :p2)';
			$params = array('p1' => 1, 'p2' => 3);
			$params2 = array('p1' => 3, 'p2' => 4);
			$col = 'ID';
			$setup = true;
			break;

		case 'db2':
			$strsql0 = 'select * from contacts where id IN (?, ?)';
			$params = array(1, 3);
			$params2 = array(2, 4);
			$col = 'ID';
			$setup = true;
			break;

		case 'sqlsrv':
			$strsql0 = 'select * from contacts where id IN (?, ?)';
			$params = array(1, 3);
			$params2 = array(2, 4);
			$col = 'id';
			$setup = true;
			break;

	}

	//*****************************************************
	// Are We Setup? Run Tests.	
	//*****************************************************
	if ($setup) {
		print_array(qdb_exec($data_source, $strsql0, $params, $col, $qdba_opts));
		print_array(qdb_exec($data_source, $strsql0, $params2, $col, $qdba_opts));
	}
	else {
		print div($no_setup_msg, array('class' => 'message_box no_setup'));
	}
}
else {
	print div($no_bind_msg, array('class' => 'message_box notice'));
}

