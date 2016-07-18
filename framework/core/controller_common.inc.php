<?php
//****************************************************************************
//****************************************************************************
/**
* Common Controller Functions
*
* @package		phpOpenFW
* @subpackage	Core
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 12/19/2011, Last updated: 7/18/2016
**/
//****************************************************************************
//****************************************************************************


//************************************************************************************
/**
* Load Form Element Classes Function
*/
//************************************************************************************
function load_form_elements()
{
	$form_elem_dir = __DIR__ . '/../core/structure/forms';
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
	$known_params = array(
		'type', 
		'server', 
		'port', 
		'source', 
		'user', 
		'pass', 
		'instance', 
		'conn_str', 
		'options', 
		'persistent', 
		'reuse_connection', 
		'charset'
	);
	$optional_params = array(
		'port',
		'user'
		'pass',
		'instance',
		'conn_str',
		'options',
		'persistent',
		'reuse_connection',
		'charset'
	);
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
			else if (in_array($param_index, $optional_params)) {
				$param_count--;
			}
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
	$_SESSION['app_plugin_folder'][$pf_hash] = $dir;
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
    //=================================================================
    // Are there plugin folders defined?
    //=================================================================
    if (empty($_SESSION['app_plugin_folder'])) {
    	//trigger_error("Error: load_plugin(): No plugin folders are set!");
    	return false;
    }

    //=================================================================
    // Create a plugin hash
    // Adjust plugin for Namespaces
    //=================================================================
    $plugin_hash = md5($plugin);
    $plugin = str_replace('\\', '/', $plugin);

    //=================================================================
    // Is the location of the plugin cached?
    //=================================================================
    if (isset($_SESSION['app_plugin_folder_cache'][$plugin_hash])) {
        require_once($_SESSION['app_plugin_folder_cache'][$plugin_hash]);
        return true;
    }

    //=================================================================
    // Attempt to locate and load the plugin
    //=================================================================
	foreach ($_SESSION['app_plugin_folder'] as $pf) {
    	$plugin_opts = array(
		    "{$pf}/{$plugin}.inc.php",
            "{$pf}/{$plugin}.php",
            "{$pf}/{$plugin}.class.php"
        );
        foreach ($plugin_opts as $tmp_plugin) {
    		if (file_exists($tmp_plugin)) {
        		$_SESSION['app_plugin_folder_cache'][$plugin_hash] = $tmp_plugin;
    			require_once($tmp_plugin);
    			return true;
    		}
	    }
    }

    //=================================================================
    // Does the plugin exist as a full qualified file path?
    //=================================================================
	if (file_exists($plugin)) {
    	$_SESSION['app_plugin_folder_cache'][$plugin_hash] = $plugin;
		require_once($plugin);
		return true;
	}

    //=================================================================
    // Plugin Not Found
    //=================================================================
	//trigger_error("Error: load_plugin(): Plugin \"{$plugin}\" does not exist!");
	return false;
}


