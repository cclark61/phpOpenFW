<?php

//***********************************************************************
// Standard Query
//***********************************************************************
print_header('Standard Query');
$strsql = 'select * from contacts where id IN (2, 4)';
$data1->data_query($strsql);
print_array($data1->data_assoc_result());

//***********************************************************************
// Standard Query using qdb_list()
//***********************************************************************
print_header('Standard Query using qdb_list()');
$strsql = 'select * from contacts where id IN (1, 3)';
print_array(qdb_list($data_source, $strsql));


?>
