<?php
/**
* A simple core class to construct the module page framework
*
* @package		phpOpenFW
* @subpackage	Application-Logic-1-Structure
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 1-19-2006, Last updated: 1-13-2016
**/

//***************************************************************
/**
 * Module Page Class
 * @package		phpOpenFW
 * @subpackage	Application-Logic-1-Structure
 */
//***************************************************************
class module extends page
{

	//************************************************************************	
	// Class variables
	//************************************************************************

	// Module Page specific variables
	
	/**
	* @var string Current module
	**/
	private $mod;
	
	/**
	* @var array Module arguments of the current page
	**/
	private $mod_args;

	/**
	* @var array Module URL parameters that exist past the last known controller
	**/
	private $mod_params;
	
	/**
	* @var string Action of the current page
	**/
	private $action;
	
	/**
	* @var string Html path of the current module
	**/
	private $mod_url;
	
	/**
	* @var string Html path of the current page
	**/
	private $page_url;
	
	// Navigation variables
	/**
	* @var string Navigation array set by nav class
	**/
	private $curr_array;

	/**
	* @var string The XSL Stylesheet to use for the content
	**/
	private $content_xsl;

	/**
	* @var string Module Controller
	**/
	private $mod_controller;

	/**
	* @var boolean Module Controller was found?
	**/
	private $mod_controller_found;

	/**
	* @var boolean Skip execution of Module Controller
	**/
	private $skip_mod_controller;

	/**
	* Module constructor function
	**/
	//************************************************************************************
	//************************************************************************************
	// Module constructor function
	//************************************************************************************
	//************************************************************************************
	public function __construct()
	{
		//----------------------------------------------------
		// Initialize
		//----------------------------------------------------
		parent::initialize();
		$this->mod_controller_found = 1;
		$this->mod_params = array();
		$this->skip_mod_controller = false;

		//************************************************************************************
		// Parse Module Arguments
		//************************************************************************************

		//**************************************
		// Rewrite Nav Style
		//**************************************
		if ($this->nav_xml_format == 'rewrite') {

			//----------------------------------------------------
			// Get URL Path
			//----------------------------------------------------
			$this->mod = POFW_get_url_path();

			//----------------------------------------------------
			// Remove contents of $this->html_path if it exists
			//----------------------------------------------------
			if ($this->html_path != '') {
				$this->mod = str_replace($this->html_path, '', $this->mod);
			}

			//----------------------------------------------------
			// Check for leading slash
			//----------------------------------------------------
			if (substr($this->mod, 0, 1) == '/') {
				$this->mod = substr($this->mod, 1, strlen($this->mod) - 1);
			}

			//----------------------------------------------------
			// Check for trailing slash
			//----------------------------------------------------
			if (substr($this->mod, strlen($this->mod) - 1, 1) == '/') {
				$this->mod = substr($this->mod, 0, strlen($this->mod) - 1);
			}

			$this->mod_args = ($this->mod != '') ? (explode('/', $this->mod)) : (array());
		}

		//**************************************
		// Long URL Nav Style
		//**************************************
		else if ($this->nav_xml_format == 'long_url') {

			//----------------------------------------------------
			// Pull module arguments from $_SERVER['PHP_SELF']
			//----------------------------------------------------
			$this->mod = $_SERVER['PHP_SELF'];

			//----------------------------------------------------
			// Remove contents of $this->html_path if it exists
			//----------------------------------------------------
			if ($this->html_path != '') {
				$this->mod = str_replace($this->html_path, '', $this->mod);
			}

			//----------------------------------------------------
			// Check for leading slash
			//----------------------------------------------------
			if (substr($this->mod, 0, 1) == '/') {
				$this->mod = substr($this->mod, 1, strlen($this->mod) - 1);
			}

			//----------------------------------------------------
			// Remove "index.php/"
			//----------------------------------------------------
			if (substr($this->mod, 0, 10) == 'index.php/') {
				$this->mod = substr($this->mod, 10, strlen($this->mod) - 1);
			}

			//----------------------------------------------------
			// Check for trailing slash
			//----------------------------------------------------
			if (substr($this->mod, strlen($this->mod) - 1, 1) == '/') {
				$this->mod = substr($this->mod, 0, strlen($this->mod) - 1);
			}

			$this->mod_args = ($this->mod != '') ? (explode('/', $this->mod)) : (array());
		}

		//**************************************
		// Numeric Nav Style
		//**************************************
		else {
			if (isset($_POST['mod'])) { $this->mod = $_POST['mod']; }
			else if (isset($_GET['mod'])) { $this->mod = $_GET['mod']; }
			else { $this->mod = '-1'; }
			$this->mod_args = preg_split('/-/', $this->mod, '-1');
		}

		// Set module string to "-1" if empty
		if ($this->mod == '') { $this->mod = '-1'; }

		//************************************************************************************
		// Navigation parameters
		//************************************************************************************
		$this->curr_array = $_SESSION['menu_array'];
		$this->local_file_path = (isset($this->curr_array['dir'])) ? ($this->curr_array['dir']) : ('');
		$new_mod_args = array();

		foreach ($this->mod_args as $arg) {
			if ($this->mod_controller_found) {
				if (isset($this->curr_array['mods']) && array_key_exists($arg, $this->curr_array['mods'])) {
					$this->local_file_path .= $this->curr_array['mods'][$arg]['dir'] . '/';
					$this->local_html_path .= $this->curr_array['mods'][$arg]['dir'] . '/';
					$this->curr_array = $this->curr_array['mods'][$arg];
					$new_mod_args[] = $arg;
				}
				else {
					$this->mod_controller_found = 0;
					$this->mod_params[] = $arg;
				}
			}
			else {
				$this->mod_params[] = $arg;
			}
		}

		$this->local_html_path = $this->html_path . '/' .  $this->mods_dir . '/' . $this->local_html_path;
		$this->mod_controller = $this->local_file_path . 'controller.php';

		//************************************************************************************
		// Correct / reset module string and arguments
		//************************************************************************************
		$this->mod_args = $new_mod_args;
		$this->mod = ($this->nav_xml_format == 'rewrite' || $this->nav_xml_format == 'long_url') ? (implode('/', $new_mod_args)) : (implode('-', $new_mod_args));

		//************************************************************************************
		// Set Page URL
		//************************************************************************************
		if ($this->mod == '-1' || $this->mod == '') {
			$this->page_url = "{$this->html_path}/";
		}
		else {
			if ($this->nav_xml_format == 'rewrite') {
				$this->page_url = "{$this->html_path}/{$this->mod}/";
			}
			else if ($this->nav_xml_format == 'long_url') {
				$this->page_url = "{$this->html_path}/index.php/{$this->mod}/";
			}
			else {
				$this->page_url = "{$this->html_path}/?mod={$this->mod}";
			}
		}

		//************************************************************************************
		// Action
		//************************************************************************************
		if (isset($_POST['action'])) { $this->action = $_POST['action']; }
		else if (isset($_GET['action'])) { $this->action = $_GET['action']; }
		else { $this->action = '-1'; }

	}
	
	/**
	* Module destructor function
	**/
	//************************************************************************************
	//************************************************************************************
	// Module destructor function
	//************************************************************************************
	//************************************************************************************
	public function __destruct() { parent::render(); }
	
	/**
	* Module render function
	**/
	//************************************************************************************
	//************************************************************************************
	// Module render function
	//************************************************************************************
	//************************************************************************************
	public function render()
	{
        //============================================================
		// Core Components
		//============================================================
		load_db_engine();
		load_form_engine();
		require_once("{$this->frame_path}/core/structure/objects/rs_list.class.php");
		require_once("{$this->frame_path}/core/structure/objects/table.class.php");

        //============================================================
		// Application Logic
		//============================================================
		require_once("{$this->app_logic_path}/security/module_list.class.php");

        //============================================================
		// Menu
		//============================================================
		$this->menu_xml = $_SESSION['menu_xml'];

        //============================================================
		// Content
		//============================================================
		$this->content_constructor();
	}
	
	/**
	* Module content constructor function (* Note: This is not a class constructor function)
	**/
	//************************************************************************************
	//************************************************************************************
	// Content constructor function
	//************************************************************************************
	//************************************************************************************
	public function content_constructor()
	{
		$this->content_xml = array();

        //============================================================
		// Pre-module Include Script (pre_module.inc.php)
		//============================================================
		$pre_mod_inc = "{$this->file_path}/{$this->mods_dir}/pre_module.inc.php";
		if (file_exists($pre_mod_inc)) { require_once($pre_mod_inc); }

        //============================================================
		// Was the intended module controller found?
		//============================================================
		$this->content_xml[] = new gen_element('mod_controller_found', $this->mod_controller_found);

        //============================================================
		// Current Module Parameters
		//============================================================
		$this->content_xml[] = new gen_element('current_module', $this->mod);
		if ($this->mod == '-1') { $tmp_mod_args = array('-1'); }
		else {
			$ex_chr = ($this->nav_xml_format == 'rewrite') ? ('/') : ('-');
			$tmp_mod_args = explode($ex_chr, $this->mod);
		}

        //============================================================
		// Current Module Depth
		//============================================================
		$curr_mod_depth  = count($tmp_mod_args);
		$this->content_xml[] = new gen_element('current_module_depth', $curr_mod_depth);

        //============================================================
		// Current Module Arguments
		//============================================================
		$tmp = new gen_element('current_module_args');
		$tmp->display_tree();
		foreach ($tmp_mod_args as $key => $arg) {
			$index_key = $key + 1;
			$tmp->add_child(new gen_element('module_arg', $arg, array('index' => $index_key)));
		}
		$this->content_xml[] = $tmp;

        //============================================================
		// Start Content Block
		//============================================================
		$content_node = new gen_element('content');
		$content_node->display_tree();

        //============================================================
		// Section Title
		//============================================================
		$local_inc = $this->local_file_path . 'local.inc.php';
		if (file_exists($local_inc)) { include($local_inc); }
		
		if (isset($mod_title)) {
			$tmp2 = new gen_element('section_title');
			$tmp2->display_tree();
			$tmp2->add_child(new gen_element('linkhref', $this->page_url));
			$tmp2->add_child(new gen_element('desc', $mod_title));
			$content_node->add_child($tmp2);
		}

        //============================================================
        // Controller Exists
        //============================================================
		if (file_exists($this->mod_controller)) {

            //--------------------------------------------------------
			// Module Exists
            //--------------------------------------------------------
			$content_node->add_child(new gen_element('controller_exists', 1));
			define('CONTROLLER_EXISTS', true);

            //--------------------------------------------------------
			// Set $_GET and $_POST arrays to local variables
			// GET first, then POST to prevent GET variables over writing POST variables
            //--------------------------------------------------------
			extract($_GET, EXTR_PREFIX_SAME, "GET_");
			extract($_POST, EXTR_PREFIX_SAME, "POST_");
			
            //--------------------------------------------------------
			// Include local controller
            //--------------------------------------------------------
			ob_start();
			if (!$this->skip_mod_controller) { require_once($this->mod_controller); }
			
            //--------------------------------------------------------
			// Perform an XML Transformation if necessary
            //--------------------------------------------------------
			if (isset($this->content_xsl) && !empty($this->content_xsl)) {
				$tmp_xml = ob_get_clean();
				ob_start();
				xml_transform($tmp_xml, $this->content_xsl);
			}
			
			$tmp_content = ob_get_clean();
			
			$content_node->add_child(new gen_element('content_data', xml_escape($tmp_content)));
			$this->content_xml[] = $content_node;
		}
		//============================================================
		// Controller does NOT Exist
		//============================================================
		else {
			$content_node->add_child(new gen_element('controller_exists', 0));
			define('CONTROLLER_EXISTS', false);
		}

        //============================================================
		// Post-module Include Script (post_module.inc.php)
		//============================================================
		$post_mod_inc = "{$this->file_path}/{$this->mods_dir}/post_module.inc.php";
		if (file_exists($post_mod_inc)) { require_once($post_mod_inc); }
	}

	//************************************************************************************
	//***********************************************************************************
	// Variable Access Functions (getters)
	//***********************************************************************************
	//************************************************************************************

	//***********************************************************************
	/**
	* @return string current module html path
	**/
	//***********************************************************************
	public function mod_path() { return $this->local_file_path; }
	
	//***********************************************************************
	/**
	* @return string current page action
	**/
	//***********************************************************************
	public function action() { return $this->action; }

	//***********************************************************************
	/**
	* Returns an array of values that was set with set_mod_vars($vars) otherwise returns FALSE
	* @return array an array of values set for this module or FALSE
	**/
	//***********************************************************************
	public function get_mod_vars()
	{
		$mod_index = 'mod-' . $this->mod;
		if (isset($_SESSION[$mod_index]) && !empty($_SESSION[$mod_index])) {
			return $_SESSION[$mod_index];
		}
		else {
			return false;
		}
	}
	
	//***********************************************************************
	/**
	* Returns a variable's value from the current module's session array
	* @return mixed A variable's value from the current module's session array
	**/
	//***********************************************************************
	public function get_mod_var($var_name)
	{
		$mod_index = 'mod-' . $this->mod;
		if (isset($_SESSION[$mod_index][$var_name]) && !empty($_SESSION[$mod_index])) {
			return $_SESSION[$mod_index][$var_name];
		}
		else {
			return false;
		}
	}
	
	//***********************************************************************
	/**
	* Print the current module's session array
	**/
	//***********************************************************************
	public function print_mod_vars()
	{
		$mod_index = 'mod-' . $this->mod;
		if (isset($_SESSION[$mod_index])) {
			print "<pre>\n";
			print_r($_SESSION[$mod_index]);
			print "</pre>\n";
		}
		else { print "No module variables set.\n"; }
	}

	//************************************************************************************
	//************************************************************************************
	// Variable Setting Functions (setters)
	//************************************************************************************
	//************************************************************************************

	//***********************************************************************
	/**
	* set the current page action
	* @param string new page action
	**/	
	//***********************************************************************
	public function set_action($new_action) { $this->action = $new_action; }

	//***********************************************************************
	/**
	* Set the current page url (this function may be unnecessary)
	* @param string page url
	**/	
	//***********************************************************************
	public function set_page_url($new_page_url)	{ $this->page_url = $new_page_url; }

	//***********************************************************************
	/**
	* Set the content XSL stylesheet
	* @param string Full file path of XSL stylesheet
	**/	
	//***********************************************************************
	public function set_content_xsl($xsl_file)
	{ 
		if (file_exists($xsl_file)) { $this->content_xsl = $xsl_file; }
		else { echo "<strong>set_content_xsl(): Invalid file path!!<br/> \"{$xsl_file}\" does not exist!</strong></br>\n"; }
	}

	//***********************************************************************
	/**
	* Set variables in array format for the current module
	* @param array an array of values to be accessed later
	**/
	//***********************************************************************
	public function set_mod_vars($vars)
	{
		if (is_array($vars)) {
			$mod_index = 'mod-' . $this->mod;
			$_SESSION[$mod_index] = $vars;
			return true;
		}
		else { return false; }
	}

	//***********************************************************************
	/**
	* Set a variable in the current module's session array
	* @param string The name of the variable
	* @param mixed The Value of the variable
	**/
	//***********************************************************************
	public function set_mod_var($var_name, $var_value)
	{
		$mod_index = 'mod-' . $this->mod;
		$_SESSION[$mod_index][$var_name] = $var_value;
	}

	//***********************************************************************
	/**
	* Destroy the current module's session array
	**/
	//***********************************************************************
	public function clear_mod_vars()
	{
		$mod_index = 'mod-' . $this->mod;
		if (isset($_SESSION[$mod_index])) {
			unset($_SESSION[$mod_index]);
			return true;
		}
		else {
			return false;
		}

	}
	
	//***********************************************************************
	/**
	* Destroy a current module variable
	**/
	//***********************************************************************
	public function clear_mod_var($var='')
	{
		$mod_index = 'mod-' . $this->mod;
		if ($var != '' && isset($_SESSION[$mod_index][$var])) { 
			unset($_SESSION[$mod_index][$var]);
			return true;
		}
		else {
			return false;
		}
	}

	//***********************************************************************
	/**
	* Add a Javascript File to be included
	* @param string Javascript File
	**/
	//***********************************************************************
	public function add_js_file($file)
	{
		if ($file) { $this->js_files[] = $file; }
	}

	//***********************************************************************
	/**
	* Add a CSS File to be included
	* @param array CSS link attributes
	**/
	//***********************************************************************
	public function add_css_file($file_attrs)
	{
		if (is_array($file_attrs)) {
			if (!isset($file_attrs['rel'])) { $file_attrs['rel'] = 'stylesheet'; }
			if (!isset($file_attrs['type'])) { $file_attrs['type'] = 'text/css'; }
			if (!isset($file_attrs['media'])) { $file_attrs['media'] = 'all'; }
			$this->css_files[] = $file_attrs;
		}
		else {
			settype($file_attrs, 'string');
			$css_file = $file_attrs;
			$file_attrs = array();
			$file_attrs['href'] = $css_file;
			$file_attrs['rel'] = 'stylesheet';
			$file_attrs['type'] = 'text/css';
			$file_attrs['media'] = 'all';
			$this->css_files[] = $file_attrs;
		}
	}

	//***********************************************************************
	/**
	* Add a Theme Javascript File to be included
	* @param string Javascript File
	**/
	//***********************************************************************
	public function add_theme_js_file($file)
	{
		if ($file) { $this->theme_js_files[] = $file; }
	}

	//***********************************************************************
	/**
	* Add a Theme CSS File to be included
	* @param array CSS link attributes
	**/
	//***********************************************************************
	public function add_theme_css_file($file_attrs)
	{
		if (is_array($file_attrs)) {
			if (!isset($file_attrs['rel'])) { $file_attrs['rel'] = 'stylesheet'; }
			if (!isset($file_attrs['type'])) { $file_attrs['type'] = 'text/css'; }
			if (!isset($file_attrs['media'])) { $file_attrs['media'] = 'all'; }
			$this->theme_css_files[] = $file_attrs;
		}
		else {
			settype($file_attrs, 'string');
			$css_file = $file_attrs;
			$file_attrs = array();
			$file_attrs['href'] = $css_file;
			$file_attrs['rel'] = 'stylesheet';
			$file_attrs['type'] = 'text/css';
			$file_attrs['media'] = 'all';
			$this->theme_css_files[] = $file_attrs;
		}
	}

	//***********************************************************************
	/**
	* Toggle the Skip Module Controller Flag
	* @param bool Skip Flag
	**/
	//***********************************************************************
	public function skip_module($skip=true)
	{
		$this->skip_mod_controller = (bool)$skip;
	}

}

