<?php

/**
* Data Result / MySQL Plugin
* A MySQL plugin to the (data_result) class
*
* @package		phpOpenFW
* @subpackage 	Database_Tools
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @access		private
* @version 		Started: 11/16/2012 updated: 11/17/2012
*/

//***************************************************************
/**
 * dr_mysql Class
 * @package		phpOpenFW
 * @subpackage	Database_Tools
 * @access		private
 */
//***************************************************************
class dr_mysql extends dr_structure
{

	//*************************************************************************
	/**
	* Set the Number of Rows in the current result set
	**/
	//*************************************************************************
	public function set_num_rows()
	{
		$this->num_recs = mysql_num_rows($this->resource);
	}

	//*************************************************************************
	/**
	* Set the Number of Fields in the current result set
	**/
	//*************************************************************************
	public function set_num_fields()
	{
		$this->num_fields = mysql_num_fields($this->resource);
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
		return mysql_data_seek($this->resource, $offset);
	}

	//*************************************************************************
	/**
	* Fetch a row from the result set
	**/
	//*************************************************************************
	public function fetch_row()
	{
		return mysql_fetch_assoc($this->resource);
	}

}

