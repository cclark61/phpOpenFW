<?php

/**
* Data Result / LDAP Plugin
* A LDAP plugin to the (data_result) class
*
* @package		phpOpenFW
* @subpackage 	Database_Tools
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @access		private
* @version 		Started: 11/16/2012 updated: 11/20/2012
*/

//***************************************************************
/**
 * dr_ldap Class
 * @package		phpOpenFW
 * @subpackage	Database_Tools
 * @access		private
 */
//***************************************************************
class dr_ldap extends dr_structure
{

	//*************************************************************************
	/**
	* Set the Number of Rows in the current result set
	**/
	//*************************************************************************
	public function set_num_rows()
	{
		$this->num_recs = ldap_count_entries($this->handle, $this->resource);
	}

	//*************************************************************************
	/**
	* Set the Number of Fields in the current result set
	**/
	//*************************************************************************
	public function set_num_fields()
	{
		$this->num_fields = false;
	}

	//*************************************************************************
	/**
	* Set Result Pointer
	*
	* @param integer The numeric position to set the pointer at.
	**/
	//*************************************************************************
	public function set_result_pointer($offset=0)
	{
		$this->set_opt('fetch_pos', 0);
	}

	//*************************************************************************
	/**
	* Fetch a row from the result set
	**/
	//*************************************************************************
	public function fetch_row()
	{
		if ($this->get_opt('fetch_pos')) {
			return ldap_next_entry($this->handle, $this->resource);
		}
		else {
			return ldap_first_entry($this->handle, $this->resource);
		}
	}

	//*************************************************************************
	/**
	* Fetch all rows in a result
	**/
	//*************************************************************************
	public function fetch_all_rows()
	{
		if (!$this->records_set) {

			// Reset Result Pointer
			$this->set_result_pointer();

			$this->records = ldap_get_entries($this->handle, $this->resource);

		    if ($this->records) {
		    	$this->records_set = true;
		    	$this->num_recs = count($this->records);
		    }
	    }

	    $this->flags['fetch_all_rows']++;
	    return $this->records;
	}

}

