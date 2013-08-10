<?php
//**************************************************************************
//**************************************************************************
/**
* Main Site Controller
*
* @package		phpOpenFW
* @subpackage	Controllers
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @link			http://www.emonlade.net/phpopenfw/
* @version 		Started: 2009, Last updated: 2/19/2013
**/
//**************************************************************************
//**************************************************************************

//============================================================
// Capture page start time
//============================================================
$page_start_time = microtime();

//============================================================
// Check if user is logged in
//============================================================
$logged_in = (isset($_SESSION['userid'])) ? (true) : (false);

//============================================================
// POST Authentication Redirect
//============================================================
if ($logged_in && !empty($_SESSION['post_auth_redirect_url'])) {
	$tmp_url = $_SESSION['post_auth_redirect_url'];
	unset($_SESSION['post_auth_redirect_url']);
	header("Location: {$tmp_url}");
	exit;
}

//============================================================
// Regular Redirect
//============================================================
if (!empty($_SESSION['redirect_url'])) {
	$tmp_url2 = $_SESSION['redirect_url'];
	unset($_SESSION['redirect_url']);
	header("Location: {$tmp_url2}");
	exit;
}

//============================================================
// Include Application Logic
// Start Page Data
// Create new Page Object
//============================================================
include_once(__DIR__ . '/app_logic/logic2/page.class.php');
include_once(__DIR__ . '/app_logic/logic2/functions.inc.php');
$page = new page();
$controller_args = array();

//============================================================
// Server Name / HTTP Host / SSL ?
//============================================================
$server_name = $_SERVER['SERVER_NAME'];
$http_host = (!empty($_SERVER['HTTP_HOST'])) ? ($_SERVER['HTTP_HOST']) : ('');
$https = (!empty($_SERVER['HTTPS'])) ? (1) : (0);

//============================================================
// Settings
//============================================================
$base_path = $file_path;
$templates = $file_path . '/templates';
$nav_dir = $file_path . '/navs';
$controller_path = "{$file_path}/controllers";
$http_prefix = ($https) ? ('https://') : ('http://');
$site_home_url = ($http_host) ? ($http_host) : ($server_name);
$site_home_url = $http_prefix . $site_home_url;
$catch_errors = (!empty($_SESSION['catch_errors'])) ? (1) : (0);
if (!isset($buffer_page)) { $buffer_page = false; }
if (!isset($mode)) { $mode = false; }

//============================================================
// Defined Constants
//============================================================
define('SERVER_NAME', $server_name);
define('HTTP_HOST', $http_host);
define('HTTPS', $https);
define('HTTP_PREFIX', $http_prefix);
define('SITE_MODE', $mode);
define('FILE_PATH', $file_path);
define('SITE_HOME_URL', $site_home_url);
define('BUFFER_PAGE', $buffer_page);
define('CATCH_ERRORS', $catch_errors);
define('LOGGED_IN', $logged_in);
define('CONTROLLER_PATH', $controller_path);

//============================================================
// Load phpLiteFW Controller
//============================================================
require_once(__DIR__ . '/phplitefw.inc.php');
$pco = new phplitefw_controller();
if (is_dir(FILE_PATH . '/plugins')) {
	$pco->set_plugin_folder(FILE_PATH . '/plugins');
}

//============================================================
// Load Database / Config
//============================================================
$pco->load_db_engine();
if (isset($data_arr) && is_array($data_arr)) {
	foreach ($data_arr as $key => $data_params) {
		$pco->reg_data_source($key, $data_params);
	}
}

//************************************************************************
//************************************************************************
// Page Content Settings
//************************************************************************
//************************************************************************

//============================================================
// Server Name / HTTP Host / HTTPS
//============================================================
$page->set_data('mode', $mode);
$page->set_data('server_name', $server_name);
$page->set_data('http_host', $http_host);
$page->set_data('https', $https);

//============================================================
// Set User Info
//============================================================
$tmp = ($logged_in) ? ('yes') : ('no');
$page->set_data('logged_in', $tmp);
if ($logged_in && isset($_SESSION['user_info'])) {
	$page->set_data('user_info', $_SESSION['user_info']);
}

//============================================================
// Messages to Capture
//============================================================
// -> Generic Message
// -> Action Message
// -> Warn Message
// -> Error Message
// -> Bottom Message
// -> Page Message
// -> Timer Message
//============================================================
$message_types = 'gen_message action_message warn_message error_message bottom_message page_message timer_message';
foreach (explode(' ', $message_types) as $mtype) {
	if (isset($_SESSION[$mtype])) {
		$page->set_data($mtype, $_SESSION[$mtype]);
		unset($_SESSION[$mtype]);
	}
}

//============================================================
// Error Handler
//============================================================
if (CATCH_ERRORS) {
	$_SESSION['page_errors'] = array();
	set_error_handler('my_error_handler');
}

//************************************************************************
//************************************************************************
// Handle Page Control
//************************************************************************
//************************************************************************

//============================================================
// Build URL Path and Parts
//============================================================
$full_url_path = (isset($_SERVER['REDIRECT_URL'])) ? ($_SERVER['REDIRECT_URL']) : ($_SERVER['REQUEST_URI']);
if (strlen($full_url_path) > 0) {

	//---------------------------------------------
	// Remove Trailing Slashes
	//---------------------------------------------
	while (substr($full_url_path, strlen($full_url_path) - 1, 1) == "/") {
		$full_url_path = substr($full_url_path, 0, strlen($full_url_path) - 1);
	}

	//---------------------------------------------
	// Remove Front Slashes
	//---------------------------------------------
	while (substr($full_url_path, 0, 1) == "/") {
		$full_url_path = substr($full_url_path, 1, strlen($full_url_path));
	}
}

if ($full_url_path == '') { $full_url_path = 'home'; }
$url_parts = explode('/', $full_url_path);
if (count($url_parts) == 1 && $url_parts[0] == '') { $url_parts[0] = 'home'; }

//============================================================
// Set current pages / page arguments
//============================================================
$index_num = count($url_parts);
$curr_pages = array();
$page_args = array();
$tmp_page_args = array();
$controller = false;
for ($i = count($url_parts) - 1; $i >= 0 ; $i--) {

	//---------------------------------------------
	// Set Current Page
	//---------------------------------------------
	$index_name = 'curr_page' . $index_num;
	$curr_pages[$index_name] = $url_parts[$i];

	if (!$controller) {
		if (!is_numeric($url_parts[$i]) && substr($url_parts[$i], strlen($url_parts[$i]) - 5, 5) != '.html') {
			$tmp = implode(array_slice($url_parts, 0, $i + 1), '/');
			$tmp_controller = "{$controller_path}/{$tmp}/controller.php";
			if (file_exists($tmp_controller)) {
				$controller = $tmp_controller;
			}
		}

		if (!$controller) { $tmp_page_args[] = $url_parts[$i]; }
	}

	$index_num--;
}

$index = count($tmp_page_args);
foreach ($tmp_page_args as $arg) {
	$index_name = 'page_arg' . $index;
	$page_args[$index_name] = $arg;
	$index--;
}
$page->set_data('curr_pages', $curr_pages);
$page->set_data('page_args', $page_args);

//************************************************************************
//************************************************************************
// Execute Page Level Controller
//************************************************************************
//************************************************************************

//============================================================
// Start output buffering if using page buffering turned on
//============================================================
if (BUFFER_PAGE) { ob_start(); }

//============================================================
// Set Controller Parameters
//============================================================
$controller_args['logged_in'] = $logged_in;
$controller_args['base_path'] = $base_path;
$controller_args['file_path'] = $file_path;
$controller_args['templates'] = $templates;
$controller_args['nav_dir'] = $nav_dir;
$controller_args['curr_pages'] = $curr_pages;
$controller_args['page_args'] = $page_args;
$controller_args['controller_path'] = $controller_path;
$controller_args['controller'] = $controller;
$controller_args['site_home_url'] = $site_home_url;

//============================================================
// Execute Page Controller
//============================================================
$page_status = execute_page_controller($page, $controller_args);

//============================================================
// End output buffering if using page buffering turned on
//============================================================
if (BUFFER_PAGE) { ob_end_clean(); }

//************************************************************************
//************************************************************************
// Render Page
//************************************************************************
//************************************************************************
if (BUFFER_PAGE) { print $page; }
else { $page->render(); }

//************************************************************************
//************************************************************************
// Page Finish and Cleanup
//************************************************************************
//************************************************************************

//============================================================
// Capture Page end time
//============================================================
$page_end_time = microtime();

//============================================================
// Include Post-page Include File
//============================================================
if (file_exists(FILE_PATH . '/post_page.inc.php')) {
	include(FILE_PATH . '/post_page.inc.php');
}

//============================================================
// Unset Page / Session Errors
//============================================================
if (isset($_SESSION['page_errors'])) { unset($_SESSION['page_errors']); }
if (isset($_SESSION['throw_500'])) { unset($_SESSION['throw_500']); }

?>
