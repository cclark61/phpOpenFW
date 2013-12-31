<?php
/**
* Data Result Class
* A data result abstraction class used to handle database results.
*
* @package		phpOpenFW
* @subpackage 	Database_Tools
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 11/15/2012 updated: 11/18/2012
*/

//***************************************************************
/**
 * Data Result Class
 * @package		phpOpenFW
 * @subpackage	Database_Tools
 */
//***************************************************************
class data_result {

	//************************************************************************	
	// Class variables
	//************************************************************************
	/**
	* @var string Data source index as specified in the configuration
	**/
	private $data_src;

	/**
	* @var string Data source type (mysql, mysqli, pgsql, ldap, oracle, mssql, db2, sqlsrv, sqlite)
	**/
	private $data_type;

	/**
	* @var Object Internal Data Object 
	**/
	private $data_object;

	//*************************************************************************
	/**
	* Constructor function
	*
	* Initializes data result object
	*
	* @param mixed The result resource id or object
	* @param string A valid data source handle as specified in the configuration
	**/
	//*************************************************************************
	public function __construct($resource, $data_src='', $opts=false)
	{
        //=================================================================
		// Data Source
        //=================================================================
		if ($data_src != '') {
			if (!isset($_SESSION[$data_src])) {
				$this->display_error(__FUNCTION__, "Invalid Data Source '{$data_src}'.");
				return false;
			}
		}
		else {
			if (isset($_SESSION['default_data_source'])) {
				$data_src = $_SESSION['default_data_source'];
			}
			else {
				$this->display_error(__FUNCTION__, 'Data Source not given and default data source is not set.');
				return false;
			}
		}
		$this->data_src = $data_src;

        //=================================================================
		// Data Type
        //=================================================================
        $this->data_type = $_SESSION[$this->data_src]['type'];

        //=================================================================
        // Create Object based on Data Source Type
        //=================================================================

        // Data Library
        $data_lib = dirname(__FILE__) . '/lib/data_result';
        $data_object_lib = $data_lib . '/dr_' . $this->data_type . '.class.php';
        $data_structure_lib = $data_lib . '/dr_structure.class.php';

        // Include necessary data structure libraries
        include_once($data_structure_lib);
        include_once($data_object_lib);

        // Create new Data Object
        $dr_class = 'dr_' . $this->data_type;
        $this->data_object = new $dr_class($resource, $data_src, $opts);
        return $this->data_object;
	}

	//*************************************************************************
	/**
	* Destructor Function
	**/
	//*************************************************************************
    public function __destruct() {}

	//*************************************************************************
	/**
	* Display Error Function
	**/
	//*************************************************************************
	private function display_error($function, $msg)
	{
		$class = __CLASS__;
		trigger_error("Error: [{$class}]::{$function}(): {$msg}");
	}

	//*************************************************************************
	/**
	* Get the number records in a result set. A False value means the value could not be determined.
	**/
	//*************************************************************************
	public function num_rows() { return $this->data_object->num_rows(); }

	//*************************************************************************
	/**
	* Get the number fields in a result set. A False value means the value could not be determined.
	**/
	//*************************************************************************
	public function num_fields() { return $this->data_object->num_fields(); }

	//*************************************************************************
	/**
	* Fetch all rows in a result
	**/
	//*************************************************************************
	public function fetch_all_rows()
	{
		return $this->data_object->fetch_all_rows();
	}

	//*************************************************************************
	/**
	* Fetch a row from the result set
	**/
	//*************************************************************************
	public function fetch_row()
	{
		return $this->data_object->fetch_row();
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
		settype($offset, 'int');
		return $this->data_object->set_result_pointer($offset);
	}

	//*************************************************************************
	/**
	* Get Transaction Option
	* @param string Option Key
	* @return The value of the option or false if it does not exist
	**/
	//*************************************************************************
	public function get_opt($opt)
	{
		return $this->data_object->get_opt($opt);
	}

	//*************************************************************************
	/**
	* Set Transaction Option
	* @param string Option Key
	* @param string Option Value
	**/
	//*************************************************************************
	public function set_opt($opt, $val=false)
	{
		return $this->data_object->set_opt($opt, $val);
	}

	//*************************************************************************
	/**
	* Get Raw Resource
	* @return The raw resource or statement handle returned from the query.
	**/
	//*************************************************************************
	public function get_raw_resource()
	{
		return $this->data_object->get_raw_resource();
	}

}

?>