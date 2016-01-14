<?php

/**
* Data Result / IBM DB2 Plugin
* A IBM DB2 plugin to the (data_result) class
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
 * dr_db2 Class
 * @package		phpOpenFW
 * @subpackage	Database_Tools
 * @access		private
 */
//***************************************************************
class dr_db2 extends dr_structure
{

	//*************************************************************************
	/**
	* Set the Number of Rows in the current result set
	**/
	//*************************************************************************
	public function set_num_rows()
	{
		$this->num_recs = db2_num_rows($this->resource);
	}

	//*************************************************************************
	/**
	* Set the Number of Fields in the current result set
	**/
	//*************************************************************************
	public function set_num_fields()
	{
		$this->num_fields = db2_num_fields($this->resource);
	}

	//*************************************************************************
	/**
	* Fetch a row from the result set
	**/
	//*************************************************************************
	public function fetch_row()
	{
		if ($this->flags['fetch_all_rows']) {
			return false;
		}

		if ($this->num_fields) {
			$this->flags['fetch_row']++;
			return @db2_fetch_assoc($this->resource);
		}

		return false;
	}

	//*************************************************************************
	/**
	* Fetch all rows in a result
	**/
	//*************************************************************************
	public function fetch_all_rows()
	{
		return $this->fetch_all_rows2();
	}

}

