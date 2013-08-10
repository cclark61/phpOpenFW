<?php
//*************************************************************************
//*************************************************************************
/**
* Site Controller Functions
*
* @package		phpOpenFW
* @subpackage	Application-Logic-2-Structure
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 2/18/2013, Last updated: 2/19/2013
**/
//*************************************************************************
//*************************************************************************

//=========================================================================
//=========================================================================
// Execute Page Controller
//=========================================================================
//=========================================================================
function execute_page_controller(&$page, $args=false)
{
	//============================================================
	// Controller Parameters
	//============================================================
	if (is_array($args)) { extract($args); }

	//============================================================
	// Site Configuration Parameters
	//============================================================
	if (!empty($_SESSION['config']) && is_array($_SESSION['config'])) {
		extract($_SESSION['config']);
	}

	//============================================================
	// Current Pages
	// Page Arguments
	//============================================================
	if (!empty($curr_pages)) { extract($curr_pages); }
	if (!empty($page_args)) { extract($page_args); }

	//============================================================
	// Page setup / defaults
	//============================================================
	$page_status = 200;					// Default Status Code
	$content_layout = 'page';			// Set default content layout

	//============================================================
	// Default Settings
	//============================================================
	$throw_404 = false;
	$throw_500 = false;

	//============================================================
	// Extract $_POST and $_GET
	//============================================================
	extract($_POST, EXTR_PREFIX_SAME, 'post_');
	extract($_GET, EXTR_PREFIX_SAME, 'get_');

	//============================================================
	// Action variable
	//============================================================
	$action = (isset($_GET['action'])) ? ($_GET['action']) : ('');
	if (isset($_POST['action'])) { $action = $_POST['action']; }

	//============================================================
	// Include Pre-page Include File
	//============================================================
	if (file_exists(FILE_PATH . '/pre_page.inc.php')) {
		include(FILE_PATH . '/pre_page.inc.php');
	}
	
	//============================================================
	// Controller Exists
	//============================================================
	if (file_exists($controller)) { include($controller); }
	//============================================================
	// Throw 404
	//============================================================
	else { $throw_404 = true; }
	
	//============================================================
	// Display: "Page Not Found"
	//============================================================
	if ($throw_404) {
		$controller = "{$controller_path}/error/controller_404.php";
		if (file_exists($controller)) { include($controller); }
		else {
			if (BUFFER_PAGE) { ob_end_clean(); }
			die('404 controller not found.');
		}
		
		//---------------------------------------------
		// Flag Status Code
		//---------------------------------------------
		$page_status = 404;
	}
	//============================================================
	// Display: "Page Error"
	//============================================================
	else if ($throw_500 || isset($_SESSION['throw_500'])) {
		$controller = "{$controller_path}/error/controller_500.php";
		if (file_exists($controller)) { include($controller); }
		else {
			if (BUFFER_PAGE) { ob_end_clean(); }
			die('500 controller not found.');
		}
		
		//---------------------------------------------
		// Flag Status Code
		//---------------------------------------------
		$page_status = 500;
	}

	//============================================================
	// Include Post-controller Include File
	//============================================================
	if (file_exists(FILE_PATH . '/post_controller.inc.php')) {
		include(FILE_PATH . '/post_controller.inc.php');
	}

	return $page_status;
}

//=========================================================================
//=========================================================================
// Error Handler
//=========================================================================
//=========================================================================
function my_error_handler($errno, $errstr, $errfile, $errline, $errcontext)
{
	if (!empty($_SESSION['min_allowable_error_level'])) {
		$min_error_level = (int)$_SESSION['min_allowable_error_level'];
	}
	else {
		$min_error_level = 2048;
	}

	$tmp = array();
	$tmp['errno'] = $errno;
	$tmp['errstr'] = $errstr;
	$tmp['errfile'] = $errfile;
	$tmp['errline'] = $errline;
	$_SESSION['page_errors'][] = $tmp;

	if ($errno < $min_error_level) {
		$_SESSION['throw_500'] = true;
	}
}

//=========================================================================
//=========================================================================
// Set Plugin Folder
//=========================================================================
//=========================================================================
function set_plugin_folder($folder)
{
	$GLOBALS['pco']->set_plugin_folder($folder);
}

//=========================================================================
//=========================================================================
// Load Plugin
//=========================================================================
//=========================================================================
function load_plugin($plugin)
{
	$GLOBALS['pco']->load_plugin($plugin);
}

?>
