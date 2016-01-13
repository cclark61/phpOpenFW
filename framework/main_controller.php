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
* @version 		Started: ??-??-????, Last updated: 1-13-2016
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
// Is XSL / DOM Available?
//============================================================
check_xsl_loaded();

//============================================================
// Set Application Plugin Folders
//============================================================
set_plugin_folder("{$_SESSION['frame_path']}/plugins");
set_plugin_folder("{$_SESSION['file_path']}/plugins");

//============================================================
// Include necessary core components
//============================================================
require_once("{$_SESSION['frame_path']}/core/structure/objects/element.class.php");
load_plugin('xml_transform');

//============================================================
// Set application logic directory
//============================================================
$_SESSION['app_logic_path'] = $_SESSION['frame_path'] . '/app_logic/logic1';
$app_logic_path = $_SESSION['app_logic_path'];

//============================================================
// Include necessary application logic
//============================================================
require_once("{$app_logic_path}/structure/page.class.php");
require_once("{$app_logic_path}/structure/message.class.php");
require_once("{$app_logic_path}/structure/module.class.php");

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

    //--------------------------------------------------------
	// Set Login URL if needed
    //--------------------------------------------------------
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

        //=============================================================
		// Perform Login Operation
		//=============================================================
		if (!isset($_SESSION['userid'])) {
			$login = new login();
			
            //--------------------------------------------------------
			// If login.inc.php exists include it
            //--------------------------------------------------------
			if (file_exists("{$_SESSION['file_path']}/login.inc.php")) { require_once("{$_SESSION['file_path']}/login.inc.php"); }

            //--------------------------------------------------------
			// If previous URL given go there after login
            //--------------------------------------------------------
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
					//============================================================
                    // Show Login Page
					//============================================================
					$page = new message('login');
					break;
					
				case 'error';
					$page = new message('8');
					break;
					
				default:
				    //=============================================================
					// Perform Login Operation
					//=============================================================
					require_once("{$app_logic_path}/security/login.class.php");
					$login = new login();

                    //=============================================================
					// If login.inc.php exists include it
                    //=============================================================
					if (file_exists("{$_SESSION['file_path']}/login.inc.php")) { require_once("{$_SESSION['file_path']}/login.inc.php"); }

                    //=============================================================
					// If previous URL given go there after login
					//=============================================================
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

//============================================================
// Render Page
//============================================================
$page->render();

