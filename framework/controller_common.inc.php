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
* @version 		Started: 12/19/2011, Last updated: 11/20/2012
**/
//****************************************************************************
//****************************************************************************

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
		include_once($form_elem_dir . '/simple.inc.php');
		include_once($form_elem_dir . '/complex/cfe.class.php');
		include_once($form_elem_dir . '/complex/ssa.class.php');
		include_once($form_elem_dir . '/complex/sst.class.php');
		include_once($form_elem_dir . '/complex/cga.class.php');
		include_once($form_elem_dir . '/complex/rga.class.php');
		include_once($form_elem_dir . '/complex/rgt.class.php');
		return true;
	}
	return false;
}

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

?>