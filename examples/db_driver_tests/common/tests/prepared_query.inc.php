<?php

//***********************************************************************
// Prepared Query with Bind Parameters
//***********************************************************************
print_header('Prepared Query with Bind Parameters');

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
			$col = 'id';
			$col2 = 'first_name';
			$setup = true;
			break;

		case 'pgsql':
			$strsql0 = 'select * from contacts where id IN ($1, $2, $3, $4)';
			$params = array(1, 2, 3, 4);
			$col = 'id';
			$col2 = 'first_name';
			$setup = true;
			break;

		case 'oracle':
			$strsql0 = 'select * from contacts where id IN (:p1, :p2, :p3, :p4)';
			$params = array('p1' => 1, 'p2' => 2, 'p3' => 3, 'p4' => 4);
			$col = 'ID';
			$col2 = 'FIRST_NAME';
			$setup = true;
			break;

		case 'db2':
			$strsql0 = 'select * from contacts where id IN (?, ?, ?, ?)';
			$params = array(1, 2, 3, 4);
			$col = 'ID';
			$col2 = 'FIRST_NAME';
			$setup = true;
			break;

		case 'sqlsrv':
			$strsql0 = 'select * from contacts where id IN (?, ?, ?, ?)';
			$params = array(1, 2, 3, 4);
			$col = 'id';
			$col2 = 'first_name';
			$setup = true;
			break;

	}

	//*****************************************************
	// Are We Setup? Run Tests.	
	//*****************************************************
	if ($setup) {
		$data1->prepare($strsql0);

		$data1->execute($params);
		print_array($data1->data_assoc_result());
		print_array($data1->data_key_assoc($col2));
		print_array($data1->data_key_val($col, $col2));

	}
	else {
		print div($no_setup_msg, array('class' => 'message_box no_setup'));
	}
}
else {
	print div($no_bind_msg, array('class' => 'message_box notice'));
}

