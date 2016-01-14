<?php

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
$trans_ds_types = array('mysqli', 'pgsql', 'oracle', 'db2', 'sqlsrv');

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
//$data1->data_debug(true);
$data1->set_opt('make_bind_params_refs', 1);

//***********************************************************************
// Delete Rows with ID > 5
//***********************************************************************
$strsql00 = 'delete from contacts where id > 5 or ID not IN (1, 2, 3, 4)';
qdb_list($data_source, $strsql00);

//***********************************************************************
// Messages
//***********************************************************************
$no_setup_msg = 'Invalid Setup.';
$no_bind_msg = 'Data Source Type "' . xhe('em', $data_source_type) . '" does not support bind parameters.';
$no_trans_msg = 'Data Source Type "' . xhe('em', $data_source_type) . '" does not support transactions.';

//***********************************************************************
// Quick Database Actions Options
//***********************************************************************
$qdba_opts = array('debug' => 1);

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
//***********************************************************************
// Delete Rows with ID > 5
//***********************************************************************
//***********************************************************************
$strsql00 = 'delete from contacts where id > 5 or ID not IN (1, 2, 3, 4)';
qdb_list($data_source, $strsql00);

