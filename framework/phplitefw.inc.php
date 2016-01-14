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
    private $pofw;

	//**********************************************************************
	/**
	 * phpLiteFW Controller Constructor Function
	 **/
	//**********************************************************************
	public function __construct()
	{
        //============================================================
        // Include phpOpenFW Core
        //============================================================
        require_once(__DIR__ . '/core/phpOpenFW.class.php');
        $this->pofw = new phpOpenFW();
        $this->pofw->bootstrap();
	}    

	//***********************************************************************
	/**
	* Passthrough Functions
	*/
	//***********************************************************************
	public function load_db_config($db_config, $force_config=false) { $this->pofw->load_db_config($db_config, $force_config); }
	public function load_plugin($plugin) { load_plugin($plugin); }
	public function reg_data_source($ds_index, $ds_params) { reg_data_source($ds_index, $ds_params); }
	public function default_data_source($index) { default_data_source($index); }
	public function set_plugin_folder($dir) { set_plugin_folder($dir); }
	public function load_form_engine() { $this->pofw->load_form_engine(); }
	public function load_db_engine() { $this->pofw->load_db_engine(); }

}

