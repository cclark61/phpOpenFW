<?php
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
* @version 		Started: ??-??-????, Last updated: 8-27-2014
**/
//****************************************************************************
//****************************************************************************

//============================================================
// Include Controller Common Functions
//============================================================
require_once(dirname(__FILE__) . '/controller_common.inc.php');

//============================================================
// Load Config if not logged in
//============================================================
if (!isset($_SESSION['userid'])) { load_config(); }

//============================================================
// Set phpOpenFW Version
//============================================================
set_version();

//============================================================
// Set application logic directory
//============================================================
$_SESSION['app_logic_path'] = $_SESSION['frame_path'] . '/app_logic/logic1';
$app_logic_path = $_SESSION['app_logic_path'];

//============================================================
// Include necessary core components
//============================================================
require_once("{$_SESSION['frame_path']}/core/structure/objects/element.class.php");

//============================================================
// Include necessary application logic
//============================================================
require_once("{$app_logic_path}/structure/page.class.php");
require_once("{$app_logic_path}/structure/message.class.php");
require_once("{$app_logic_path}/structure/module.class.php");
load_plugin('xml_transform');

//============================================================
// Set module
//============================================================
$mod = (isset($_GET['mod'])) ? ( $_GET['mod'] ) : ( '-1' );
$in_mod = $mod;

//============================================================
// If not logged in
//============================================================
if (!isset($_SESSION['userid'])) {
	$mod = 'login';
	
	// Set Login URL if needed
	if ($_SERVER['REQUEST_URI'] != '/' && $in_mod != 'logout' && !isset($_SESSION['login_url'])) { 
		$_SESSION['login_url'] = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}
}

//============================================================
// Login: Check for a passed user
//============================================================
if ($mod == 'login' && !isset($_POST['user'])) { $mod = '-1'; } 

//============================================================
// Logout: Check that the user is logged in
//============================================================
if ($mod == 'logout' && !isset($_SESSION['userid'])) { $mod = '-1'; } 

//============================================================
// Choose page action
//============================================================
switch ($mod) {

	//************************************************************
	// * Login
	//************************************************************
	case 'login':
		require_once("{$app_logic_path}/security/login.class.php");

		// Perform Login Operation
		if (!isset($_SESSION['userid'])) {
			$login = new login();

			// If login.inc.php exists include it
			if (file_exists("{$_SESSION['file_path']}/login.inc.php")) { require_once("{$_SESSION['file_path']}/login.inc.php"); }

			// If previous URL given go there after login
			if (isset($_SESSION['login_url'])) {
				$url_prefix = (!empty($_SERVER['HTTPS'])) ? ('https://') : ('http://');
				$login_url = $_SESSION['login_url'];
				unset($_SESSION['login_url']);
				header("Location: {$url_prefix}{$login_url}");
				exit;
			}
		}
		$page = new module();
		break;

	//************************************************************
	// * Logout
	//************************************************************
	case 'logout':
		$page = new message('7');
		break;

	//************************************************************
	// * Normal Page
	//************************************************************
	default:

		//************************************************************
		// ** User is NOT Logged in
		//************************************************************
		if (!isset($_SESSION['userid'])) {
			switch ($_SESSION['auth_data_type']) {
				case 'ldap':
				case 'mysql':
				case 'pgsql':
				case 'mysqli':
				case 'oracle':
				case 'mssql':
				case 'sqlsrv':
				case 'sqlite':
				case 'db2':
				case 'custom':
					// Show Login Page
					$page = new message('login');
					break;
					
				case 'error';
					$page = new message('8');
					break;
					
				default:
					// Perform Login Operation
					require_once("{$app_logic_path}/security/login.class.php");
					$login = new login();
					
					// If login.inc.php exists include it
					if (file_exists("{$_SESSION['file_path']}/login.inc.php")) { require_once("{$_SESSION['file_path']}/login.inc.php"); }
		
					// If previous URL given go there after login
					if (isset($_SESSION['login_url'])) {
						$url_prefix = (!empty($_SERVER['HTTPS'])) ? ('https://') : ('http://');
						header("Location: $url_prefix" . $_SESSION['login_url']);
						unset($_SESSION['login_url']);
						die();
					}
					
					$page = new module();
					break;
			}
		}
		//************************************************************
		// ** User is Logged in
		//************************************************************
		else {
			$page = new module();
		}
		break;
}

// Render Page
$page->render();

//**************************************************************************************
//**************************************************************************************

/**
* Load Configuration Function
* @access private
*/
//**************************************************************************************
// Load Configuration Function
//**************************************************************************************
function load_config()
{
	// Initialize the Arrays
	$config_arr = array();
	$data_arr = array();

	// Include the configuration file
	require_once($_SESSION['file_path'] . '/config.inc.php');

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

	// *** Confiuration Array
	$key_arr = array_keys($config_arr);
	foreach ($key_arr as $key) { $_SESSION[$key] = $config_arr[$key]; }

	// *** Data Source Array
	$key_arr2 = array_keys($data_arr);
	foreach ($key_arr2 as $key2) {
		$reg_code = reg_data_source($key2, $data_arr[$key2]);
		if (!$reg_code) { $_SESSION[$key2]['handle'] = 0; }
	}
	
	// Set Authentication Data Source
	if (!isset($_SESSION['auth_data_source']) || empty($_SESSION['auth_data_source'])) {
		$_SESSION['auth_data_source'] = 'none';
	}
	
	// Set Authentication Data Type
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

	//*************************************************************
	// Set Application Plugin Folders
	//*************************************************************
	$_SESSION['app_plugin_folder'] = array();
	set_plugin_folder("{$_SESSION['frame_path']}/plugins");
	set_plugin_folder("{$_SESSION['file_path']}/plugins");
}
//**************************************************************************************
//**************************************************************************************

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

/**
* Load Plugin Function
* @param string The Name of the plugin without the ".inc.php"
*/
//************************************************************************************
// Load a Plugin from the "plugins" directory
//************************************************************************************
function load_plugin($plugin)
{
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

	trigger_error("Error: [Main Controller]::load_plugin(): Plugin \"{$plugin}\" does not exist!");
	return false;
}

//***********************************************************************
/**
* Set External Plugin Folder
* @param string File path to plugin folder
*/
//***********************************************************************
function set_plugin_folder($dir)
{
	foreach ($_SESSION['app_plugin_folder'] as $pf) {
		if ($pf == $dir) { return true; }
	}

	if (is_dir($dir)) {
		$_SESSION['app_plugin_folder'][] = $dir;
		return true;
	}
	else {
		trigger_error('Error: [Main Controller]::set_plugin_folder() - Invalid directory passed.');
		return false;
	}
}

?>