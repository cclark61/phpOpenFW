<?php

/**
* Database Interface Object Plugin
* An abstract class for building Database to Object programmatic bridges
*
* @package		phpOpenFW
* @subpackage	Plugin
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 10-17-2006 Updated: 10-3-2013
* @internal
**/

//***************************************************************
/**
 * Database Interface Object Class
 * @package		phpOpenFW
 * @subpackage	Plugin
 */
//***************************************************************
abstract class Database_Interface_Object
{

	//====================================================================
	// Member Variables
	//====================================================================

	protected $data_source;
	protected $db_type;
	protected $schema;
	protected $table;
	protected $primary_key;
	protected $data;
	protected $table_info;
	protected $quoted_types;
	protected $load_prefix;
	protected $print_trans;
	protected $unset_fields;
	protected $class_name;
	protected $no_save_empty_types;
	protected $save_default_types;
	protected $use_bind_params;
	protected $bind_params;
	protected $bind_param_count;
	protected $charset;

	//=====================================================================
	// Member Functions
	//=====================================================================
	
	//***********************************************************************
	// Load Values
	//***********************************************************************
	public function load($pkey_values)
	{
		//=============================================
		// Load data from database
		//=============================================
		if (!empty($pkey_values)) {
            $ll_where = false;
			$strsql = 'select * from';
			if (isset($this->schema)) { $strsql .= " {$this->schema}.{$this->table}"; }
			else { $strsql .= " $this->table"; }
			
            //-----------------------------------------------------------------
			// Build where clause
            //-----------------------------------------------------------------
			$strsql .= $this->build_where($pkey_values);

            //-----------------------------------------------------------------
            // Print Query if Necessary
            //-----------------------------------------------------------------
			if ($this->print_trans) {
                print $strsql;
	            if ($this->use_bind_params) {
	            	ob_start();
	            	print "<br/><pre>\n";
	            	print_r($this->bind_params);
	            	print "</pre>\n";
	            	$ret_val .= ob_get_clean();
	            }
            }

            //-----------------------------------------------------------------
            // Create a new data transaction and execute query
            //-----------------------------------------------------------------
            $data1 = new data_trans($this->data_source);

            //-----------------------------------------------------------------
			// Use Bind Parameters
            //-----------------------------------------------------------------
			if ($this->use_bind_params) {
				// Prepare Query
				$prep_status = $data1->prepare($strsql);
			
				// Execute Query
				$exec_status = $data1->execute($this->bind_params);

				// Reset Bind Variables
				$this->reset_bind_vars();	            
            }
            //-----------------------------------------------------------------
            // Do NOT Use Bind Parameters
            //-----------------------------------------------------------------
            else {
	            $query_result = $data1->data_query($strsql);
			}

            //-----------------------------------------------------------------
            // Get Results
            //-----------------------------------------------------------------
			$result = $data1->data_assoc_result();

            //-----------------------------------------------------------------
            // Set / Unset Appropriate Fields
            //-----------------------------------------------------------------
			foreach ($this->table_info as $field => $info) {
                if ($info['no_load']) {
                    // No Load
                    unset($result[0][$field]);
                }
                elseif (isset($info['alias'])) {
                    // Alias
                    $result[0][$info['alias']] = $result[0][$field];
                    unset($result[0][$field]);
                }
			}

            //-----------------------------------------------------------------
            // 
            //-----------------------------------------------------------------
			if (isset($result[0])) {
				$this->data = $result[0];
				return 1;
			}
			else {
				$this->data = array();
				return 0;
			}
		}
		//=============================================
		// Load data from defaults
		//=============================================
		else {
            foreach ($this->table_info as $key => $info) {
                if (!$info['no_load']) {
                    if (isset($info['alias'])) { $key = $info['alias']; }
                    $this->data[$key] = $info['load_default'];
                }
            }
            return 2;
		}
	}
	
	//***********************************************************************
	// Export data as an associative array
	//***********************************************************************
	public function export($pre_args=array())
	{	
		// Check for pre_export()
		if (method_exists($this, 'pre_export')) {
			if (!is_array($pre_args)) {
				$pre_args = array($pre_args);				
			}
			call_user_func_array(array($this, 'pre_export'), $pre_args);
		}
		
		// Load Prefix
        if (isset($this->load_prefix)) {
        	$return_data = array();
            foreach ($this->data as $field => $info) {
                $return_data[$this->load_prefix . $field] = $this->data[$field];
            }
            if (count($return_data) <= 0) { unset($return_data); }
        }
        else {
        	if (isset($this->data)) { $return_data = $this->data; }
        }
		
		return (isset($return_data)) ? ($return_data) : (false);
	}
	
	//***********************************************************************
	// Import data from $_POST, $_GET, or a pre-defined array
	//***********************************************************************
	public function import($in_array='', $pre_args=array())
	{
		// Check for pre_import()
		if (method_exists($this, 'pre_import')) {
			if (!is_array($pre_args)) {
				$pre_args = array($pre_args);				
			}
			call_user_func_array(array($this, 'pre_import'), $pre_args);
		}
		
        $this->unset_fields = array();
		foreach ($this->table_info as $field => $info) {
            // Load Prefix
            if (isset($this->load_prefix)) { $var_field = $this->load_prefix . $field; }
            else { $var_field = $field; }
            
            // Unset $val for this pass through the loop
            if (isset($val)) { unset($val); }
            
            // Search Input Array
            if (isset($in_array) && !empty($in_array)) {
            	if (isset($in_array[$var_field])) { $val = $in_array[$var_field]; }
            }
            // Search POST and GET
            else {
            	if (isset($_POST[$var_field])) { $val = $_POST[$var_field]; }
            	elseif (isset($_GET[$var_field])) { $val = $_GET[$var_field]; }
            }
            
            // Set in data array or unset_fields array
            if (isset($val)) { $this->data[$field] = $val; }
            else {
            	// Check for a default save value
            	if (isset($info['save_default'])) { $this->data[$field] = $info['save_default']; }
            	else { $this->unset_fields[$field] = ''; }
            }
		}
		
		return $this->unset_fields;
	}
	
	//***********************************************************************
	// Save data to database
	//***********************************************************************
	public function save($pkey_values='', $pre_args=array(), $post_args=array())
	{
		// Check for pre_save()
		if (method_exists($this, 'pre_save')) {
			if (!is_array($pre_args)) {
				$pre_args = array($pre_args);				
			}
			call_user_func_array(array($this, 'pre_save'), $pre_args);
		}
	
        $qa = array();
        $ret_val = false;
        
        // Set Table
        if (isset($this->schema)) { $qa['table'] = "$this->schema.$this->table"; }
        else { $qa['table'] = $this->table; }
        
        foreach ($this->data as $field => &$value) {
			// Set to Save by default
        	$save = true;
        	
        	// Check if field is not supposed to save
        	if ($this->table_info[$field]['no_save']) {
        		$save = false;
        	}
        	// Else if value is empty (but exists)
        	else if ($value == '') {
        		// If Save Default set
        		if (isset($this->table_info[$field]['save_default'])) {
        			$value = $this->table_info[$field]['save_default'];
        		}
        		// Else if field data type save default set
        		else if (array_key_exists($this->table_info[$field]['data_type'], $this->save_default_types)) {
        			$value = $this->save_default_types[$this->table_info[$field]['data_type']];
        		}
        		// Else if field set to no save on empty
        		else if (isset($this->table_info[$field]['no_save_empty']) && $this->table_info[$field]['no_save_empty']) {
        			$save = false;
        		}
        		// Else if field data type set to no save on empty
        		else if (array_key_exists($this->table_info[$field]['data_type'], $this->no_save_empty_types)) {
        			$save = false;
        		}
        		
        		// If NULL
        		if (is_null($value)) {
        			$value = 'NULL';
        			$this->table_info[$field]['quotes'] = 'disable';
        		}
        	}
        	
            // Check if field is not supposed to save
            if ($save) {
				// Use Bind Parameters
				if ($this->use_bind_params && $this->table_info[$field]['can_bind_param']) {
	
					switch ($this->db_type) {
	
		                case 'mysqli':
		                	$qa['fields'][$field] = '?';
		                	$this->bind_param_count++;
		                	$this->bind_params[] = &$value;
		                	$tmp_type = (isset($GLOBALS['mysql_bind_types'][strtoupper($this->table_info[$field]['data_type'])])) 
		                		? ($GLOBALS['mysql_bind_types'][strtoupper($this->table_info[$field]['data_type'])]) 
		                		: ('s');
		                	$this->bind_params[0] .= $tmp_type;
		                    break;

						case 'sqlsrv':
		                	$this->bind_param_count++;
							$qa['fields'][$field] = '?';
		                	$this->bind_params[] = &$value;
							break;

		                case 'pgsql':
		                	$this->bind_param_count++;
		                	$tmp_param = '$' . $this->bind_param_count;
		                	$qa['fields'][$field] = $tmp_param;
		                	$this->bind_params[] = $value;
	                    	break;
		
						case 'oracle':
		                	$this->bind_param_count++;
							$tmp_param = 'p' . $this->bind_param_count;
		                	$qa['fields'][$field] = ':' . $tmp_param;
		                	$this->bind_params[$tmp_param] = $value;
							break;
		
						case 'mssql':
						case 'mysql':
						case 'sqlite':
							switch ($this->table_info[$field]['quotes']) {
			                    // Force quotes
			                    case 'force':
			                        $qa['fields'][$field] = "'$value'";
			                        break;
			                    
			                    // Disable quotes
			                    case 'disable':
			                        $qa['fields'][$field] = $value;
			                        break;
			                        
			                    // Auto detect if quotes are needed
			                    default:
			                        if (isset($this->quoted_types[$this->db_type][$this->table_info[$field]['data_type']])) {
			                            $qa['fields'][$field] = "'$value'";
			                        }
			                        else {
			                            $qa['fields'][$field] = $value;
			                        }
			                        break;
			                }
							break;
		
						case 'db2':
		                	$qa['fields'][$field] = '?';
		                	$this->bind_params[] = $value;
		                	$this->bind_param_count++;
							break;
	
						default:
		
		            }
					
				}
				// Do NOT use Bind Parameters
				else {
	                switch ($this->table_info[$field]['quotes']) {
	                    // Force quotes
	                    case 'force':
	                        $qa['fields'][$field] = "'$value'";
	                        break;
	                    
	                    // Disable quotes
	                    case 'disable':
	                        $qa['fields'][$field] = $value;
	                        break;
	                        
	                    // Auto detect if quotes are needed
	                    default:
	                        if (isset($this->quoted_types[$this->db_type][$this->table_info[$field]['data_type']])) {
	                            $qa['fields'][$field] = "'$value'";
	                        }
	                        else {
	                            $qa['fields'][$field] = $value;
	                        }
	                        break;
	                }
				}
            }
        }
        
        // Set Query Type (insert or update)
        if (!empty($pkey_values)) {
            $qa['type'] = 'update';
            $qa['filter_phrase'] = $this->build_where($pkey_values);
        }
        else {
            $qa['type'] = 'insert';
        }
        
        $query = new data_query($qa);
        $strsql = $query->render();

        if ($this->print_trans) {
            $ret_val = $strsql;
            if ($this->use_bind_params) {
            	ob_start();
            	print "<br/><pre>\n";
            	print_r($this->bind_params);
            	print "</pre>\n";
            	$ret_val .= ob_get_clean();
            }
        }
        else {
            // Create a new data transaction and execute query
            $data1 = new data_trans($this->data_source);
            
            // set Character set?
            if (!empty($this->charset))
            {
                $data1->set_opt('charset', $this->charset);
            }

			// Use Bind Parameters
			if ($this->use_bind_params) {
				// Prepare Query
				$prep_status = $data1->prepare($strsql);

				// Execute Query
				$exec_status = $data1->execute($this->bind_params);
				$ret_val = $exec_status;

				// Reset Bind Variables
				$this->reset_bind_vars();	            
            }
            // Do NOT Use Bind Parameters
            else {
	            $query_result = $data1->data_query($strsql);
	            $ret_val = $query_result;
			}

			// Lsat Insert ID if Insert Statement performed and a valid ID is returned
            if ($qa['type'] == 'insert') {
            	$lii = $data1->last_insert_id();
            	if ($lii !== false) { $ret_val = $lii; }
            }
        }

        // Check for post_save()
		if (method_exists($this, 'post_save')) {
			if (!is_array($post_args)) {
				$post_args = array($post_args);				
			}
			call_user_func_array(array($this, 'post_save'), $post_args);
		}

		return $ret_val;
	}
	
	//***********************************************************************
	// Delete record from database
	//***********************************************************************
	public function delete($pkey_values='', $pre_args=array(), $post_args=array())
	{
		// Check for pre_delete()
		if (method_exists($this, 'pre_delete')) {
			if (!is_array($pre_args)) {
				$pre_args = array($pre_args);				
			}
			call_user_func_array(array($this, 'pre_delete'), $pre_args);
		}
		
        if (!empty($pkey_values)) {
            $qa = array();
            $qa['type'] = 'delete';

            // Set Table
            if (isset($this->schema)) { $qa['table'] = "$this->schema.$this->table"; }
            else { $qa['table'] = $this->table; }
            $qa['filter_phrase'] = $this->build_where($pkey_values);
        
            $query = new data_query($qa);
            $strsql = $query->render();

            if ($this->print_trans) {
                print $strsql;
	            if ($this->use_bind_params) {
	            	ob_start();
	            	print "<br/><pre>\n";
	            	print_r($this->bind_params);
	            	print "</pre>\n";
	            	$ret_val .= ob_get_clean();
	            }
            }
            else {
	            // Create a new data transaction and execute query
	            $data1 = new data_trans($this->data_source);

				// Use Bind Parameters
				if ($this->use_bind_params) {
					// Prepare Query
					$prep_status = $data1->prepare($strsql);
				
					// Execute Query
					$exec_status = $data1->execute($this->bind_params);

					// Reset Bind Variables
					$this->reset_bind_vars();	            
	            }
	            // Do NOT Use Bind Parameters
	            else {
		            $query_result = $data1->data_query($strsql);
				}
            }
        }
        else {
            trigger_error("Error: [$this->class_name]::delete(): No primary key(s) given.", E_USER_ERROR);
        }
        
        // Check for post_delete()
		if (method_exists($this, 'post_delete')) {
			if (!is_array($post_args)) {
				$post_args = array($post_args);				
			}
			call_user_func_array(array($this, 'post_delete'), $post_args);
		}
	}
	
	//***********************************************************************
	// Set Data Source for this object
	//***********************************************************************
	protected function set_data_source($data_source, $table)
	{
		// Set Data to Empty Array
		$this->data = array();
		
		// Set Class Name
		$this->class_name = get_class($this);
		
        // Set Transaction default to run
        $this->print_trans = false;
	
        // Set Quoted Types
        $this->quoted_types = array();
        $this->quoted_types['mysqli'] = array(
        	'char' => '',
        	'date' => '',
        	'text' => '',
        	'tinytext' => '',
        	'mediumtext' => '',
        	'longtext' => '',
        	'varchar' => '',
        	'enum' => '',
        	'timestamp' => '',
        	'datetime' => '',
        	'time' => '',
        	'year' => ''
        );
		$this->quoted_types['mysql'] = $this->quoted_types['mysqli'];
        $this->quoted_types['pgsql'] = array(
        	'char' => '',
        	'date' => '',
        	'text' => '',
        	'varchar' => '',
        	'time' => '',
        	'timestamp' => '',
        	'xml' => ''
        );
        $this->quoted_types['oracle'] = array(
        	'CHAR' => '',
        	'NCHAR' => '',
        	'VARCHAR' => '',
        	'VARCHAR2' => '',
        	'VARCHAR2' => '',
        	'DATE' => '',
        	'TIMESTAMP' => '',
        	'CLOB' => '',
        	'NCLOB' => ''
        );
		$this->quoted_types['sqlsrv'] = array(
        	'char' => '',
        	'varchar' => '',
        	'text' => '',
        	'nchar' => '',
        	'nvarchar' => '',
        	'ntext' => '',
        	'date' => '',
        	'datetimeoffset' => '',
        	'datetime' => '',
        	'datetime2' => '',
        	'smalldatetime' => '',
        	'time' => '',
        	'xml' => ''
		);
		$this->quoted_types['mssql'] = $this->quoted_types['sqlsrv'];
		$this->quoted_types['sqlite'] = array(
			'TEXT' => ''
		);
		$this->quoted_types['db2'] = array(
        	'CHARACTER' => '',
        	'VARCHAR' => '',
        	'DATE' => '',
        	'TIME' => '',
        	'TIMESTAMP' => ''
		);

        // Initialize No Save Empty Data Types and Save Default Data Types Arrays
        $this->no_save_empty_types = array();
        $this->save_default_types = array();

		// Setup Bind Parameters
		$this->reset_bind_vars();

        // Set Data Source
		$this->data_source = $data_source;
		settype($data_source, 'string');
		if (!isset($_SESSION[$this->data_source])) {
            trigger_error('Data Source does not exist.', E_USER_ERROR);
        }
		else {
            // Set Database
            $this->database = $_SESSION[$this->data_source]['source'];

            // Set Database Type
            $this->db_type = (isset($_SESSION[$this->data_source]['type'])) ? ($_SESSION[$this->data_source]['type']) : (false);

            // Set Table and schema
            $this->table = $table;
            settype($table, 'string');
            $table_parts = explode('.', $this->table);
            if (is_array($table_parts)) {
                $this->table = $table_parts[count($table_parts) - 1];
                if (isset($table_parts[count($table_parts) - 2])) {
                    $this->schema = $table_parts[count($table_parts) - 2];
                }
            }
        
            // Pull Table Info
            $data1 = new data_trans($this->data_source);
        
            switch ($this->db_type) {
                case 'mysql':
                case 'mysqli':
                    $strsql = "SHOW COLUMNS FROM {$this->table}";
                    $data1->data_query($strsql);
                    $meta_data = $data1->data_assoc_result();
                    foreach ($meta_data as $field) {
                        $this->table_info[$field['Field']] = array();
                        $fld_type = explode('(', $field['Type']);
                        if (count($fld_type) > 1) {
                            $this->table_info[$field['Field']]['data_type'] = $fld_type[0];
                            if ($fld_type[0] != 'enum') {
                                $this->table_info[$field['Field']]['length'] = substr($fld_type[1], 0, strlen($fld_type[1]) - 1);
                            }
                        }
                        else {
                            $this->table_info[$field['Field']]['data_type'] = $field['Type'];
                            $this->table_info[$field['Field']]['length'] = NULL;
                        }
                        $this->table_info[$field['Field']]['nullable'] = (strtoupper($field['Null']) == 'YES') ? (1) : (0);
                        $this->table_info[$field['Field']]['load_default'] = $field['Default'];
                        $this->table_info[$field['Field']]['no_save'] = false;
                        $this->table_info[$field['Field']]['no_load'] = false;
                        $this->table_info[$field['Field']]['quotes'] = 'auto';
                        $this->table_info[$field['Field']]['can_bind_param'] = true;
                    }
                    break;
                
                case 'pgsql':
                    $strsql = 'SELECT * FROM information_schema.columns';
                    $strsql .= " WHERE table_catalog = '{$this->database}'";
                    if (!empty($this->schema)) { $strsql .= " and table_schema = '{$this->schema}'"; };
                    $strsql .= " and table_name = '{$this->table}' order by ordinal_position";
                    $data1->data_query($strsql);
                    $meta_data = $data1->data_assoc_result();
                    foreach ($meta_data as $field) {
                        $this->table_info[$field['column_name']] = array();
                        $this->table_info[$field['column_name']]['data_type'] = $field['udt_name'];
                        $this->table_info[$field['column_name']]['length'] = $field['character_maximum_length'];
                        $this->table_info[$field['column_name']]['nullable'] = (strtoupper($field['is_nullable']) == 'YES') ? (1) : (0);
                        $this->table_info[$field['column_name']]['load_default'] = $field['column_default'];
                        $this->table_info[$field['column_name']]['no_save'] = false;
                        $this->table_info[$field['column_name']]['no_load'] = false;
                        $this->table_info[$field['column_name']]['quotes'] = 'auto';
                        $this->table_info[$field['column_name']]['can_bind_param'] = true;
                    }
                    break;

				case 'oracle':
					$tmp_tbl = strtoupper($this->table);
					$strsql = "select * from ALL_TAB_COLUMNS where table_name = '{$tmp_tbl}'";
					$data1->data_query($strsql);
                    $meta_data = $data1->data_assoc_result();
                    foreach ($meta_data as $field) {
                        $this->table_info[$field['COLUMN_NAME']] = array();
                        $this->table_info[$field['COLUMN_NAME']]['data_type'] = $field['DATA_TYPE'];
                        $this->table_info[$field['COLUMN_NAME']]['length'] = $field['DATA_LENGTH'];
                        $this->table_info[$field['COLUMN_NAME']]['nullable'] = (strtoupper($field['NULLABLE']) == 'YES') ? (1) : (0);
                        $this->table_info[$field['COLUMN_NAME']]['load_default'] = $field['DATA_DEFAULT'];
                        $this->table_info[$field['COLUMN_NAME']]['no_save'] = false;
                        $this->table_info[$field['COLUMN_NAME']]['no_load'] = false;
                        $this->table_info[$field['COLUMN_NAME']]['quotes'] = 'auto';
                        $this->table_info[$field['COLUMN_NAME']]['can_bind_param'] = true;
                    }
					break;

				case 'sqlsrv':
				case 'mssql':
					$strsql = "select * from information_schema.columns where table_name = '{$this->table}'";
					if (!empty($this->schema)) { $strsql .= " and table_schema = '{$this->schema}'"; };
					$data1->data_query($strsql);
                    $meta_data = $data1->data_assoc_result();
                    foreach ($meta_data as $field) {
                        $this->table_info[$field['COLUMN_NAME']] = array();
                        $this->table_info[$field['COLUMN_NAME']]['data_type'] = $field['DATA_TYPE'];
                        $this->table_info[$field['COLUMN_NAME']]['length'] = $field['CHARACTER_MAXIMUM_LENGTH'];
                        $this->table_info[$field['COLUMN_NAME']]['nullable'] = (strtoupper($field['IS_NULLABLE']) == 'YES') ? (1) : (0);
                        $this->table_info[$field['COLUMN_NAME']]['load_default'] = $field['COLUMN_DEFAULT'];
                        $this->table_info[$field['COLUMN_NAME']]['no_save'] = false;
                        $this->table_info[$field['COLUMN_NAME']]['no_load'] = false;
                        $this->table_info[$field['COLUMN_NAME']]['quotes'] = 'auto';
                        $this->table_info[$field['COLUMN_NAME']]['can_bind_param'] = true;
                    }
					break;

				case 'sqlite':
					break;

				case 'db2':
					if (!strstr($this->table, '/')) {
						trigger_error('Table and schema must be specified in the format of [SCHEMA]/[TABLE]');
					}
					else {
						list($schema, $table) = explode('/', $this->table);
						$strsql = "
							SELECT 
								* 
							FROM 
								QSYS2/SYSCOLUMNS 
							WHERE 
								TABLE_NAME = '{$table}' 
								and TABLE_SCHEMA = '{$schema}'
						";
						$data1->data_query($strsql);
	                    $meta_data = rs_trim($data1->data_assoc_result(), true, true);
	                    foreach ($meta_data as $field) {
	                        $this->table_info[$field['COLUMN_NAME']] = array();
	                        $this->table_info[$field['COLUMN_NAME']]['data_type'] = $field['DATA_TYPE'];
	                        $this->table_info[$field['COLUMN_NAME']]['length'] = $field['LENGTH'];
	                        $this->table_info[$field['COLUMN_NAME']]['nullable'] = (strtoupper($field['IS_NULLABLE']) == 'Y') ? (1) : (0);
	                        $this->table_info[$field['COLUMN_NAME']]['load_default'] = (strtoupper($field['HAS_DEFAULT']) == 'Y') ? ($field['COLUMN_DEFAULT']) : ('');
							$load_def = &$this->table_info[$field['COLUMN_NAME']]['load_default'];
							if ($load_def[0] == "'") { $load_def = substr($load_def, 1); }
							if ($load_def[strlen($load_def) - 1] == "'") { $load_def = substr($load_def, 0, strlen($load_def) - 1); }
							$load_def = trim($load_def);
	                        $this->table_info[$field['COLUMN_NAME']]['no_save'] = false;
	                        $this->table_info[$field['COLUMN_NAME']]['no_load'] = false;
	                        $this->table_info[$field['COLUMN_NAME']]['quotes'] = 'auto';
	                        $this->table_info[$field['COLUMN_NAME']]['can_bind_param'] = true;
						}
					}
					break;

            }
        }
        
        return true;
	}

    //***********************************************************************
	// Set the primary key for this object
	//***********************************************************************
	public function set_pkey($pkey, $save=false)
	{
		if (gettype($pkey) == 'array') {
            $this->primary_key = $pkey;
		    foreach ($this->primary_key as $field) {
                $this->no_load($field);
                if (!$save) {
                    $this->no_save($field);
                }
		    }  
        }
		else {
            $this->primary_key = $pkey;
            settype($this->primary_key, 'string');
            $this->no_load($this->primary_key);
            if (!$save) {
                $this->no_save($this->primary_key);
            }
		}
	}
	
	//***********************************************************************
	// Set field load default (deprecated)
	//***********************************************************************
	public function set_field_default($field=null, $value=null)
	{
		if ($field === null || $value === null) {
			trigger_error('set_field_default(): Invalid parameter count!', E_USER_ERROR);
		}
		else {
			$this->set_load_default($field, $value);
		}
	}
	
	//***********************************************************************
	// Set field load default
	//***********************************************************************
	public function set_load_default($field=null, $value=null)
	{
		if ($field === null || $value === null) {
			trigger_error('set_load_default(): Invalid parameter count!', E_USER_ERROR);
		}
		else {
			if (isset($this->table_info[$field])) { $this->table_info[$field]['load_default'] = $value; }
			else { trigger_error("set_load_default(): Field '$field' does not exist.", E_USER_ERROR); }
		}
	}
	
	//***********************************************************************
	// Set field save default value
	//***********************************************************************
	public function set_save_default($field=null, $value=null)
	{
		if ($field === null || $value === null) {
			trigger_error('set_save_default(): Invalid parameter count!', E_USER_ERROR);
		}
		else {
			if (isset($this->table_info[$field])) { $this->table_info[$field]['save_default'] = $value; }
			else { trigger_error("set_save_default(): Field '$field' does not exist.", E_USER_ERROR); }
		}
	}
	
	//***********************************************************************
	// Set default save value for data type(s)
	//***********************************************************************
	public function set_save_default_types($types)
	{
		if (isset($types)) {
			if (is_array($types)) {
				foreach($types as $type => $value) {
					$this->save_default_types[$type] = $value;
				}
			}
			else {
				$err_msg = 'Data types and values must be passed as an associative array with each element in the following form: [data type] => [default value].';
				trigger_error("set_save_default_types(): $err_msg.", E_USER_ERROR);
			}
		}
		else { trigger_error('set_save_default_types(): No data type(s) passed.', E_USER_ERROR); }
	}
	
	//***********************************************************************
	// Set field data
	//***********************************************************************
	public function set_field_data($field, $value, $use_quotes='auto')
	{		
		if (isset($this->table_info[$field])) { $this->data[$field] = $value; }
		else { trigger_error("set_field_data(): Field '$field' does not exist.", E_USER_ERROR); }
	}

	//***********************************************************************
	// Set field alias
	//***********************************************************************
	public function set_field_alias($field, $alias)
	{
		if (isset($this->table_info[$field])) { $this->table_info[$field]['alias'] = $alias; }
		else { trigger_error("set_field_alias(): Field '$field' does not exist.", E_USER_ERROR); }
	}
	
	//***********************************************************************
	// Set field quotes (Force or Disable)
	//***********************************************************************
	public function set_field_quotes($field, $mode)
	{
		if (isset($this->table_info[$field])) {
            switch (strtoupper($mode)) {
                case 'FORCE':
                    $this->table_info[$field]['quotes'] = 'force';
                    break;
                    
                case 'DISABLE':
                    $this->table_info[$field]['quotes'] = 'disable';
                    break;
            }
        }
		else { trigger_error("set_field_quotes(): Field '$field' does not exist.", E_USER_ERROR); }
	}
	
	//***********************************************************************
	// Set load prefix
	//***********************************************************************
	public function set_load_prefix($prefix)
	{
		$this->load_prefix = $prefix;
		settype($this->load_prefix, 'string');
	}

	//***********************************************************************
	// Enable/Disable a field from using Bind Parameters
	//***********************************************************************
	public function set_use_bind_param($field, $flag)
	{
		$flag = (bool)$flag;
		if (isset($this->table_info[$field])) { $this->table_info[$field]['can_bind_param'] = $flag; }
		else { trigger_error("set_use_bind_param(): Field '$field' does not exist.", E_USER_ERROR); }
	}

	//*************************************************************************
	/**
	* Set Charset
	* @param string $str Example: utf8
	**/
	//*************************************************************************
	public function set_charset($charset)
	{
	    $this->charset = $charset;
	} 

	//***********************************************************************
	// Exclude a field from database transactions
	//***********************************************************************
	public function no_save($field)
	{
		if (isset($this->table_info[$field])) { $this->table_info[$field]['no_save'] = true; }
		else { trigger_error("no_save(): Field '$field' does not exist.", E_USER_ERROR); }
	}

	//***********************************************************************
	// Exclude a field from database transactions when empty
	//***********************************************************************
	public function no_save_empty($field)
	{
		if (isset($this->table_info[$field])) { $this->table_info[$field]['no_save_empty'] = true; }
		else { trigger_error("no_save_empty(): Field '$field' does not exist.", E_USER_ERROR); }
	}

	//***********************************************************************
	// Exclude a data types from database transactions when empty
	//***********************************************************************
	public function no_save_empty_types($types)
	{
		if (isset($types)) {
			if (is_array($types)) {
				foreach($types as $type) {
					$this->no_save_empty_types[$type] = 1;
				}
			}
			else {
				$this->no_save_empty_types[$types] = 1;
			}
		}
		else { trigger_error('no_save_empty_types(): No data type(s) passed.', E_USER_ERROR); }
	}

	//***********************************************************************
	// Exclude a field from loading
	//***********************************************************************
	public function no_load($field)
	{
		if (isset($this->table_info[$field])) { $this->table_info[$field]['no_load'] = true; }
		else { trigger_error("no_load(): Field '$field' does not exist.", E_USER_ERROR); }
	}

	//***********************************************************************
	// Set transactions to print only
	//***********************************************************************
	public function print_only()
	{
	   $this->print_trans = true;
	}

	//***********************************************************************
	// Build a where clause from primary keys
	//***********************************************************************
	private function build_where(&$pkey_values)
	{
        $strsql = '';
        $ll_where = false;

		// If string passed transform into an array
        if (!is_array($pkey_values)) { $pkey_values = array($this->primary_key => $pkey_values); }
			
		foreach ($pkey_values as $key => &$value) {
            if (!$ll_where) {
        		$strsql .= ' where';
    			$ll_where = true;
			}
			else { $strsql .= ' and'; }

			// Bind Parameters
			if ($this->use_bind_params) {

				switch ($this->db_type) {

	                case 'mysqli':
	                	$strsql .= " $key = ?";
	                	$this->bind_param_count++;
	                	$this->bind_params[] = &$value;
	                	$tmp_type = (isset($GLOBALS['mysql_bind_types'][strtoupper($this->table_info[$key]['data_type'])])) 
	                		? ($GLOBALS['mysql_bind_types'][strtoupper($this->table_info[$key]['data_type'])]) 
	                		: ('s');
	                	$this->bind_params[0] .= $tmp_type;
	                    break;
	                
	                case 'pgsql':
	                	$this->bind_param_count++;
	                	$tmp_param = '$' . $this->bind_param_count;
	                	$strsql .= " $key = {$tmp_param}";
	                	$this->bind_params[] = $value;
	                    break;
	
					case 'oracle':
	                	$this->bind_param_count++;
						$tmp_param = ':p' . $this->bind_param_count;
	                	$strsql .= " $key = {$tmp_param}";
	                	$this->bind_params[$tmp_param] = $value;
						break;

					case 'mssql':
					case 'mysql':
					case 'sqlite':
						if (isset($this->quoted_types[$this->db_type][$this->table_info[$key]['data_type']])) {
							$strsql .= " $key = '$value'";
						}
						else {
							$strsql .= " $key = $value";
						}
						break;
	
					case 'db2':
					case 'sqlsrv':
	                	$strsql .= " $key = ?";
	                	$this->bind_params[] = $value;
	                	$this->bind_param_count++;
						break;

					default:
	
	            }
				
			}
			// No Bind Parameters
			else if (isset($this->quoted_types[$this->db_type][$this->table_info[$key]['data_type']])) {
				$strsql .= " $key = '$value'";
			}
			else {
				$strsql .= " $key = $value";
			}
        }
        
        return $strsql;
	}

	//***********************************************************************
	// Set / Reset Bind Parameter Variables
	//***********************************************************************
	private function reset_bind_vars()
	{
        $this->use_bind_params = false;
        $this->bind_params = array();
        $this->bind_param_count = 0;
	}

	//***********************************************************************
	// Set Use of Bind Parameters
	//***********************************************************************
	public function use_bind_params($flag=true)
	{
		$this->use_bind_params = (bool)$flag;
		if ($this->db_type == 'mysqli') { $this->bind_params[0] = ''; }
	}

	//***********************************************************************
	// Get Structural Table Information
	//***********************************************************************
	public function get_table_info()
	{
		return $this->table_info;
	}

	//***********************************************************************
	// Get unset fields
	//***********************************************************************
	public function get_unset_fields()
	{
	   return (isset($this->unset_fields)) ? ($this->unset_fields) : (false);
	}
	
	//***********************************************************************
	// Get field data
	//***********************************************************************
	public function get_field_data($field)
	{		
		if (isset($this->table_info[$field])) {
			if (isset($this->data[$field])) { return $this->data[$field]; }
			else { return false; }
		}
		else {
			trigger_error("get_field_data(): Field '$field' does not exist.", E_USER_ERROR);
			return false;
		}
	}

	//***********************************************************************
	// Dump Information
	//***********************************************************************
	public function dump($type=false)
	{
	   print "<pre>\n";
	   switch ($type) {
	       case 'data':
	           print_r($this->data);
	           break;
	           
	       default:
	           print_r($this->table_info);
	           break;
	   }
	   print "</pre>\n";
	}
	
}

//***********************************************************************
//***********************************************************************
// GLOBAL Database Variables
//***********************************************************************
//***********************************************************************

// All but the 's' types, use 's' as the default
$GLOBALS['mysql_bind_types'] = array(

	// Integer
	'TINYINT' => 'i',
	'SMALLINT' => 'i',
	'MEDIUMINT' => 'i',
	'INT' => 'i',
	'BIGINT' => 'i',

	'BIT' => 'i',
	'BOOL' => 'i',
	'SERIAL' => 'i',

	// Double
	'DECIMAL' => 'd',
	'FLOAT' => 'd',
	'DOUBLE' => 'd',
	'REAL' => 'd',

	// Blob
	'TINYBLOB' => 'b',
	'MEDIUMBLOB' => 'b',
	'BLOB' => 'b',
	'LONGBLOB' => 'b'
);

?>