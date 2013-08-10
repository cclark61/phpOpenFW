<?php

//=================================================================
// Top Module Links
//=================================================================
$top_mod_links = array();

// Basic Tests
$top_mod_links["links"][] = array("link" => $this->page_url, "desc" => "Basic Tests", "image" => xml_escape($db_image));

// Database Transactions
$tmp_link = add_url_params($this->page_url, array("action" => "db_trans"), true);
$top_mod_links["links"][] = array("link" => $tmp_link, "desc" => "Transactions Tests", "image" => xml_escape($db_trans_image));


//=================================================================
// Pull a list records
//=================================================================
$strsql = "select * from contacts order by first_name, last_name";
$contacts = qdb_list($data_source, $strsql);

//=================================================================
// Alter dataset if Oracle or DB2
//=================================================================
lower_rec_keys($driver_type, $contacts);

foreach ($contacts as $key => $contact) {
	extract($contact);
	$edit_link = add_url_params($this->page_url, array("action" => "edit", "id" => $id));
	$delete_link = add_url_params($this->page_url, array("action" => "confirm_delete", "id" => $id));
	$contacts[$key]["edit"] = anchor($edit_link, $edit_image);
	$contacts[$key]["delete"] = anchor($delete_link, $delete_image);
	if (isset($change_id) && $id == $change_id) { $change_row = $key; }
}

//=================================================================
// Record Set List
//=================================================================
$data_order = array();
$data_order["id"] = "ID";
$data_order["first_name"] = "First Name";
$data_order["last_name"] = "Last Name";
$data_order["city"] = "City";
$data_order["state"] = "State";
//$data_order["edit"] = ".";
//$data_order["delete"] = ".";

$table = new rs_list($data_order, $contacts);
$table->empty_message("--");
if (isset($change_row)) { $table->set_row_attr($change_row, "class", "hl_change"); }
$table->identify("", "standard_rs contact_list");
$table->render();

?>
