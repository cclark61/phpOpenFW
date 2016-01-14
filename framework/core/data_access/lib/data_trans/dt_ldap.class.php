<?php

/**
* Data Transaction / LDAP Plugin
* A LDAP plugin to the (data_trans) class
*
* @package		phpOpenFW
* @subpackage 	Database_Tools
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @access		private
* @version 		Started: 2-1-2007 updated: 11-20-2012
*/

//***************************************************************
/**
 * dt_ldap Class
 * @package		phpOpenFW
 * @subpackage	Database_Tools
 * @access		private
 */
//***************************************************************
class dt_ldap extends dt_structure
{
    
    /**
	* Opens a connection to the specified data source based on the data source type
	**/
	//*************************************************************************
	// Make a connection to the given source and store the handle
	//*************************************************************************
	public function open()
	{
		if (!$this->port) { $this->port = 389; }
        $this->handle = @ldap_connect($this->server, $this->port);
        if (!$this->handle){
            trigger_error("{$this->disp_dt} Connection Error: [ {$this->server}:{$this->port} ].");
            return false;
        }
        else {
            if (!ldap_set_option($this->handle, LDAP_OPT_PROTOCOL_VERSION, 3)){
                trigger_error("{$this->disp_dt} Failed to set LDAP protocol version to 3.");
                return false;
            }
	  	}

		// Keep track of the number of connections we create
        if (!isset($GLOBALS['ldap_conns'])) { $GLOBALS['ldap_conns'] = 0; }
		$GLOBALS['ldap_conns']++;

		return true;
	}
	
	/**
	* Closes a connection to the specified data source based on the data source type
	**/
	//*************************************************************************
	// Close the connection to the data source
	//*************************************************************************
    public function close()
	{
		if ($this->handle && !$this->data_result) {
			ldap_close($this->handle);
		}
	   $this->conn_open = false;
	}
	
	/**
	* Executes a query based on the data source type
	* @param mixed LDAP: properly formatted and filled array
	* @param string "anon" - Anonymous Bind, "user" - User Bind, "admin" - Admin Bind
	**/
	//*************************************************************************
	// Execute a query and store the record set
	//*************************************************************************
	public function query($query)
	{
		$ret_val = false;

        if (!is_array($query)) {
        	$this->rsrc_id = false;
        }
        else {
			$this->curr_query = $query;

            switch ($this->trans_type) {

	            //*********************************************************
	            // Query
	            //*********************************************************
                case 'qry':
                case 'qry1':

                    //====================================================
                    // LDAP Query Parts
                    //====================================================
                    $search_dn = $query[0] . $this->source;
                    $ldapFilter = (isset($query[1])) ? ($query[1]) : ('*');
                    $selectAttrs = (!isset($query[2]) || !is_array($query[2])) ? (array('*')) : ($query[2]);

                    //====================================================
                    // Query Type
                    //====================================================
                    if ($this->trans_type == 'qry1') {
                        $this->rsrc_id = @ldap_list($this->handle, $search_dn, $ldapFilter, $selectAttrs);
                    }
                    else {
                        $this->rsrc_id = @ldap_search($this->handle, $search_dn, $ldapFilter, $selectAttrs);
                    }

                    //====================================================
                    // Check for Error
                    //====================================================
                    if (ldap_errno($this->handle)) {
                        $this->gen_error(ldap_error($this->handle));
                    }
		
                    //====================================================
                    // Sort
                    //====================================================
                    if (isset($query['ldapSortAttributes'])) {
                        foreach ($query['ldapSortAttributes'] as $eachSortAttribute) {
                            ldap_sort($this->handle, $this->rsrc_id, $eachSortAttribute);
                        }
                    }

                    //====================================================
                    // Create Data Result Object if Necessary
                    //====================================================
			    	if ($this->rsrc_id && gettype($this->rsrc_id) != 'boolean') {
			        	$this->data_result = new data_result($this->rsrc_id, $this->data_src, array('handle' => $this->handle));
			        }

                    break;
							
	            //*********************************************************
	            // Add
	            //*********************************************************
                case 'add':
                    $this->rsrc_id = @ldap_add($this->handle, $query['dn'], $query['values']);
                    $ret_val = $this->rsrc_id;
                    break;

	            //*********************************************************
	            // Modify
	            //*********************************************************
                case 'mod':
                    $this->rsrc_id = @ldap_modify($this->handle, $query['dn'], $query['values']);
                    $ret_val = $this->rsrc_id;
                    break;

	            //*********************************************************
	            // Delete
	            //*********************************************************
                case 'del':							
                    $this->rsrc_id = @ldap_delete($this->handle, $query['dn']);
                    $ret_val = $this->rsrc_id;
                    break;
            }
        }

		//----------------------------------------------
		// Return Data Result Object if it exists
		//----------------------------------------------
		if ($this->data_result) {
        	$this->num_rows = $this->data_result->num_rows();
        	$this->num_fields = $this->data_result->num_fields();
			$ret_val = $this->data_result;
		}

        return $ret_val;
	}

}

