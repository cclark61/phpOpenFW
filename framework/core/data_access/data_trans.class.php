<?php
/**
* Data Transaction Class
* A data abstraction class used to handle all database transactions. This class is a controller to the data object class it uses.
*
* @package		phpOpenFW
* @subpackage 	Database_Tools
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 9-20-2005 updated: 8-27-2014
*/

//***************************************************************
/**
 * Data Transaction Class
 * @package		phpOpenFW
 * @subpackage	Database_Tools
 */
//***************************************************************
class data_trans {

	//************************************************************************	
	// Class variables
	//************************************************************************
	/**
	* @var string data source type (mysql, mysqli, pgsql, ldap, oracle, mssql, db2, sqlsrv, sqlite)
	**/
	private $data_type;
	
	/**
	* @var bool Print the queries run through this transaction (Yes or No)
	**/
	private $print_query;

	/**
	* @var Object Internal Data Object 
	**/
	private $data_object;

	/**
	* Constructor function
	*
	* Initializes data transaction, setting all necessary variables from specified data source
	*
	* @param string The name of data source to use as specified in config.inc.php
	**/
	//*************************************************************************
	// Constructor function
	//*************************************************************************
	public function __construct($data_src='', $tmp_trans_type='qry')
	{
		// Data Source
		if ($data_src != '') {
			if (!isset($_SESSION[$data_src])) {
				trigger_error("Error: [data_trans]::__construct(): Invalid Data Source '{$data_src}'.");
				return false;
			}
		}
		else {
			if (isset($_SESSION['default_data_source'])) {
				$data_src = $_SESSION['default_data_source'];
			}
			else {
				trigger_error('Error: [data_trans]::__construct(): Data Source not given and default data source is not set.');
				return false;
			}
		}

		// Driver
        $this->data_type = $_SESSION[$data_src]['type'];

        // Include Data Result Object
        require_once(dirname(__FILE__) . '/data_result.class.php');

        //=================================================================
        // Create Object based on Data Source Type
        //=================================================================

        // Data Library Paths
        $data_lib = dirname(__FILE__) . '/lib/data_trans';
        $data_structure_lib = $data_lib . '/dt_structure.class.php';
        $data_object_lib = $data_lib . '/dt_' . $this->data_type . '.class.php';

        // Include necessary Data Object Libraries
        require_once($data_structure_lib);
        require_once($data_object_lib);

        // Create new Data Object
        $dt_class = 'dt_' . $this->data_type;
        $this->data_object = new $dt_class($data_src, $tmp_trans_type);

		// Check if we are setting the character set
		if (!empty($_SESSION[$data_src]['charset'])) {
			$this->data_object->set_opt('charset', $_SESSION[$data_src]['charset']);
		}
		
        return $this->data_object;
	}

	//*************************************************************************
	/**
	* Destructor Function
	**/
	//*************************************************************************
    public function __destruct() { $this->data_object->shutdown(); }

	/**
	* Executes a query based on the data source type
	* @param mixed MySQL: SQL Statement, PostgreSQL: SQL Statement, LDAP: properly formatted and filled array
	* @param array Bind Parameters (Optional)
	**/
	//*************************************************************************
	// Execute a query and store the record set
	//*************************************************************************
	public function data_query($query, $params=false)
	{
		// Unset last_id / Clear results
		$this->data_object->clear_last_insert_id();
		$this->data_object->clear_result();

		// Execute the Query
		$query_result = $this->data_object->query($query, $params);

		// Print Query (if necessary)
		if ($this->print_query) { $this->display_query(); }

		// Return Results
		return $query_result;
	}

	/**
	* Bind to a Data Source (Used for Authentication Primarily)
	* @param string userid of the user trying to bind to data source
	* @param string password of the user trying to bind to data source
	**/
	//*************************************************************************
	// Bind to a data source as a specified user
	// *** Used for Authentication
	//*************************************************************************
	private function data_bind($user, $pass)
	{
		return $this->data_object->bind($user, $pass);
	}

	/**
	* Bind to the Data Source with user and password specified in the configuration
	**/
	//*************************************************************************
	// Bind to a data source as the configured user
	//*************************************************************************
	public function data_admin_bind()
	{
		return $this->data_bind($this->data_object->get_user(), $this->data_object->get_pass());
	}
	
	/**
	* Bind to the Data Source with the provided user and password
	**/
	//*************************************************************************
	// Bind to a data source as a specified user
	//*************************************************************************
	public function data_user_bind($user, $pass)
	{
		return $this->data_bind($user, $pass);
	}
	
	/**
	* Return the current record set in the form of an associative array
	* @return array current record set in the form of an associate array 
	**/
	//*************************************************************************
	// Extract the record set to local variables
	//*************************************************************************
	public function data_assoc_result()
	{
		return $this->data_object->assoc_result();
	}
	
	/**
	* Return an abbreviated form of the current record set in the form of an associative array
	* @param string field to be used as the 'key' in the associative array
	* @param string field to be used as the 'value' in the associative array
	* @return array abbreviated form of the current record set in the form of an associative array 
	**/
	//*************************************************************************
	// Extract an abbreviated record set to a 'key' => 'value' array
	//*************************************************************************
	public function data_key_val($key, $value)
	{
		return $this->data_object->key_val_result($key, $value);
	}

	//*************************************************************************
	/**
	* Return the current record set in the form of an associative array with $key as the key for each record
	* @param string The index of the field to be used as the 'key' for each record
	* @return array Return the current record set in the form of an associative array with $key as the key for each record
	**/
	//*************************************************************************
	public function data_key_assoc($key)
	{
		return $this->data_object->key_assoc_result($key);
	}

	/**
	* Return ID of the last inserted row for the current session
	* @return integer ID of the last inserted row for the current session
	**/
	//*************************************************************************
	// Return the ID of the last inserted row
	//*************************************************************************
	public function last_insert_id()
	{
		return $this->data_object->last_insert_id();
	}

	/**
	* Set the current transaction type
	* @param string Query Type: '?' - query (default, all SQL queries), '+' - add (LDAP), '~' - modify (LDAP), 
	* '-' - delete (LDAP), '?1' - one level query (LDAP)
	**/
	//*************************************************************************
	// Set the Transaction type
	// '?' - query (default), '+' - add, '~' - modify, '-' - delete
	// '?1' - one level query (LDAP)
	//*************************************************************************
	public function set_trans_type($in_type)
	{
		switch ($in_type) {
			case '+':
				$tmp_trans_type = 'add';
				break;
				
			case '~':
				$tmp_trans_type = 'mod';
				break;
				
			case '-':
				$tmp_trans_type = 'del';
				break;
				
			case '?1':
				$tmp_trans_type = 'qry1';
				break;
					
			default:
				$tmp_trans_type = 'qry';
				break;
		}
		$this->data_object->set_trans_type($tmp_trans_type);
	}
	
	//*************************************************************************
	/**
	* Return the number rows in the current record set
	* @return the number rows in the current record set
	**/
	//*************************************************************************
	public function data_num_rows()
	{
		return $this->data_object->num_rows();
	}

	//*************************************************************************
	/**
	* Return the number fields in the current record set
	* @return integer The number fields in the current record set
	**/
	//*************************************************************************
	public function data_num_fields()
	{
		return $this->data_object->num_fields();
	}

	//*************************************************************************
	/**
	* Return the number rows in the current record set
	* @return the number rows in the current record set
	**/
	//*************************************************************************
	public function data_affected_rows()
	{
		return $this->data_object->affected_rows();
	}

	//*************************************************************************
	/**
	* Show the current record set in raw format
	**/
	//*************************************************************************	
	public function data_raw_output()
	{
		print "<pre>\n";
		print_r($this->data_object->assoc_result());
		print "</pre>\n";
	}

	//*************************************************************************
	/**
	* Print all queries run through this transaction
	* @param bool True (default) = Enable print of queries, False = Disable printing of queries
	**/
	//*************************************************************************
	public function data_debug($print_queries=true) { $this->print_query = (bool)$print_queries; }

	//*************************************************************************
	/**
	* Return whether the current user is bound to the current data source
	* @return whether the current user is bound to the current data source
	**/
	//*************************************************************************
	public function is_bound() { return $this->data_object->is_bound(); }

	//*************************************************************************
	/**
	* Set Transactions auto commit flag (if supported)
	* @param bool True (default) = auto commit, False = no auto commit
	**/
	//*************************************************************************
	public function auto_commit($auto_commit=true) { return $this->data_object->auto_commit($auto_commit); }

	//*************************************************************************
	/**
	* Start a new Database Transaction
	**/
	//*************************************************************************
	public function start_trans() { return $this->data_object->start_trans(); }

	//*************************************************************************
	/**
	* Commit current Outstanding Statements / Transaction(s)
	**/
	//*************************************************************************
	public function commit() { return $this->data_object->commit(); }

	//*************************************************************************
	/**
	* Rollback current Outstanding Statements / Transaction(s)
	**/
	//*************************************************************************
	public function rollback() { return $this->data_object->rollback(); }

	//*************************************************************************
	/**
	* Prepares an SQL statement to be executed
	* @param string SQL Statement
	**/
	//*************************************************************************
	public function prepare($statement=false)
	{
		// Unset last_id / Clear results
		$this->data_object->clear_last_insert_id();
		$this->data_object->clear_result();

		if (!$this->data_object->is_open()) { return false; }

		if (!$statement) {
			trigger_error('Error: [data_trans]::prepare(): No statement or invalid statement passed.');
			return false;
		}
		else {
			// Arguments
			$args = array();
			foreach (func_get_args() as $arg) { $args[] = $arg; }
			return call_user_func_array(array($this->data_object, 'prepare'), $args);
		}
	}

	//*************************************************************************
	/**
	* Executes a prepared SQL statement given parameters
	* @param array An array of parameters to be passed during binding.
	**/
	//*************************************************************************
	public function execute($params=false)
	{
		// Unset last_id / Clear results
		$this->data_object->clear_last_insert_id();
		$this->data_object->clear_result();

		// Check if Connection Open
		if (!$this->data_object->is_open()) { return false; }

		// Extra Function Arguments
		$args = array();
		foreach (func_get_args() as $arg) { $args[] = $arg; }

		// Execute Query
		$exec_result = call_user_func_array(array($this->data_object, 'execute'), $args);

		// Print Query (if necessary)
		if ($this->print_query) { $this->display_query(); }

		return $exec_result;
	}

	//*************************************************************************
	/**
	* Display the current query being run
	**/
	//*************************************************************************
	protected function display_query()
	{
		// Pull Query and Bind Params
		$query = $this->data_object->get_query();
		if (!$query) {
			return false;
		}

		print 'Current Query: ';

		if (is_array($query)) {
			if (isset($_SERVER)) {
				print "<pre>\n";
				print_r($query);
				print "</pre>\n";
			}
			else { print_r($query); }
		}
		else {
			if (isset($_SERVER)) { print "{$query}<br/>\n"; }
			else { print "{$query}\n"; }
		}
	}

	/**
	* Get Transaction Option
	* @param string Option Key
	* @return The value of the option or false if it does not exist
	**/
	//*************************************************************************
	// Get Transaction Option
	//*************************************************************************
	public function get_opt($opt)
	{
		return $this->data_object->get_opt($opt);
	}

	/**
	* Set Transaction Option
	* @param string Option Key
	* @param string Option Value
	**/
	//*************************************************************************
	// Set Transaction Option
	//*************************************************************************
	public function set_opt($opt, $val=false)
	{
		return $this->data_object->set_opt($opt, $val);
	}

	/**
	* Get Connection Handle
	* @return The database connection handle being used
	**/
	//*************************************************************************
	// Get Connection Handle
	//*************************************************************************
	public function get_conn_handle()
	{
		return $this->data_object->get_conn_handle();
	}

}

