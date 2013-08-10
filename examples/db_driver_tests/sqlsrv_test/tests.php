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
$data1->execute(array(10, 11));
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
$data1->execute(array(10, 'bob', 'bobson', 'toowalk', 'MN'));
$data1->execute(array(11, 'john', 'smith', 'Racine', 'WI'));
//print_array(qdb_list($data_source, $strsql0));
$data1->commit();

print_sub_header('Inserted two rows and committed. They should exist...');
$data1->prepare($strsql);
$data1->execute(array(10, 11));
$data = $data1->data_key_assoc('id');
print_array($data);

print_sub_header('Deleted the two rows that were added then rolled back.');
$strsql3 = 'delete from contacts where id IN (?, ?)';
$data1->prepare($strsql3);
$data1->execute(array(10, 11));
$data1->rollback();
$data1->commit();

print_sub_header('They should still be there...');
$data1->prepare($strsql);
$data1->execute(array(10, 11));
$data = $data1->data_key_assoc('id');
print_array($data);

print_sub_header('Deleted the two rows that were added then committed.');
$data1->prepare($strsql3);
$data1->execute(array(10, 11));
$data1->commit();

print_sub_header('They should gone.');
$data1->prepare($strsql);
$data1->execute(array(10, 11));
$data = $data1->data_key_assoc('id');
print_array($data);

?>
