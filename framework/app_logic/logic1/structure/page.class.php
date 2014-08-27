<?php
/**
* A simple core class to construct the basic page framework
*
* @package		phpOpenFW
* @subpackage	Application-Logic-1-Structure
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 12-29-2004, Last updated: 8-27-2014
**/

//***************************************************************
/**
 * Page Class
 * @package		phpOpenFW
 * @subpackage	Application-Logic-1-Structure
 */
//***************************************************************
abstract class page
{

	//*************************************************************************
	// Class variables
	//*************************************************************************

	/**
	* @var string root file path of the application
	**/
	protected $file_path;

	/**
	* @var string root file path of the framework
	* (pulled from config.inc.php)
	**/
	protected $frame_path;

	/**
	* @var string File path of the application logic directory for this logic
	**/
	protected $app_logic_path;

	/**
	* @var string root html path of the application
	* (pulled from config.inc.php)
	**/
	protected $html_path;

	/**
	* @var string The local html path of this module
	**/
	protected $local_html_path;
	
	/**
	* @var string The absolute file path of the current module
	**/
	protected $local_file_path;
	
	/**
	* @var string Modules Directory
	**/
	protected $mods_dir;

	/**
	* @var string Application navigation XML format (numeric, rewrite, long_url)
	**/
	protected $nav_xml_format;

	/**
	* @var string Title of the Site
	* (pulled from config.inc.php)
	**/
	protected $site_title;

	/**
	* @var string current theme to be used for the application
	* (pulled from config.inc.php)
	**/
	protected $theme;

	/**
	* @var string Current theme directory
	**/
	protected $theme_dir;

	/**
	* @var string Creator to used for the copyright
	* (pulled from config.inc.php)
	**/
	protected $creator;
	
	/**
	* @var string Account Name
	* (pulled from config.inc.php)
	**/
	protected $account_name;

	/**
	* @var string Page type, default: page
	**/
	protected $page_type;

	/**
	* @var string the entire current page in XML format
	**/
	protected $page_xml;

	/**
	* @var string the page menu in XML format
	**/
	protected $menu_xml;

	/**
	* @var array the page content in XML format
	**/
	protected $content_xml;

	/**
	* @var array Static Site XML data (config.inc.php)
	**/
	protected $site_xml;

	/**
	* @var array Application injected XML data
	**/
	protected $app_xml;

	/**
	* @var string the XSL template to be used to transform the $page_xml into HTML or XHTML
	**/
	protected $xsl_template;

	/**
	* @var string the XSL template directory to be used to transform the $page_xml into HTML or XHTML
	**/
	protected $templates_dir;

	/**
	* @var string page output format (default HTML)
	**/
	protected $output_type;

	/**
	* @var array An array of javascript files to included
	**/
	protected $js_files;
	
	/**
	* @var array An array of CSS files to included
	**/
	protected $css_files;

	/**
	* @var array An array of theme javascript files to included
	**/
	protected $theme_js_files;
	
	/**
	* @var array An array of theme CSS files to included
	**/
	protected $theme_css_files;
	
	/**
	* @var string Time Zone
	**/
	protected $time_zone;
	
	/**
	* Initialize basic variables
	* Set all basic page variables needed for page operation
	**/
	//************************************************************************************
	//************************************************************************************
	// Initialize Function
	//************************************************************************************
	//************************************************************************************
	protected function initialize()
	{
		// Set Base Variables
		$this->file_path = $_SESSION['file_path'];
		$this->frame_path = $_SESSION['frame_path'];
		$this->app_logic_path = $_SESSION['app_logic_path'];
		$this->html_path = $_SESSION['html_path'];
		$this->mods_dir = (isset($_SESSION['modules_dir'])) ? ($_SESSION['modules_dir']) : ('modules');
		$this->local_html_path = '';
		$this->site_title = (isset($_SESSION['site_title'])) ? ($_SESSION['site_title']) : ('');
		$this->theme = (!empty($_SESSION['theme'])) ? ($_SESSION['theme']) : ('default');
		$this->theme_dir = "{$this->file_path}/themes/{$this->theme}";
		$this->creator = (isset($_SESSION['creator'])) ? ($_SESSION['creator']) : ('');
		$this->time_zone = (isset($_SESSION['time_zone'])) ? ($_SESSION['time_zone']) : ('EST');
		$this->account_name = (isset($_SESSION['account_name'])) ? ($_SESSION['account_name']) : (false);
		$this->page_type = (isset($_POST['page_type'])) ? ( $_POST['page_type'] ) : ( (isset($_GET['page_type'])) ? ($_GET['page_type']) : ('page') );
		$this->output_type = 'html';
		$this->xsl_template = false;
		
		//****************************************************
		// Template Directory
		//****************************************************
		$tmp_tpl_path = "{$this->theme_dir}/templates";
		if (is_dir($tmp_tpl_path)) {
			$this->templates_dir = $tmp_tpl_path;			
		}
		else {
			$this->templates_dir = $this->file_path . '/templates';
		}

		//****************************************************
		// Nav XML Format
		//****************************************************
		if (isset($_SESSION['nav_xml_format'])) {
			$valid_formats = array('numeric' => 'numeric', 'rewrite' => 'rewrite', 'long_url' => 'long_url');
			$this->nav_xml_format = (isset($valid_formats[$_SESSION['nav_xml_format']])) ? ($valid_formats[$_SESSION['nav_xml_format']]) : ('numeric');
		}
		else { $this->nav_xml_format = 'numeric'; }

		
		//****************************************************
		// Pre-page Include Script (pre_page.inc.php)
		//****************************************************
		$pre_page_inc = "$this->file_path/$this->mods_dir/pre_page.inc.php";
		if (file_exists($pre_page_inc)) { require_once($pre_page_inc); }

		//****************************************************
		// Set Time Zone
		//****************************************************
		date_default_timezone_set($this->time_zone);
		
		//****************************************************
		// Set Javascript files, CSS files, Application XML to an empty array
		//****************************************************
		$this->js_files = array();
		$this->css_files = array();
		$this->theme_js_files = array();
		$this->theme_css_files = array();
		$this->site_xml = (isset($_SESSION['site_xml'])) ? ($_SESSION['site_xml']) : (false);
		$this->app_xml = array();
	}
	
	//************************************************************************************
	/**
	* Render the current page
	* Compile all XML parts, and if an XSL stylesheet is present for this page type
	* transform the XML into HTML/XHTML
	**/
	//************************************************************************************
	// Render function
	//************************************************************************************
	protected function render()
	{			
		//===========================================================
		// Set Master XSL Template File
		//===========================================================
		if (!$this->xsl_template) {
			$xslt_stylesheet = "{$this->templates_dir}/{$this->page_type}.xsl";
			$this->set_xsl_template($xslt_stylesheet);
		}

		//===========================================================
		// Start Page
		//===========================================================
		$page = new gen_element('page');
		$page->display_tree();

		//===========================================================
		// Start Page and set basic values
		//===========================================================
		$page->add_child(new gen_element('html_path', $this->html_path));
		$page->add_child(new gen_element('page_type', $this->page_type));
		
		//===========================================================
		// CSS files
		//===========================================================
		$tmp = new gen_element('css_files');
		$tmp->display_tree();
		foreach ($this->css_files as $file_attrs) {
			$tmp2 = new gen_element('css_file');
			$tmp2->display_tree();
			foreach ($file_attrs as $attr_key => $attr_val) {
				$tmp3 = new gen_element($attr_key, $attr_val);
				$tmp2->add_child($tmp3);
			}
			$tmp->add_child($tmp2);
		}
		$page->add_child($tmp);

		//===========================================================
		// Theme CSS files
		//===========================================================
		if ($this->theme_css_files) {
			$tmp = new gen_element('theme_css_files');
			$tmp->display_tree();
			foreach ($this->theme_css_files as $file_attrs) {
				$tmp2 = new gen_element('theme_css_file');
				$tmp2->display_tree();
				foreach ($file_attrs as $attr_key => $attr_val) {
					$tmp3 = new gen_element($attr_key, $attr_val);
					$tmp2->add_child($tmp3);
				}
				$tmp->add_child($tmp2);
			}
			$page->add_child($tmp);
		}

		//===========================================================
		// Javascript files
		//===========================================================
		$tmp = new gen_element('js_files');
		$tmp->display_tree();
		foreach ($this->js_files as $js_file) {
			$tmp->add_child(new gen_element('js_file', $js_file));
		}
		$page->add_child($tmp);

		//===========================================================
		// Theme Javascript files
		//===========================================================
		if ($this->theme_js_files) {
			$tmp = new gen_element('theme_js_files');
			$tmp->display_tree();
			foreach ($this->theme_js_files as $js_file) {
				$tmp->add_child(new gen_element('theme_js_file', $js_file));
			}
			$page->add_child($tmp);
		}

		//===========================================================
		// Copyright
		//===========================================================
		$curr_year = date('Y');
		$page->add_child(new gen_element('copyright', "$curr_year $this->creator"));

		//===========================================================
		// Account Name
		//===========================================================
		if ($this->account_name) {
			$page->add_child(new gen_element('account_name', $this->account_name));
		}
		
		//===========================================================
		// Site XML
		//===========================================================
		if ($this->site_xml) {
			if (is_array($this->site_xml)) {
				$tmp_site_xml = array2xml('site_data', $this->site_xml);
				$page->add_child("$tmp_site_xml\n");
			}
			else {
				$page->add_child(new gen_element('site_data', $this->site_xml));
			}
		}
		
		//===========================================================
		// Application XML
		//===========================================================
		if (count($this->app_xml) > 0) {
			$tmp = new gen_element('application_data');
			$tmp->display_tree();

			foreach ($this->app_xml as $xml_line) {
				if (is_array($xml_line[1])) {
					$new_xml_line = array2xml($xml_line[0], $xml_line[1]);
					$tmp->add_child("$new_xml_line\n");	
				}
				else {
					$tmp->add_child(new gen_element($xml_line[0], $xml_line[1]));
				}
			}
			$page->add_child($tmp);
		}
		
		//===========================================================
		// If Logged in
		//===========================================================
		if (isset($_SESSION['userid'])) {

			// User Info
			$tmp = new gen_element('user');
			$tmp->display_tree();
			$tmp->add_child(new gen_element('userid', $_SESSION['userid']));
			if (!isset($_SESSION['name'])) { $_SESSION['name'] = ''; }
			$tmp->add_child(new gen_element('name', xml_escape($_SESSION['name'])));
			$page->add_child($tmp);
		
			// Special links (logout)
			$exit_phrase = 'Logout';
			if ($_SESSION['auth_data_type'] == 'none') { $exit_phrase = 'Quit'; }
			$tmp = new gen_element('userlinks');
			$tmp->display_tree();
			$tmp2 = new gen_element('link');
			$tmp2->display_tree();
			$tmp2->add_child(new gen_element('linkdesc', $exit_phrase));
			$tmp2->add_child(new gen_element('linkhref', "$this->html_path/?mod=logout"));
			$tmp->add_child($tmp2);

			$page->add_child($tmp);
		}
		
		//===========================================================
		// Menu
		//===========================================================
		if (isset($this->menu_xml)) { $page->add_child($this->menu_xml); }

		//===========================================================
		// Content
		//===========================================================
		if (isset($this->content_xml) && is_array($this->content_xml) && $this->content_xml) { 
			foreach ($this->content_xml as $node) { $page->add_child($node); }
		}
		
		//===========================================================
		// Render Page
		//===========================================================
		$this->page_xml = $page;

		//===========================================================
		// Determine output type
		//===========================================================
		if ($this->output_type == 'xml') { $this->xsl_template = ''; }
		
		//===========================================================
		// Transform the page content
		//===========================================================
		ob_start();
		$sxoe = (isset($_SESSION['show_xml_on_error']) && $_SESSION['show_xml_on_error'] == 1) ? (true) : (false);
		xml_transform($this->page_xml, $this->xsl_template, $sxoe);
		$page_content = ob_get_clean();

		//===========================================================
		// Finally!! Output the results!!
		//===========================================================
		echo $page_content;

		//===========================================================
		// Post-page Include Script (post_page.inc.php)
		//===========================================================
		$post_page_inc = "$this->file_path/$this->mods_dir/post_page.inc.php";
		if (file_exists($post_page_inc)) { require_once($post_page_inc); }
	}

	//***********************************************************************************
	// Variable Access Functions
	//***********************************************************************************

	//***********************************************************************
	/**
	* @return string Root File Path of Application
	**/
	//***********************************************************************
	protected function file_path() { return $this->file_path; }
	
	//***********************************************************************
	/**
	* @return string location of framework
	**/
	//***********************************************************************
	protected function frame_path() { return $this->frame_path; }
	
	//***********************************************************************
	/**
	* @return string root html path of the application
	**/
	//***********************************************************************
	protected function html_path() { return $this->html_path; }
	
	//***********************************************************************
	/**
	* @return string html path of current page 
	**/
	//***********************************************************************
	protected function local_html_path() { return $this->local_html_path; }
	
	//***********************************************************************
	/**
	* @return string current XSL template
	**/
	//***********************************************************************
	protected function xsl_template() { return $this->xsl_template; }

	//***********************************************************************************
	// Variable Set Functions
	//***********************************************************************************

	//***********************************************************************
	/**
	* Set the XSL template directory to be used for transformation during page rendering
	*
	* @param string absolute file path to the template directory
	**/
	//***********************************************************************
	public function set_template_dir($dir) {
		if (is_dir($dir)) { $this->templates_dir = $dir; }
		else {
			$msg = __METHOD__ . "(): Template directory '{$dir}' does not exist or is not readable.";
			trigger_error($msg);
			return false;
		}
	}
	
	//***********************************************************************
	/**
	* Set the XSL Template to be used for transformation during page rendering
	*
	* @param string absolute file path to XSL Template
	**/
	//***********************************************************************
	public function set_xsl_template($xsl_temp) {
		if (file_exists($xsl_temp)) { $this->xsl_template = $xsl_temp; }
		else {
			$msg = __METHOD__ . "(): Template '{$xsl_temp}' does not exist or is not readable.";
			trigger_error($msg);
			return false;
		}
	}
	
	//***********************************************************************
	/**
	* Set the page type
	*
	* @param string page type: page (default), report, etc.??
	**/
	//***********************************************************************
	protected function set_page_type($type) { $this->page_type = $type; }

	//***********************************************************************
	/**
	* Set the ouput method to XML, default is HTML
	**/
	//***********************************************************************
	protected function set_output_xml() { $this->output_type = 'xml'; }
	
	//***********************************************************************
	/**
	* Add Application XML
	**/
	//***********************************************************************
	protected function add_xml($key='', $val='')
	{
		if ($key == '') { trigger_error('Error: add_xml() :: XML key must not be blank.'); }
		else { $this->app_xml[] = array($key, $val); }
	}

	//***********************************************************************
	/**
	* Load DB Engine
	**/
	//***********************************************************************
	protected function load_db_engine()
	{
		require_once("{$this->frame_path}/core/data_access/data_trans.class.php");
		require_once("{$this->frame_path}/core/data_access/data_query.class.php");
	}

	//***********************************************************************
	/**
	* Load Form Engine
	**/
	//***********************************************************************
	protected function load_form_engine()
	{
		require_once("{$this->frame_path}/core/structure/forms/form.class.php");
		require_once("{$this->frame_path}/core/structure/forms/form_too.class.php");
		load_form_elements();
	}
	
//*************************************************************************************
//*************************************************************************************
// End page.class.php
//*************************************************************************************
//*************************************************************************************

}

?>