<?php
/**
* Module List Class
*
* @package		phpOpenFW
* @subpackage	Application-Logic-1-Security
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @access		private
* @version 		Started: 11-16-2005, Last updated: 4-6-2010
**/

//***************************************************************
/**
 * Module List Class
 * @package		phpOpenFW
 * @subpackage	Application-Logic-1-Security
 * @access		private
 */
//***************************************************************
class module_list {

	//***********************************************************************************
	//***********************************************************************************
	// Class variables
	//***********************************************************************************
	//***********************************************************************************
	/**
	* @var array A multi-dimensional array of all modules in the application
	**/
	private $list;

	/**
	* @var string The type of Nav format we are using (numeric, rewrite, long_url). Default is 'numeric'.
	**/
	private $nav_type;

	/**
	* Constructor function (creates the list of modules)
	**/
	//***********************************************************************************
	//***********************************************************************************
	// Constructor function
	//***********************************************************************************
	//***********************************************************************************
	public function __construct($index='')
	{
		//*******************************************
		// Nav Type / Format
		//*******************************************
		if (isset($_SESSION['nav_xml_format'])) {
			$valid_formats = array('numeric' => 'numeric', 'rewrite' => 'rewrite', 'long_url' => 'long_url');
			$this->nav_type = (isset($valid_formats[$_SESSION['nav_xml_format']])) ? ($valid_formats[$_SESSION['nav_xml_format']]) : ('numeric');
		}
		else { $this->nav_type = 'numeric'; }

		$this->list = array();
		if (isset($_SESSION['menu_array']) && is_array($_SESSION['menu_array']) && count($_SESSION['menu_array']) > 0) {
			$dir_array = array();
			$this->build_mod_list($_SESSION['menu_array'], '', -1, '', $dir_array, $index);
			$this->list = $dir_array;
		}
	}

	/**
	* Build a multi-dimensional array of all modules in the application (recursive)
	* @param string directory structure at the current module level
	* @param string directory path at the current module level
	* @param integer depth at the current module level
	* @param array the directory array (passed by reference)
	**/
	//***********************************************************************************
	//***********************************************************************************
	// build_mod_list() function
	// Fills an array that you pass it with the entire menu system.
	//***********************************************************************************
	//***********************************************************************************
	private function build_mod_list($dir_structure, $dir_path, $depth, $url, &$dir_array, $index='')
	{
        // Directory Path
		if (substr($dir_path, strlen($dir_path) - 1, 1) != '/' && $dir_path != '') { $dir_path .= '/'; }
		if ($depth != -1) { $dir_path .= $dir_structure['dir']; }
		
		// Save settings for this module
		$tmp_arr = array();
		$tmp_arr['dir'] = $dir_path;
		$tmp_arr['title'] = $dir_structure['title'];
		$tmp_arr['depth'] = $depth;
		$tmp_arr['url'] = $url;
		
		if (!empty($index) && isset($tmp_arr[$index])) {
            $dir_array[$tmp_arr[$index]] = $tmp_arr;
        }
        else {
            $dir_array[] = $tmp_arr;
        }
		
		// Increment depth / set new url
        $depth++;
        settype($url, 'string');
        if (!empty($url)) {
        	$url .= ($this->nav_type == 'rewrite' || $this->nav_type == 'long_url') ? ('/') : ('-');
        }

        // Recursively build list for sub-modules
        $count = 0;
		foreach ($dir_structure['mods'] as $key => $value) {
       		if ($this->nav_type == 'rewrite' || $this->nav_type == 'long_url') {
       			$index = $dir_path;
       			$new_url = $url . $key;
       		}
       		else {
       			$new_url = $url . $count;
       			$key = $count;
       		}
			$this->build_mod_list($dir_structure['mods'][$key], $dir_path, $depth, $new_url, $dir_array, $index);
			$count++;
		}
	}

	/**
	* Returns a complete list of modules
	* @return array complete list of modules (multi-dimensional array)
	**/
	//***********************************************************************************
	//***********************************************************************************
	// Export Function
	//***********************************************************************************
	//***********************************************************************************
	public function export() { return $this->list; }
	
}

?>
