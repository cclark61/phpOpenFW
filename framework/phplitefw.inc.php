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
 * @version 	Started: 2/8/2008, Last updated: 1/13/2016
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
		require_once(__DIR__ . '/controller_common.inc.php');

		//============================================================
		// Set phpOpenFW Version
		//============================================================
		set_version();

        //============================================================
        // Is XSL / DOM Available?
        //============================================================
        $this->xml_ext_loaded = check_xsl_loaded();

		//============================================================
		// Framework Path
		//============================================================
		$this->frame_path = __DIR__;
		if (!isset($_SESSION['frame_path'])) { $_SESSION['frame_path'] = $this->frame_path; }

		//============================================================
		// Site URL
		//============================================================
		$this->site_url = (isset($_SERVER['SERVER_NAME'])) ? ($_SERVER['SERVER_NAME']) : ('');
		if (!isset($_SESSION['site_url'])) { $_SESSION['site_url'] = $this->site_url; }

		//============================================================
		// Set Mode
		//============================================================
		$this->mode = ($mode) ? ($mode) : ('litefw');

        //============================================================
        // Set Application Plugin Folders
        //============================================================
        set_plugin_folder("{$this->frame_path}/plugins");

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
				load_plugin('xml_transform');
				load_plugin('rs_list');
				load_plugin('table');
			}
		}
	}    

	//***********************************************************************
	/**
	* Passthrough Functions
	*/
	//***********************************************************************
	public function load_db_config($db_config, $force_config=false) { load_db_config($db_config, $force_config); }
	public function load_plugin($plugin) { load_plugin($plugin); }
	public function reg_data_source($ds_index, $ds_params) { reg_data_source($ds_index, $ds_params); }
	public function default_data_source($index) { default_data_source($index); }
	public function set_plugin_folder($dir) { set_plugin_folder($dir); }
	public function load_form_engine() { load_form_engine(); }
	public function load_db_engine() { load_db_engine(); }

}

