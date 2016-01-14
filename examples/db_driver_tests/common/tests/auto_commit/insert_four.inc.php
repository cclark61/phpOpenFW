<?php

//------------------------------------------------
// Debug Flag
//------------------------------------------------
$tmp_debug = false;

//------------------------------------------------
// Data_Trans Debug Setting
//------------------------------------------------
if ($tmp_debug) { $data1->data_debug(true); }

//------------------------------------------------
// Insert Row #1
//------------------------------------------------
$data1->data_query($strsql1);

//------------------------------------------------
// Insert Row #2
//------------------------------------------------
if ($tmp_debug) { 
	qdb_list($data_source, $strsql2, false, $qdba_opts);
}
else {
	qdb_list($data_source, $strsql2);
}

//------------------------------------------------
// Insert Row #3
//------------------------------------------------
if ($tmp_debug) { 
	qdb_exec($data_source, $strsql3, $params, false, $qdba_opts);
}
else {
	qdb_exec($data_source, $strsql3, $params);
}

//------------------------------------------------
// Insert Row #4
//------------------------------------------------
//print_array($test_vals[3]);
$contact = new contact(false);
$contact->use_bind_params();
$contact->import($test_vals[3]);
$print_only = false;
if ($print_only && $tmp_debug) {
	$contact->print_only();
	print $contact->save();
	$contact->dump('data');
}
else { $contact->save(); }

