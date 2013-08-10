<?php
//***********************************************************************
//***********************************************************************
// Database Engine Drivers Testing Script
//***********************************************************************
//***********************************************************************
include('pre_page.inc.php');

// Data Source
$data_source = 'test_sqlsrv';
$GLOBALS['curr_ds'] = $data_source;

// Bind Values
$param1 = 1;
$param2 = 2;

// Start Benchmark
load_plugin('benchmark');
$cb = new code_benchmark();
$cb->start_timer();

//*******************************************************
// Get Driver Type
//*******************************************************
if (isset($_SESSION[$data_source])) { $driver_type = $_SESSION[$data_source]['type']; }
else {
	$warn_message = "Data source '{$data_source}' does not exist.";
	return false;
}

//*******************************************************
// Save Current Data Source and Type Globally
//*******************************************************
$GLOBALS['curr_ds'] = $data_source;
$data_source_type = $_SESSION[$data_source]['type'];
$GLOBALS['curr_ds_type'] = $data_source_type;
$bind_ds_types = array('mysqli', 'pgsql', 'oracle', 'db2', 'sqlsrv');

//*******************************************************
// Display Data Soruce and it's Type
//*******************************************************
print div(
	xhe('p', xhe('strong', 'Data Source is: ') . xhe('em', $data_source))
	.xhe('p', xhe('strong', 'Datasource Type is: '). xhe('em', $data_source_type))
, array('class' => 'sub_title'));

//*******************************************************
// Local Functions
//*******************************************************
include('local.func.php');
include('tests/contact.class.php');

//*******************************************************
// Test Directory
//*******************************************************
$test_dir = dirname(__FILE__) . '/tests';

//*******************************************************
// New Data Transaction
//*******************************************************
$data1 = new data_trans($data_source);
$data1->data_debug(true);
$data1->set_opt('make_bind_params_refs', 1);

//***********************************************************************
// Delete Rows with ID > 5
//***********************************************************************
$strsql00 = 'delete from contacts where id > 5';
qdb_list($data_source, $strsql00);

//***********************************************************************
// Messages
//***********************************************************************
$no_setup_msg = 'Invalid Setup.';
$no_bind_msg = 'Data Source Type "' . xhe('em', $data_source_type) . '" does not support bind parameters.';

//***********************************************************************
//***********************************************************************
// Begin Tests
//***********************************************************************
//***********************************************************************

//======================================================
// Record Set List
//======================================================
include("{$test_dir}/rs_list.inc.php");

//======================================================
// Standard Query
//======================================================
include("{$test_dir}/standard_query.inc.php");

//======================================================
// Prepared Query
//======================================================
include("{$test_dir}/prepared_query.inc.php");

//======================================================
// qdb_exec() Tests
//======================================================
include("{$test_dir}/qdb_exec.inc.php");

//======================================================
// qdb_lookup() Tests
//======================================================
include("{$test_dir}/qdb_lookup.inc.php");

//======================================================
// qdb_row() Tests
//======================================================
include("{$test_dir}/qdb_row.inc.php");

//======================================================
// Database Interface Object Tests
//======================================================
include("{$test_dir}/dio_test.inc.php");

//======================================================
// Auto Commit Tests
//======================================================
include("{$test_dir}/auto_commit.inc.php");

//***********************************************************************
// Prepared Insert Query with Transactions
//***********************************************************************
print_header('Prepared Insert Query with Transaction');

// No Data with IDs of 10 or 11
$strsql = 'select * from contacts where id IN (?, ?)';
$strsql0 = 'select * from contacts where id IN (10, 11)';
$param1 = 10;
$param2 = 11;
$data1->prepare($strsql, array(&$param1, &$param2));
$data1->execute();
print_array($data1->data_key_assoc('id'));

// Turn off auto commit
$data1->auto_commit(false);

// Insert two test Rows
$strsql2 = 'insert into contacts (id, first_name, last_name, city, state) values (?, ?, ?, ?, ?)';

$params = array(10, 'bob', 'bobson', 'toowalk', 'MN');
$data1->prepare($strsql2);
$data1->execute($params);

$params = array(11, 'john', 'smith', 'Racine', 'WI');
$data1->prepare($strsql2);
$data1->execute($params);

$data1->commit();

print_sub_header('Inserted two rows and committed. They should exist...');
$data1->prepare($strsql);
$data1->execute(array(&$param1, &$param2));
$data = $data1->data_key_assoc('id');
print_array($data);

print_sub_header('Deleted the two rows that were added then rolled back.');
$strsql3 = 'delete from contacts where id IN (?, ?)';
$data1->prepare($strsql3);
$data1->execute(array(&$param1, &$param2));
$data1->rollback();
//$data1->commit();

print_sub_header('They should still be there...');
$data1->prepare($strsql);
$data1->execute(array(&$param1, &$param2));
$data = $data1->data_key_assoc('id');
print_array($data);

print_sub_header('Deleted the two rows that were added then committed.');
$data1->prepare($strsql3);
$data1->execute(array(&$param1, &$param2));
$data1->commit();

print_sub_header('They should gone.');
$data1->prepare($strsql);
$data1->execute(array(&$param1, &$param2));
$data = $data1->data_key_assoc('id');
print_array($data);

// Stop Benchmark
$cb->stop_timer();
$cb->print_results(true);

?>
