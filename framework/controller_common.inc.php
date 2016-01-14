<?php
//****************************************************************************
//****************************************************************************
// Controller Common Functions
//****************************************************************************
//****************************************************************************
/**
* Main Framework Controller
*
* @package		phpOpenFW
* @subpackage	Controllers
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 12/19/2011, Last updated: 1/13/2016
**/
//****************************************************************************
//****************************************************************************

//************************************************************************************
/**
* Get URL Path Function
*/
//************************************************************************************
function POFW_get_url_path()
{
	//----------------------------------------------------
	// If $_SERVER['REDIRECT_URL'] is set
	//----------------------------------------------------
	if (isset($_SERVER['REDIRECT_URL'])) {
		return $_SERVER['REDIRECT_URL'];
	}
	//----------------------------------------------------
	// If $_SERVER['PATH_INFO'] is set
	//----------------------------------------------------
	else if (isset($_SERVER['PATH_INFO'])) {
		return $_SERVER['PATH_INFO'];
	}
	//----------------------------------------------------
	// If $_SERVER['REQUEST_URI'] is set
	//----------------------------------------------------
	else if (isset($_SERVER['REQUEST_URI'])) {
		$qs_start = strpos($_SERVER['REQUEST_URI'], '?');
		if ($qs_start === false) {
			return $_SERVER['REQUEST_URI'];
		}
		else {
			return substr($_SERVER['REQUEST_URI'], 0, $qs_start);
		}
	}

	return false;
}
	
//************************************************************************************
/**
* Set Version Function
*/
//************************************************************************************
function set_version()
{
	$version = false;
	$ver_file = dirname(__FILE__) . '/../VERSION';
	if (file_exists($ver_file)) {
		$version = file_get_contents($ver_file);
	}
	$_SESSION['PHPOPENFW_VERSION'] = $version;
	define('PHPOPENFW_VERSION', $version);
}

//************************************************************************************
/**
* Check XSL Loaded Function
*/
//************************************************************************************
function check_xsl_loaded()
{
    if (!defined('POFW_XSL_LOADED')) {
        if (extension_loaded('xsl') && extension_loaded('dom')) {
            define('POFW_XSL_LOADED', true);
        }
        else {
            define('POFW_XSL_LOADED', false);
        }
    }

    return POFW_XSL_LOADED;
}

//************************************************************************************
/**
* Kill a Session Function
*/
//************************************************************************************
function session_kill($plugin)
{
	if (isset($_SESSION)) {
		$_SESSION = array();
		
		if (ini_get('session.use_cookies')) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - 42000,
		        $params['path'], $params['domain'],
		        $params['secure'], $params['httponly']
		    );
		}
		
		session_destroy();
		return true;
	}
	return false;
}

//************************************************************************************
/**
* Load Form Element Classes Function
*/
//************************************************************************************
function load_form_elements()
{
	$form_elem_dir = dirname(__FILE__) . '/core/structure/forms';
	if (is_dir($form_elem_dir)) {
		require_once($form_elem_dir . '/simple.inc.php');
		require_once($form_elem_dir . '/complex/cfe.class.php');
		require_once($form_elem_dir . '/complex/ssa.class.php');
		require_once($form_elem_dir . '/complex/sst.class.php');
		require_once($form_elem_dir . '/complex/cga.class.php');
		require_once($form_elem_dir . '/complex/rga.class.php');
		require_once($form_elem_dir . '/complex/rgt.class.php');
		return true;
	}
	return false;
}

//************************************************************************************
/**
* Register Data Source Function
* Register a new data source in the Session
* @param string Data source index name
* @param array Data source parameter array
*/
//************************************************************************************
// reg_data_source() function
// Register a new data source in the Session
//************************************************************************************
function reg_data_source($ds_index, $ds_params)
{
	$known_params = array('type', 'server', 'port', 'source', 'user', 'pass', 'instance', 'conn_str', 'options', 'persistent', 'reuse_connection');
	$optional_params = array('port' => '', 'user' => '', 'pass' => '', 'instance' => '', 'conn_str' => '', 'options' => '', 'persistent' => '', 'reuse_connection' => '');
	settype($ds_index, 'string');
	if (!is_array($ds_params)) {
		trigger_error("Error: reg_data_source(): Index: '{$ds_index}', Second parameter must be an array.");
		return 2;
	}
	else {
		$param_count = count($known_params);
		$new_data_source = array();
		foreach ($known_params as $param_index) {
			if (isset($ds_params[$param_index])) {
				$new_data_source[$param_index] = $ds_params[$param_index];
				$param_count--;
			}
			else if (isset($optional_params[$param_index])) { $param_count--; }
		}

		if ($param_count > 0) {
			trigger_error("Error: reg_data_source(): Index: '{$ds_index}', Incorrect parameter count in parameter array.");
			return 4;
		}
		else {
			$_SESSION[$ds_index] = $new_data_source;
			return 0;
		}
	}
}

//************************************************************************************
/**
* Set Default Data Source Function
* @param string Data Source Index
*/
//************************************************************************************
// default_data_source() function
// Set Default Data Source
//************************************************************************************
function default_data_source($index)
{
	settype($index, 'string');
	if ($index != '') {
		if (isset($_SESSION[$index])) {
			$_SESSION['default_data_source'] = $index;
			return 0;
		}
		else {
			trigger_error("Error: default_data_source(): The data source '{$index}' does not exist.");
			return 2;
		}
	}
	else {
		trigger_error('Error: default_data_source(): Data source index cannot be empty.');
		return 1;
	}
}

//**************************************************************************************
/**
* Load Configuration Function
* @access private
*/
//**************************************************************************************
// Load Configuration Function
//**************************************************************************************
function load_config($config_file=false)
{
	//*************************************************************
	// Initialize the Arrays
	//*************************************************************
	$config_arr = array();
	$data_arr = array();

	//*************************************************************
	// Include the configuration file
	//*************************************************************
    if ($config_file && file_exists($config_file)) {
        require($config_file);
    }
	else {
    	require($_SESSION['file_path'] . '/config.inc.php');
    }

	//*************************************************************
	// Set HTML Path
	//*************************************************************
	if (isset($config_arr['html_path'])) {
		$_SESSION['html_path'] = $config_arr['html_path'];
	}
	else {
		$_SESSION['html_path'] = get_html_path();
	}

	//*************************************************************
	// Generate / initialize session variables from config.inc.php
	//*************************************************************

    //=============================================================
	// *** Configuration Array
    //=============================================================
	$key_arr = array_keys($config_arr);
	foreach ($key_arr as $key) { $_SESSION[$key] = $config_arr[$key]; }

    //=============================================================
	// *** Data Source Array
    //=============================================================
	$key_arr2 = array_keys($data_arr);
	foreach ($key_arr2 as $key2) {
		$reg_code = reg_data_source($key2, $data_arr[$key2]);
		if (!$reg_code) { $_SESSION[$key2]['handle'] = 0; }
	}
	
	//*************************************************************
	// Set Authentication Data Source
	//*************************************************************
	if (!isset($_SESSION['auth_data_source']) || empty($_SESSION['auth_data_source'])) {
		$_SESSION['auth_data_source'] = 'none';
	}
	
	//*************************************************************
	// Set Authentication Data Type
	//*************************************************************
	if ($_SESSION['auth_data_source'] != 'none' && $_SESSION['auth_data_source'] != 'custom') {
		 if (!array_key_exists($_SESSION['auth_data_source'], $data_arr) && $_SESSION['auth_data_source'] != 'none') {
		 	$_SESSION['auth_data_type'] = 'error';
		 }
		 else {
		 	$_SESSION['auth_data_type'] = $data_arr[$_SESSION['auth_data_source']]['type'];
		 }
	}
	else if ($_SESSION['auth_data_source'] == 'custom') {
		$_SESSION['auth_data_type'] = 'custom';
	}
	else {
		$_SESSION['auth_data_type'] = 'none';
	}
}

//************************************************************************************
/**
* Load Database Sources Configuration Function
* @param string Full file path to data source configuration file
* @param bool Force the configuration to be reloaded
*/
//************************************************************************************
function load_db_config($db_config, $force_config=false)
{
	if ((bool)$force_config === true || !empty($_SESSION['db_config_set'])) {
		if (file_exists($db_config)) {
			$data_arr = array();
			require_once($db_config);
			
			if (isset($data_arr) && count($data_arr) > 0) {
				$key_arr2 = array_keys($data_arr);
				foreach ($key_arr2 as $key2){
					$reg_code = $this->reg_data_source($key2, $data_arr[$key2]);
					if (!$reg_code) { $_SESSION[$key2]['handle'] = 0; }
				}
				$_SESSION['db_config_set'] = true;
			}
			else {
				trigger_error('Error: load_db_config(): No data sources defined!');
				$_SESSION['db_config_set'] = false;
			}
		}
		else {
			trigger_error('Error: load_db_config(): Data Source Configuration file does not exist!');
			$_SESSION['db_config_set'] = false;
		}
	}
}

//************************************************************************************
/**
* Get HTML Path Function
*/
//************************************************************************************
// Get HTML Path Function
//************************************************************************************
function get_html_path()
{
	$path = '';
	if (isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['SCRIPT_FILENAME'])) {
		$doc_root = $_SERVER['DOCUMENT_ROOT'];
		$doc_root_parts = explode('/', $doc_root);
		$script_file = $_SERVER['SCRIPT_FILENAME'];
		$script_file_parts = explode('/', $script_file);

		foreach ($script_file_parts as $key => $part) {
			if (!isset($doc_root_parts[$key])) {
				if ($part != 'index.php') { $path .= '/' . $part; }
			}
		}
	}
	else {
		$_SESSION['html_path'] = $path;
		$self = $_SERVER['PHP_SELF'];
		$self_arr = explode('/', $self);
		foreach ($self_arr as $item) {
			if (!empty($item) && $item != 'index.php') { $path .= "/$item"; }
		}
		if ($path == '/') { $path = ''; }
	}
	return $path;
}

//************************************************************************************
/**
* Set External Plugin Folder
* @param string File path to plugin folder
*/
//************************************************************************************
function set_plugin_folder($dir)
{
    //=================================================================
    // Validate Directory
    //=================================================================
    if (!$dir || !is_dir($dir)) {
    	trigger_error('Error: set_plugin_folder(): Invalid directory passed.');
    	return false;        
    }

    //=================================================================
    // Get the MD5 Hash for indexing
    //=================================================================
    $pf_hash = md5($dir);

    //=================================================================
    // Does App Plugin Index Exist?
    //=================================================================
    if (!isset($_SESSION['app_plugin_folder'])) {
        $_SESSION['app_plugin_folder'] = array();
    }
    //=================================================================
    // Check if plugin folder is already set
    //=================================================================
    else if (isset($_SESSION['app_plugin_folder'][$pf_hash])) {
        return true;
    }

    //=================================================================
    // Addd New Plugin Folder
    //=================================================================
	$_SESSION['app_plugin_folder'][] = $dir;
	return true;
}

//************************************************************************************
/**
* Unset External Plugin Folder
* @param string File path to plugin folder
*/
//************************************************************************************
function unset_plugin_folder($dir)
{
    //=================================================================
    // Validate Directory
    //=================================================================
    if (!$dir || !is_dir($dir)) {
    	trigger_error('Error: unset_plugin_folder(): Invalid directory passed.');
    	return false;        
    }

    //=================================================================
    // Get the MD5 Hash for indexing
    //=================================================================
    $pf_hash = md5($dir);

    //=================================================================
    // Does App Plugin Index Exist?
    //=================================================================
    if (!isset($_SESSION['app_plugin_folder'])) {
        return false;
    }
    //=================================================================
    // Check if plugin folder is already set
    //=================================================================
    else if (isset($_SESSION['app_plugin_folder'][$pf_hash])) {
        unset($_SESSION['app_plugin_folder'][$pf_hash]);
        return true;
    }

	return false;
}

//************************************************************************************
/**
* Load Plugin Function
* @param string The Name of the plugin without the ".inc.php"
*/
//************************************************************************************
// Load a Plugin from the "plugins" directory
//************************************************************************************
function load_plugin($plugin)
{
    if (isset($_SESSION['app_plugin_folder'])) {
    	foreach ($_SESSION['app_plugin_folder'] as $pf) {
    		$plugin_file3 = "{$pf}/{$plugin}.inc.php";
    		$plugin_file4 = "{$pf}/{$plugin}.php";
    		$plugin_file5 = "{$pf}/{$plugin}.class.php";
    
    		if (file_exists($plugin_file3)) {
    			require_once($plugin_file3);
    			return true;
    		}
    		else if (file_exists($plugin_file4)) {
    			require_once($plugin_file4);
    			return true;
    		}
    		else if (file_exists($plugin_file5)) {
    			require_once($plugin_file5);
    			return true;
    		}	
    	}
    
    	if (file_exists($plugin)) {
    		require_once($plugin);
    		return true;
    	}
    }

	trigger_error("Error: load_plugin(): Plugin \"{$plugin}\" does not exist!");
	return false;
}

//************************************************************************************
/**
* Load Form Engine
*/
//************************************************************************************
function load_form_engine()
{
    load_form_elements();
	if (defined('POFW_XSL_LOADED') && POFW_XSL_LOADED) {
        require_once(__DIR__ . "/core/structure/forms/form.class.php");
		require_once(__DIR__ . "/core/structure/forms/form_too.class.php");
    }
	else {
		trigger_error('Error: load_form_engine(): Cannot use form engine, XSL and/or DOM are not loaded!.');
		return false;
	}

    return true;
}

//************************************************************************************
/**
* Load Database Engine
*/
//************************************************************************************
function load_db_engine()
{
	require_once(__DIR__ . "/core/data_access/data_trans.class.php");
	require_once(__DIR__ . "/core/data_access/data_query.class.php");
    load_plugin('qdba');
	load_plugin('dio');
}

