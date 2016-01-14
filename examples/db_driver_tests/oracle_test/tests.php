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
$strsql = 'select * from contacts where id IN (:id1, :id2)';
$strsql0 = 'select * from contacts where id IN (10, 11)';
$data1->prepare($strsql);
$data1->execute(array('id1' => 10, 'id2' => 11));
print_array($data1->data_key_assoc('ID'));

//***********************************************************************
// Turn off auto commit
//***********************************************************************
$data1->auto_commit(false);

//***********************************************************************
// Insert two test Rows
//***********************************************************************
$strsql2 = 'insert into contacts (id, first_name, last_name, city, state) values (:p1, :p2, :p3, :p4, :p5)';
$data1->prepare($strsql2);
$data1->execute(array(
	'p1' => 10, 
	'p2' => 'bob', 
	'p3' => 'bobson', 
	'p4' => 'toowalk', 
	'p5' => 'MN'
));
$data1->execute(array(
	'p1' => 11, 
	'p2' => 'john', 
	'p3' => 'smith', 
	'p4' => 'Racine', 
	'p5' => 'WI'
));
//print_array(qdb_list($data_source, $strsql0));
$data1->commit();

print_sub_header('Inserted two rows and committed. They should exist...');
$data1->prepare($strsql);
$data1->execute(array('id1' => 10, 'id2' => 11));
$data = $data1->data_key_assoc('ID');
print_array($data);

print_sub_header('Deleted the two rows that were added then rolled back.');
$strsql3 = 'delete from contacts where id IN (:id1, :id2)';
$data1->prepare($strsql3);
$data1->execute(array('id1' => 10, 'id2' => 11));
$data1->rollback();
$data1->commit();

print_sub_header('They should still be there...');
$data1->prepare($strsql);
$data1->execute(array('id1' => 10, 'id2' => 11));
$data = $data1->data_key_assoc('ID');
print_array($data);

print_sub_header('Deleted the two rows that were added then committed.');
$data1->prepare($strsql3);
$data1->execute(array('id1' => 10, 'id2' => 11));
$data1->commit();

print_sub_header('They should gone.');
$data1->prepare($strsql);
$data1->execute(array('id1' => 10, 'id2' => 11));
$data = $data1->data_key_assoc('ID');
print_array($data);

