<?php

//**************************************************************************
//**************************************************************************
/**
 * Main Framework Controller (phpLiteFW)
 *
 * @package		phpOpenFW
 * @subpackage	Controllers
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		http://www.gnu.org/licenses/gpl-2.0.txt
 * @version 	Started: 2-8-2008, Last updated: 8-27-2014
 */
//**************************************************************************
//**************************************************************************

//**************************************************************************
/**
 * Main Framework Controller (phpLiteFW)
 * @package		phpOpenFW
 * @subpackage	Controllers
 */
//**************************************************************************
class phplitefw_controller
{
	private $frame_path;
	private $app_plugin_folder;
	private $site_url;
	private $db_config_set;
	private $xml_ext_loaded;
	private $mode;

	//**********************************************************************
	/**
	 * phpLiteFW Controller constructor function
	 **/
	//**********************************************************************
	public function __construct($mode=false)
	{
		//============================================================
		// Include Controller Common Functions
		//============================================================
		require_once(dirname(__FILE__) . '/controller_common.inc.php');

		//============================================================
		// Set phpOpenFW Version
		//============================================================
		set_version();

		//============================================================
		// Framework Path
		//============================================================
		$this->frame_path = dirname(__FILE__);
		if (!isset($_SESSION['frame_path'])) { $_SESSION['frame_path'] = $this->frame_path; }
			
		//============================================================
		// Site URL
		//============================================================
		$this->site_url = (isset($_SERVER['SERVER_NAME'])) ? ($_SERVER['SERVER_NAME']) : ('');
		if (!isset($_SESSION['site_url'])) { $_SESSION['site_url'] = $this->site_url; }
					
		//============================================================
		// Application Plugin Folder
		//============================================================
		$this->app_plugin_folder = (isset($_SESSION['app_plugin_folder'])) ? ($_SESSION['app_plugin_folder']) : (array());
		
		//============================================================
		// Check if Data Sources have been configured
		//============================================================
		$this->db_config_set = (isset($_SESSION['db_config_set']) && $_SESSION['db_config_set']) ? (true) : (false);

		//============================================================
		// Set Mode
		//============================================================
		$this->mode = ($mode) ? ($mode) : ('litefw');
	
		//============================================================
		// Check if extensions required for XSL/Dom Transformations are loaded
		//============================================================
		$this->xml_ext_loaded = (extension_loaded('xsl') && extension_loaded('dom')) ? (true) : (false);

		//============================================================
		// Load element class
		//============================================================
		require_once("{$this->frame_path}/core/structure/objects/element.class.php");
		
		//============================================================
		// If proper extensions are loaded, 
		// load XML transformation plugin and record set list class
		//============================================================
		if ($this->xml_ext_loaded) {
			if ($this->mode == 'litefw') {
				$this->load_plugin('xml_transform');
				require_once("{$this->frame_path}/core/structure/objects/rs_list.class.php");
				require_once("{$this->frame_path}/core/structure/objects/table.class.php");
			}
		}
	}

	//************************************************************************
	/**
	* Load Database Sources Configuration Function
	* @param string Full file path to data source configuration file
	* @param bool Force the configuration to be reloaded
	*/
	//************************************************************************
	public function load_db_config($db_config, $force_config=false)
	{
		if ($force_config === true || !$this->db_config_set) {
			if (file_exists($db_config)) {
				$data_arr = array();
				require_once($db_config);
				
				if (count($data_arr) > 0) {
					$key_arr2 = array_keys($data_arr);
					foreach ($key_arr2 as $key2){
						$reg_code = $this->reg_data_source($key2, $data_arr[$key2]);
						if (!$reg_code) { $_SESSION[$key2]['handle'] = 0; }
					}
					$this->db_config_set = true;
					$_SESSION['db_config_set'] = true;
				}
				else {
					trigger_error('Error: [phplitefw_controller]::load_db_config(): No data sources defined!');
					$this->db_config_set = false;
					$_SESSION['db_config_set'] = false;
				}
			}
			else {
				trigger_error('Error: [phplitefw_controller]::load_db_config(): Data Source Configuration file does not exist!');
				$this->db_config_set = false;
				$_SESSION['db_config_set'] = false;
			}
		}
	}

	//***********************************************************************
	/**
	* Load Plugin Function
	* @param string The Name of the plugin without the ".inc.php or .class.php"
	*/
	//***********************************************************************
	public function load_plugin($plugin)
	{
		$plugin_file1 = "{$this->frame_path}/plugins/{$plugin}.inc.php";
		$plugin_file2 = "{$this->frame_path}/plugins/{$plugin}.class.php";
		
		if (file_exists($plugin_file1)) {
			require_once($plugin_file1);
			return true;
		}
		else if (file_exists($plugin_file2)) {
			require_once($plugin_file2);
			return true;
		}
		else {
			if ($this->app_plugin_folder) {
				foreach ($this->app_plugin_folder as $pf) {
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
			}
		}

		if (file_exists($plugin)) {
			require_once($plugin);
			return true;
		}

		trigger_error("Error: [phplitefw_controller]::load_plugin(): Plugin \"{$plugin}\" does not exist!");
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
	public function reg_data_source($ds_index, $ds_params) { reg_data_source($ds_index, $ds_params); }

	/**
	* Set Default Data Source Function
	* @param string Data Source Index
	*/
	//***********************************************************************
	// default_data_source() function
	// Set Default Data Source
	//***********************************************************************
	public function default_data_source($index) { default_data_source($index); }
	
	//***********************************************************************
	/**
	* Set External Plugin Folder
	* @param string File path to plugin folder
	* @param integer Boolean value 0 or 1. Remember the plugin folder in the session (1, default) or not (0).
	*/
	//***********************************************************************
	public function set_plugin_folder($dir, $remember=true)
	{
		foreach ($this->app_plugin_folder as $pf) {
			if ($pf == $dir) { return true; }
		}

		if (is_dir($dir)) {
			$this->app_plugin_folder[] = $dir;
			if ($remember) { $_SESSION['app_plugin_folder'][] = $dir; }
			return true;
		}
		else {
			trigger_error('Error: [phplitefw_controller]::set_plugin_folder() - Invalid directory passed.');
			return false;
		}
	}

	//***********************************************************************
	/**
	* Load Form Engine
	*/
	//***********************************************************************
	public function load_form_engine()
	{
		if ($this->xml_ext_loaded) {
			if (isset($this->frame_path)) {
				require_once("{$this->frame_path}/core/structure/forms/form.class.php");
				require_once("{$this->frame_path}/core/structure/forms/form_too.class.php");
				load_form_elements();
			}
			else { trigger_error('Error: [phplitefw_controller]::load_form_engine() - Framework path not set!.'); }
		}
		else {
			trigger_error('Error: [phplitefw_controller]::load_form_engine() - Cannot use form engine, XSL and/or DOM are not loaded!.');
		}
	}
	
	//***********************************************************************
	/**
	* Load Database Engine
	*/
	//***********************************************************************
	public function load_db_engine()
	{
		if (isset($this->frame_path)) {
			require_once("{$this->frame_path}/core/data_access/data_trans.class.php");
			require_once("{$this->frame_path}/core/data_access/data_query.class.php");
			$this->load_plugin('qdba');
			if ($this->mode == 'litefw') {
				$this->load_plugin('dio');
			}
		}
		else { trigger_error('Error: [phplitefw_controller]::load_db_engine() - Framework path not set!.'); }
	}

}

?>