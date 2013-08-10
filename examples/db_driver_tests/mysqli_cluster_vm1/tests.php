<?php
//***********************************************************************
//***********************************************************************
// Database Engine Drivers Testing Script
//***********************************************************************
//***********************************************************************

//***********************************************************************
// Prepared Insert Query with Transactions
//***********************************************************************
print_header('Prepared Insert Query with Transaction');

//***********************************************************************
// No Data with IDs of 10 or 11
//***********************************************************************
print_sub_header('No data to start with…');
$strsql = 'select * from contacts where id IN (?, ?)';
$strsql0 = 'select * from contacts where id IN (10, 11)';
$data1->prepare($strsql);
$affect_rows = $data1->execute(array('ii', 10, 11));
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);
print_array($data1->data_key_assoc('id'));

//***********************************************************************
// Turn off auto commit
//***********************************************************************
$data1->auto_commit(false);

//***********************************************************************
// Insert two test Rows
//***********************************************************************
$strsql2 = 'insert into contacts (id, first_name, last_name, city, state) values (?, ?, ?, ?, ?)';
$data1->prepare($strsql2);

$affect_rows = $data1->execute(array('issss', 10, 'bob', 'bobson', 'toowalk', 'MN'));
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);

$affect_rows = $data1->execute(array('issss', 11, 'john', 'smith', 'Racine', 'WI'));
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);

//print_array(qdb_list($data_source, $strsql0));
$data1->commit();

//***********************************************************************
// Rows should exist
//***********************************************************************
print_sub_header('Inserted two rows and committed. They should exist...');
$data1->prepare($strsql);
$affect_rows = $data1->execute(array('ii', 10, 11));
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);
$data = $data1->data_key_assoc('id');
print_array($data);

//***********************************************************************
// Turn on auto commit
//***********************************************************************
//$data1->auto_commit(true);

//***********************************************************************
// Delete two rows and rollback
//***********************************************************************
print_sub_header('Deleted the two rows that were added then rolled back.');
$strsql3 = 'delete from contacts where id IN (?, ?)';
$data1->prepare($strsql3);
$affect_rows = $data1->execute(array('ii', 10, 11));
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);
$data1->rollback();

//***********************************************************************
// Rows should still exist
//***********************************************************************
print_sub_header('They should still be there...');
$data1->prepare($strsql);
$affect_rows = $data1->execute(array('ii', 10, 11));
$data = $data1->data_key_assoc('id');
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);
print_array($data);

//***********************************************************************
// Delete two rows and commit
//***********************************************************************
print_sub_header('Deleted the two rows that were added then committed.');
$data1->prepare($strsql3);
$affect_rows = $data1->execute(array('ii', 10, 11));
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);
$data1->commit();

//***********************************************************************
// Rows should NOT exist
//***********************************************************************
print_sub_header('They should be gone.');
$data1->prepare($strsql);
$affect_rows = $data1->execute(array('ii', 10, 11));
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);
$data = $data1->data_key_assoc('id');
print_array($data);

//***********************************************************************
//***********************************************************************
// Test Transactions across different DB tools
//***********************************************************************
//***********************************************************************
print_header('Test Transactions across different DB tools');

//***********************************************************************
// No Data with IDs of 10, 11, 12, 13
//***********************************************************************
print_sub_header('No data to start with…');
$strsql = 'select * from contacts where id IN (?, ?, ?, ?)';
$strsql0 = 'select * from contacts where id IN (10, 11, 12, 13)';
$data1->prepare($strsql);
$affect_rows = $data1->execute(array('iiii', 10, 11, 12, 13));
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);
print_array($data1->data_key_assoc('id'));

//***********************************************************************
// Turn off auto commit
//***********************************************************************
$data1->auto_commit(false);

//***********************************************************************
// Insert four test rows:
//***********************************************************************
print_sub_header('Insert four test rows using the data_trans (prepared), qdb_list(), qdb_exec(), and DIO.');

//-----------------------------------------------------------
// "data_trans" Prepared Query
//-----------------------------------------------------------
$strsql2 = 'insert into contacts (id, first_name, last_name, city, state) values (?, ?, ?, ?, ?)';
$data1->prepare($strsql2);
$affect_rows = $data1->execute(array('issss', 10, 'bob', 'bobson', 'Toowalk', 'MN'));

//-----------------------------------------------------------
// "qdb_list()"
//-----------------------------------------------------------
//$data1->execute(array('issss', 11, 'john', 'smith', 'Racine', 'WI'));
qdb_list($data_source, "
	insert into contacts 
		(id, first_name, last_name, city, state) 
		values 
		(11, 'john', 'smith', 'Racine', 'WI')
");

//-----------------------------------------------------------
// "qdb_exec()"
//-----------------------------------------------------------
//$data1->execute(array('issss', 12, 'todd', 'tomom', 'Where', 'FL'));
$id = 12;
$fname = 'todd';
$lname = 'tomrom';
$city = 'Where';
$state = 'FL';
qdb_exec($data_source, $strsql2, array('issss', &$id, &$fname, &$lname, &$city, &$state));

//-----------------------------------------------------------
// "DIO"
//-----------------------------------------------------------
//$data1->execute(array('issss', 13, 'ricky', 'richards', 'Here', 'OH'));

// Create New Object
$contact = new contact(false);
$contact->use_bind_params();
$contact->set_field_data('id', 13);
$contact->set_field_data('first_name', 'ricky');
$contact->set_field_data('last_name', 'richards');
$contact->set_field_data('city', 'Here');
$contact->set_field_data('state', 'OH');
//$contact->print_only();
//print $contact->save();
$contact->save();

//-----------------------------------------------------------
// Commit
//-----------------------------------------------------------
//print_array(qdb_list($data_source, $strsql0));
//$data1->rollback();
$data1->commit();

//***********************************************************************
// Rows should exist
//***********************************************************************
print_sub_header('Inserted four rows and committed. They should exist...');
$data1->prepare($strsql);
$affect_rows = $data1->execute(array('iiii', 10, 11, 12, 13));
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);
$data = $data1->data_key_assoc('id');
print_array($data);

//***********************************************************************
// Delete four rows and rollback
//***********************************************************************
print_sub_header('Deleted the four rows that were added then rolled back.');
$strsql3 = 'delete from contacts where id IN (?, ?, ?, ?, ?)';
$data1->prepare($strsql3);
$affect_rows = $data1->execute(array('iiiii', 0, 10, 11, 12, 13));
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);
$data1->rollback();

//***********************************************************************
// Rows should still exist
//***********************************************************************
print_sub_header('They should still be there...');
$data1->prepare($strsql);
$affect_rows = $data1->execute(array('iiii', 10, 11, 12, 13));
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);
$data = $data1->data_key_assoc('id');
print_array($data);

//***********************************************************************
// Delete four rows and commit
//***********************************************************************
print_sub_header('Deleted the four rows that were added then committed.');
$data1->prepare($strsql3);
$affect_rows = $data1->execute(array('iiiii', 0, 10, 11, 12, 13));
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);
$data1->commit();

//***********************************************************************
// Rows should NOT exist
//***********************************************************************
print_sub_header('They should be gone.');
$data1->prepare($strsql);
$affect_rows = $data1->execute(array('iiii', 10, 11, 12, 13));
$data = $data1->data_key_assoc('id');
print div(xhe('strong', 'Affected Rows: ') . $affect_rows);
print_array($data);

?>
