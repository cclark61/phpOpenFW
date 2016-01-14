<?php
//************************************************************************************
//************************************************************************************
/**
* phpOpenFW Core Class
*
* @package		phpOpenFW
* @subpackage	Core
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 1/13/2016, Last updated: 1/13/2016
**/
//************************************************************************************
//************************************************************************************

class phpOpenFW
{
	//************************************************************************
    // Class Members
	//************************************************************************
    private $frame_path;
    private $pofw_is_cli;
    private $xsl_loaded;

    //================================================================================
    //================================================================================
    //================================================================================
    // Public Methods
    //================================================================================
    //================================================================================
    //================================================================================

	//************************************************************************
	/**
	 * phpOpenFW Constructor Function
	 **/
	//************************************************************************
	public function __construct($mode=false)
	{
        $this->frame_path = realpath(__DIR__ . '/../');
        $this->set_version();
        $this->detect_env();
        $this->check_xsl_loaded();
    }

	//************************************************************************
    /**
    * Check XSL Loaded Function
    */
	//************************************************************************
    public function bootstrap()
    {
        //============================================================
        // Include Core Components
        //============================================================
        require_once($this->frame_path . '/core/controller_common.inc.php');
        require_once($this->frame_path . '/core/structure/objects/element.class.php');

        //============================================================
        // Set Application Plugin Folders
        //============================================================
        set_plugin_folder("{$this->frame_path}/plugins");

        //============================================================
        // Load Standard Plugins
        //============================================================
        if (!POFW_IS_CLI) {
            load_plugin('xml_transform');
    		load_plugin('rs_list');
            load_plugin('table');
        }
    }

	//************************************************************************
    /**
    * Get URL Path Function
    */
	//************************************************************************
    public function get_url_path()
    {
    	//----------------------------------------------------
    	// If $_SERVER['REDIRECT_URL'] is set
    	//----------------------------------------------------
    	if (isset($_SERVER['REDIRECT_URL'])) {
    		return $_SERVER['REDIRECT_URL'];
    	}
    	//----------------------------------------------------
    	// If $_SERVER['PATH_INFO'] is set
    	//----------------------------------------------------
    	else if (isset($_SERVER['PATH_INFO'])) {
    		return $_SERVER['PATH_INFO'];
    	}
    	//----------------------------------------------------
    	// If $_SERVER['REQUEST_URI'] is set
    	//----------------------------------------------------
    	else if (isset($_SERVER['REQUEST_URI'])) {
    		$qs_start = strpos($_SERVER['REQUEST_URI'], '?');
    		if ($qs_start === false) {
    			return $_SERVER['REQUEST_URI'];
    		}
    		else {
    			return substr($_SERVER['REQUEST_URI'], 0, $qs_start);
    		}
    	}
    
    	return false;
    }

    //************************************************************************************
    /**
    * Get HTML Path Function
    */
    //************************************************************************************
    // Get HTML Path Function
    //************************************************************************************
    public function get_html_path()
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

    //**************************************************************************************
    /**
    * Load Configuration Function
    * @access private
    */
    //**************************************************************************************
    // Load Configuration Function
    //**************************************************************************************
    public function load_config($config_file=false)
    {
    	//*************************************************************
    	// Initialize the Arrays
    	//*************************************************************
    	$config_arr = array();
    	$data_arr = array();

    	//*************************************************************
        // Set Application Plugin Folder
    	//*************************************************************
    	$app_plugin_folder = "{$_SESSION['file_path']}/plugins";
        if (is_dir($app_plugin_folder)) {
            set_plugin_folder($app_plugin_folder);
        }

    	//*************************************************************
    	// Include the configuration file
    	//*************************************************************
        if ($config_file && file_exists($config_file)) {
            require($config_file);
        }
    	else {
        	require($_SESSION['file_path'] . '/config.inc.php');
        }
    
    	//*************************************************************
    	// Set HTML Path
    	//*************************************************************
    	if (isset($config_arr['html_path'])) {
    		$_SESSION['html_path'] = $config_arr['html_path'];
    	}
    	else {
    		$_SESSION['html_path'] = $this->get_html_path();
    	}
    
    	//*************************************************************
    	// Generate / initialize session variables from config.inc.php
    	//*************************************************************
    
        //=============================================================
    	// *** Configuration Array
        //=============================================================
    	$key_arr = array_keys($config_arr);
    	foreach ($key_arr as $key) { $_SESSION[$key] = $config_arr[$key]; }
    
        //=============================================================
    	// *** Data Source Array
        //=============================================================
    	$key_arr2 = array_keys($data_arr);
    	foreach ($key_arr2 as $key2) {
    		$reg_code = reg_data_source($key2, $data_arr[$key2]);
    		if (!$reg_code) { $_SESSION[$key2]['handle'] = 0; }
    	}
    	
    	//*************************************************************
    	// Set Authentication Data Source
    	//*************************************************************
    	if (!isset($_SESSION['auth_data_source']) || empty($_SESSION['auth_data_source'])) {
    		$_SESSION['auth_data_source'] = 'none';
    	}
    	
    	//*************************************************************
    	// Set Authentication Data Type
    	//*************************************************************
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
    }
    
    //************************************************************************************
    /**
    * Load Database Sources Configuration Function
    * @param string Full file path to data source configuration file
    * @param bool Force the configuration to be reloaded
    */
    //************************************************************************************
    public function load_db_config($db_config, $force_config=false)
    {
    	if ((bool)$force_config === true || !empty($_SESSION['db_config_set'])) {
    		if (file_exists($db_config)) {
    			$data_arr = array();
    			require_once($db_config);
    			
    			if (isset($data_arr) && count($data_arr) > 0) {
    				$key_arr2 = array_keys($data_arr);
    				foreach ($key_arr2 as $key2){
    					$reg_code = $this->reg_data_source($key2, $data_arr[$key2]);
    					if (!$reg_code) { $_SESSION[$key2]['handle'] = 0; }
    				}
    				$_SESSION['db_config_set'] = true;
    			}
    			else {
    				trigger_error('Error: load_db_config(): No data sources defined!');
    				$_SESSION['db_config_set'] = false;
    			}
    		}
    		else {
    			trigger_error('Error: load_db_config(): Data Source Configuration file does not exist!');
    			$_SESSION['db_config_set'] = false;
    		}
    	}
    }

    //************************************************************************************
    /**
    * Load Form Element Classes Function
    */
    //************************************************************************************
    public function load_form_elements()
    {
    	$form_elem_dir = $this->frame_path . '/core/structure/forms';
		require_once($form_elem_dir . '/simple.inc.php');
		require_once($form_elem_dir . '/complex/cfe.class.php');
		require_once($form_elem_dir . '/complex/ssa.class.php');
		require_once($form_elem_dir . '/complex/sst.class.php');
		require_once($form_elem_dir . '/complex/cga.class.php');
		require_once($form_elem_dir . '/complex/rga.class.php');
		require_once($form_elem_dir . '/complex/rgt.class.php');
		return true;
    }

    //************************************************************************************
    /**
    * Load Form Engine
    */
    //************************************************************************************
    public function load_form_engine()
    {
        load_form_elements();
    	if (defined('POFW_XSL_LOADED') && POFW_XSL_LOADED) {
        	$this->load_form_elements();
            require_once($this->frame_path . '/core/structure/forms/form.class.php');
    		require_once($this->frame_path . '/core/structure/forms/form_too.class.php');
        }
    	else {
    		trigger_error('Error: load_form_engine(): Cannot use form engine, XSL and/or DOM are not loaded!.');
    		return false;
    	}
    
        return true;
    }
    
    //************************************************************************************
    /**
    * Load Database Engine
    */
    //************************************************************************************
    public function load_db_engine()
    {
    	require_once($this->frame_path . '/core/data_access/data_trans.class.php');
    	require_once($this->frame_path . '/core/data_access/data_query.class.php');
        load_plugin('qdba');
    	load_plugin('dio');
    }

	//************************************************************************
    /**
    * Kill a Session Function
    */
	//************************************************************************
    public function session_kill($plugin)
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

    //================================================================================
    //================================================================================
    //================================================================================
    // Private Methods
    //================================================================================
    //================================================================================
    //================================================================================

	//************************************************************************
    /**
    * Detect Environment Function
    */
	//************************************************************************
    private function detect_env()
    {
        if (!defined('POFW_IS_CLI')) {
            $this->pofw_env = (php_sapi_name() == 'cli') ? (true) : (false);
            define('POFW_IS_CLI', $this->pofw_is_cli);
        }
        return POFW_IS_CLI;
    }

	//************************************************************************
    /**
    * Set Version Function
    */
	//************************************************************************
    private function set_version()
    {
        if (defined('PHPOPENFW_VERSION')) {
            return PHPOPENFW_VERSION;
        }
        else if (isset($_SESSION['PHPOPENFW_VERSION'])) {
            $version = $_SESSION['PHPOPENFW_VERSION'];
        }
        else {
        	$version = false;
        	$ver_file = $this->frame_path . '/../VERSION';
        	if (file_exists($ver_file)) {
        		$version = file_get_contents($ver_file);
        	}
        	$_SESSION['PHPOPENFW_VERSION'] = $version;
        }
    	define('PHPOPENFW_VERSION', $version);
    	return PHPOPENFW_VERSION;
    }

	//************************************************************************
    /**
    * Check XSL Loaded Function
    */
	//************************************************************************
    private function check_xsl_loaded()
    {
        if (!defined('POFW_XSL_LOADED')) {
            if (extension_loaded('xsl') && extension_loaded('dom')) {
                define('POFW_XSL_LOADED', true);
            }
            else {
                define('POFW_XSL_LOADED', false);
            }
            $this->xsl_loaded = POFW_XSL_LOADED;
        }
        return POFW_XSL_LOADED;
    }

}

