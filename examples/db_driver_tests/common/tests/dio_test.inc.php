<?php
//***********************************************************************
//***********************************************************************
// Test Database Interface Object
//***********************************************************************
//***********************************************************************
print_header('Test Database Interface Object');

// Create New Object
$contact = new contact();

// Dump Table Info
print_sub_header('Create Object, Show Table Info');
$contact->dump();

// Load a new record and dump it to screen
print_sub_header('Load Empty Record');
$contact->load('');
$contact->dump('data');

// Load an existing record and dump it to screen
print_sub_header('Load Existing Record');
$contact->load(1);
$contact->dump('data');

?>
