<?php
//***********************************************************************
//***********************************************************************
// Test Auto Commit Functionality
//***********************************************************************
//***********************************************************************
print_header('Test Auto Commit Functionality');


//***********************************************************************
// Check if Data Source Type Supports Transactions
//***********************************************************************
if (in_array($data_source_type, $bind_ds_types)) {

	//***********************************************************************
	// Turn off auto commit
	//***********************************************************************
	print_sub_header('Turning Auto Commit OFF');
	$data1->auto_commit(false);

	//*****************************************************
	// Set the setup flag to false
	//*****************************************************
	$setup = false;

	//*****************************************************
	// Default Settings / Data
	//*****************************************************
	$strsql0 = 'select * from contacts where id > 30';
	$strsql01 = 'delete from contacts where id > 30';

	$test_vals = array(
		array(
			'id' => '31',
			'first_name' => 'Test1',
			'last_name' => 'Tester',
			'city' => 'Testerville #1',
			'state' => 'A'
		),
		array(
			'id' => '32',
			'first_name' => 'Test2',
			'last_name' => 'Tester',
			'city' => 'Testerville #2',
			'state' => 'B'
		),
		array(
			'id' => '33',
			'first_name' => 'Test3',
			'last_name' => 'Tester',
			'city' => 'Testerville #3',
			'state' => 'C'
		),
		array(
			'id' => '34',
			'first_name' => 'Test4',
			'last_name' => 'Tester',
			'city' => 'Testerville #4',
			'state' => 'D'
		)
	);
	$strsql1 = "
		insert into contacts (id, first_name, last_name, city, state) 
		values (
			{$test_vals[0]['id']}, 
			'{$test_vals[0]['first_name']}', 
			'{$test_vals[0]['last_name']}', 
			'{$test_vals[0]['city']}', 
			'{$test_vals[0]['state']}'
		)
	";
	$strsql2 = "
		insert into contacts (id, first_name, last_name, city, state) 
		values (
			{$test_vals[1]['id']}, 
			'{$test_vals[1]['first_name']}', 
			'{$test_vals[1]['last_name']}', 
			'{$test_vals[1]['city']}', 
			'{$test_vals[1]['state']}'
		)
	";

	//*****************************************************
	// Test Parameters based on Data Source Type
	//*****************************************************
	switch ($data_source_type) {

		case 'mysqli':
			$strsql3 = 'insert into contacts (id, first_name, last_name, city, state) values (?, ?, ?, ?, ?)';
			$params = array(
				'issss', 
				$test_vals[2]['id'], 
				$test_vals[2]['first_name'],
				$test_vals[2]['last_name'],
				$test_vals[2]['city'],
				$test_vals[2]['state']
			);
			$setup = true;
			break;

		case 'pgsql':
			$strsql3 = 'insert into contacts (id, first_name, last_name, city, state) values ($1, $2, $3, $4, $5)';
			$params = array(
				$test_vals[2]['id'], 
				$test_vals[2]['first_name'],
				$test_vals[2]['last_name'],
				$test_vals[2]['city'],
				$test_vals[2]['state']
			);
			$setup = true;
			break;

		case 'oracle':
			$strsql3 = 'insert into contacts (id, first_name, last_name, city, state) values (:p1, :p2, :p3, :p4, :p5)';
			$params = array(
				'p1' => $test_vals[2]['id'], 
				'p2' => $test_vals[2]['first_name'],
				'p3' => $test_vals[2]['last_name'],
				'p4' => $test_vals[2]['city'],
				'p5' => $test_vals[2]['state']
			);
			$setup = true;
			break;

		case 'db2':
			$strsql3 = 'insert into contacts (id, first_name, last_name, city, state) values (?, ?, ?, ?, ?)';
			$params = array(
				$test_vals[2]['id'], 
				$test_vals[2]['first_name'],
				$test_vals[2]['last_name'],
				$test_vals[2]['city'],
				$test_vals[2]['state']
			);
			$setup = true;
			break;

		case 'sqlsrv':
			$strsql3 = 'insert into contacts (id, first_name, last_name, city, state) values (?, ?, ?, ?, ?)';
			$params = array(
				$test_vals[2]['id'], 
				$test_vals[2]['first_name'],
				$test_vals[2]['last_name'],
				$test_vals[2]['city'],
				$test_vals[2]['state']
			);
			$setup = true;
			break;

	}

	//*****************************************************
	// Oracle / DB2: Upper case field names for DIO
	//*****************************************************
	if (in_array($data_source_type, array('oracle', 'db2'))) {
		$test_vals[3] = array(
			'ID' => '34',
			'FIRST_NAME' => 'Test4',
			'LAST_NAME' => 'Tester',
			'CITY' => 'Testerville #4',
			'STATE' => 'D'
		);
	}

	//*****************************************************
	// Are We Setup? Run Tests.	
	//*****************************************************
	if ($setup) {

		//-------------------------------------------------------------
		// No Records Check
		//-------------------------------------------------------------
		print_sub_header('There should be 0 records to start with...');
		print_array(qdb_list($data_source, $strsql0));

		//-------------------------------------------------------------
		// Insert four records using various methods
		//-------------------------------------------------------------
		print_sub_header('Insert 4 records using data_trans::data_query(), qdb_list(), qdb_exec, and DIO Class...');
		include(dirname(__FILE__) . '/auto_commit/insert_four.inc.php');

		//-------------------------------------------------------------
		// There should be four records
		//-------------------------------------------------------------
		print_sub_header('There should be 4 records now...');
		print_array(qdb_list($data_source, $strsql0));

		//-------------------------------------------------------------
		// Rollback
		//-------------------------------------------------------------
		print_sub_header('Rollback...');
		$data1->rollback();

		//-------------------------------------------------------------
		// No Records Check
		//-------------------------------------------------------------
		print_sub_header('There should be 0 records now...');
		print_array(qdb_list($data_source, $strsql0));

		//-------------------------------------------------------------
		// Insert four records using various methods again...
		//-------------------------------------------------------------
		print_sub_header('Insert 4 records using various  methods again...');
		include(dirname(__FILE__) . '/auto_commit/insert_four.inc.php');

		//-------------------------------------------------------------
		// Commit
		//-------------------------------------------------------------
		print_sub_header('Commit...');
		$data1->commit();

		//-------------------------------------------------------------
		// There should be four records
		//-------------------------------------------------------------
		print_sub_header('There should be 4 records now...');
		print_array(qdb_list($data_source, $strsql0));

		//-------------------------------------------------------------
		// Delete the Four Rows
		//-------------------------------------------------------------
		print_sub_header('Delete the 4 rows...');
		qdb_list($data_source, $strsql01);

		//-------------------------------------------------------------
		// There should be no records now
		//-------------------------------------------------------------
		print_sub_header('There should be 0 records now...');
		print_array(qdb_list($data_source, $strsql0));

		//-------------------------------------------------------------
		// Rollback
		//-------------------------------------------------------------
		print_sub_header('Rollback...');
		$data1->rollback();

		//-------------------------------------------------------------
		// There should be four records
		//-------------------------------------------------------------
		print_sub_header('There should be 4 records now...');
		print_array(qdb_list($data_source, $strsql0));

		//-------------------------------------------------------------
		// Delete the Four Rows
		//-------------------------------------------------------------
		print_sub_header('Delete the 4 rows...');
		qdb_list($data_source, $strsql01);

		//-------------------------------------------------------------
		// Commit
		//-------------------------------------------------------------
		print_sub_header('Commit...');
		$data1->commit();

		//-------------------------------------------------------------
		// There should be no records now
		//-------------------------------------------------------------
		print_sub_header('There should be 0 records now...');
		print_array(qdb_list($data_source, $strsql0));

	}
	else {
		print div($no_setup_msg, array('class' => 'message_box no_setup'));
	}

	//***********************************************************************
	// Turn on auto commit
	//***********************************************************************
	$data1->auto_commit(true);
	print_sub_header('Turning Auto Commit ON');

}
else {
	print div($no_trans_msg, array('class' => 'message_box notice'));
}

?>
