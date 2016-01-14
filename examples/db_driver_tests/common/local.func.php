<?php

//***********************************************************************
// Lowercase Record Keys
//***********************************************************************
function lower_rec_keys($driver_type, &$records)
{
	if ($driver_type == 'oracle' || $driver_type == 'db2') {
		foreach ($records as $key => $rec) {
			$new_rec = array();
			foreach ($rec as $field => $data) {
				unset($rec[$field]);
				$new_key = strtolower($field);
				$new_rec[$new_key] = $data;
			}
			$records[$key] = $new_rec;
		}
	}
}

