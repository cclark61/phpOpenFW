<?php
/**
* User List Class
*
* @package		phpOpenFW
* @subpackage	Application-Logic-1-Security
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @access		private
* @version 		Started: 11-16-2005, Last updated: 3-20-2010
**/

//***************************************************************
/**
 * User List Class
 * @package		phpOpenFW
 * @subpackage	Application-Logic-1-Security
 * @access		private
 */
//***************************************************************
class user_list {

	//******************************************************************
	// Class variables
	//******************************************************************
	/**
	* @var string data source (from main config file) to authenticate against
	**/
	private $data_src;
	
	/**
	* @var string data type (pulled from data source)
	**/
	private $data_type;
	
	/**
	* @var string type of user list to create
	**/
	private $list_type;
	
	/**
	* @var string "key" field to be used in returned user list (associative array)
	**/
	private $list_key;
	
	/**
	* @var string "value" field to be used in returned user list (associative array)
	**/
	private $list_val;
	
	/**
	* @var array user list (associative array) to be returned
	**/
	private $list;

	/**
	* Constructor function
	* @param string data type from which to pull user list (basic (default), basic-detail, detailed, advanced (full ldap dump))
	**/
	//******************************************************************
	// Constructor function
	//******************************************************************
	public function __construct($type='basic')
	{
		$this->data_src = $_SESSION['auth_data_source'];
		$this->data_type = (array_key_exists($this->data_src, $_SESSION)) ? ($_SESSION[$this->data_src]['type']) : ('none');
		$this->list_type = $type;
		switch ($this->data_type) {
			case 'ldap':
				$ldapFilterAttributes = '';
				if ($this->list_type == 'basic-detail') {
					$ldapFilterAttributes = array('cn', 'givenname', 'sn', 'uid', 'mail');
				}
				$ldapSortAttributes = array('uid', 'cn');
				$ldapFilter = 'uid=*';
				$search_dn = $_SESSION['auth_user_table'] . ',';

				$query = array($search_dn, $ldapFilter, $ldapFilterAttributes);
				
				switch ($this->list_type) {
					case 'advanced':
						$this->list_key = 'dn';
						break;
						
					default:
						$this->list_key = $_SESSION['auth_user_field'];
						break;
				}
				$this->list_val = $_SESSION['auth_fname_field'];
				break;
			
			case 'mysql':
				$query = "select $_SESSION[auth_user_field], $_SESSION[auth_fname_field], $_SESSION[auth_lname_field],";
				$query .= " concat($_SESSION[auth_fname_field], ' ', $_SESSION[auth_lname_field]) as name from $_SESSION[auth_user_table]";
				$query .= " order by $_SESSION[auth_lname_field], $_SESSION[auth_lname_field]";
				$this->list_key = $_SESSION['auth_user_field'];
				$this->list_val = 'name';
				break;
		
			case 'pgsql':
				$query = "select $_SESSION[auth_user_field], $_SESSION[auth_fname_field], $_SESSION[auth_lname_field],";
				$query = " $_SESSION[auth_fname_field] || ' ' || $_SESSION[auth_lname_field] as name from $_SESSION[auth_user_table]";
				$query .= " order by $_SESSION[auth_lname_field], $_SESSION[auth_lname_field]";
				$this->list_key = $_SESSION['auth_user_field'];
				$this->list_val = 'name';
				break;
		}

		if (isset($query)) {
			$data1 = new data_trans($this->data_src);
			$data1->data_query($query);
			
			switch ($this->list_type) {
				case 'detailed':
					$this->list = $data1->data_assoc_result();
					break;
					
				case 'basic-detail':
					$this->list = $data1->data_key_assoc($this->list_key);
					break;
				
				default:
					$this->list = $data1->data_key_val($this->list_key, $this->list_val);
					break;
			}
		}
		else {
			$this->list = array();
		}
	}
	
	/**
	* Return the user list in the form of an associative array
	* @return user list in the form of an associative array
	**/
	//******************************************************************
	// Export Function
	//******************************************************************
	public function export()
	{
		array_multisort($this->list);
		return $this->list;	
	}
	
	
}
?>
