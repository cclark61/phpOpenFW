<?php

//***********************************************************************
// Create Object Class
//***********************************************************************
if (!class_exists('contact')) {
	class contact extends database_interface_object
	{	
		// Constructor function
		public function __construct()
		{
			$data_source = $GLOBALS['curr_ds'];
			$pkey_col = 'id';
			if ($_SESSION[$data_source]['type'] == 'oracle' || $_SESSION[$data_source]['type'] == 'db2') {
				$pkey_col = strtoupper($pkey_col);
			}
	
			$this->set_data_source($data_source, 'contacts');
			$this->set_pkey($pkey_col, true);
		}
	}
}

?>
