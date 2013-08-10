<?php
/**
* Module Nav Class
*
* @package		phpOpenFW
* @subpackage	Application-Logic-1-Security
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 8-18-2005, Last updated: 8-31-2011
* @access		private
**/

//***************************************************************
// Contributions by Lucas Hoezee ( http://thecodify.com/ )
// 4/13/2011
//***************************************************************

//***************************************************************
/**
 * Nav Class
 * @package		phpOpenFW
 * @subpackage	Application-Logic-1-Security
 * @access		private
 */
//***************************************************************
class nav {

	//***********************************************************************************
	//***********************************************************************************
	// Class variables
	//***********************************************************************************
	//***********************************************************************************
	/**
	* @var string base directory of modules
	**/
	private $base_dir;
	
	/**
	* @var string path to top level local.inc.php
	**/
	private $local_inc;
	
	/**
	* @var array navigation array
	**/
	private $nav_array;

	/**
	* @var array navigation array indexed by directory
	**/
	private $nav_array2;
	
	/**
	* @var string Sort Type (dir or title)
	**/
	private $sort_type;

	/**
	* @var string The type of Nav format we are using (numeric, rewrite, long_url). Default is 'numeric'.
	**/
	private $nav_type;

	/**
	* Nav constructor function
	* @param string base directory of modules
	**/
	//*************************************************************************
	//*************************************************************************
	// Constructor function
	//*************************************************************************
	//*************************************************************************
	public function __construct($base_dir)
	{
		//*******************************************
		// Nav Type / Format
		//*******************************************
		if (isset($_SESSION['nav_xml_format'])) {
			$valid_formats = array('numeric' => 'numeric', 'rewrite' => 'rewrite', 'long_url' => 'long_url');
			$this->nav_type = (isset($valid_formats[$_SESSION['nav_xml_format']])) ? ($valid_formats[$_SESSION['nav_xml_format']]) : ('numeric');
		}
		else { $this->nav_type = 'numeric'; }

		//*******************************************
		// Set object variables
		//*******************************************
		$this->base_dir = $base_dir . '/';
		$this->local_inc = $this->base_dir . 'local.inc.php';
		$this->sort_type = (isset($_SESSION['nav_sort'])) ? ($_SESSION['nav_sort']) : ('title');

		// Set Main Menu Title
		if (file_exists($this->local_inc)) {
			include($this->local_inc);
			$mod_order = (isset($mod_order)) ? ($mod_order) : (100);
		}
		else {
			$mod_title = 'Main';
			$mod_order = 100;
		}
		
		// Build the Nav Structure Array
		$this->nav_array = array(
			'title' => $mod_title, 
			'dir' => $this->base_dir, 
			'mod_order' => $mod_order,
			'mods' => $this->build_nav($this->base_dir)
		);
		$this->nav_array2 = array();
		
		// Sort Modules Array
		$tmp_dirs = array();
		if ($this->sort_type == 'dir') {
			foreach ($this->nav_array['mods'] as $tmp_key => $tmp_val) {
				$tmp_dirs[$tmp_key] = $tmp_val['dir'];
			}			
		}
		else if ($this->sort_type == 'mod_order') {
			foreach ($this->nav_array['mods'] as $tmp_key => $tmp_val) {
				$tmp_dirs[$tmp_key] = $tmp_val['mod_order'];
			}
		}
		else {
			foreach ($this->nav_array['mods'] as $tmp_key => $tmp_val) {
				$tmp_dirs[$tmp_key] = $tmp_val['title'];
			}			
		}
		array_multisort($tmp_dirs, SORT_ASC, $this->nav_array['mods']);
	}

	/**
	* Build the Navigation for the current user (recursive function)
	* @param string directory of modules from which to build navigation
	* @return array navigation array built to for current user, complete with access privileges and module structure
	**/
	//*************************************************************************
	// Build Nav function
	//*************************************************************************
	private function build_nav($dir)
	{
		// Build NAV array
		$main_array = array();
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					if (filetype($dir . $file) == 'dir' && $file != '.' && $file != '..' && substr($file, 0, 1) != '.'){
						$local_inc = $dir . $file . '/' . 'local.inc.php';
						if (file_exists($local_inc)) {
							include($local_inc);
							$sub_array = $this->build_nav("$dir/$file/");
							
							// Sort Modules Array
							$tmp_dirs = array();
							if ($this->sort_type == 'dir') {
								foreach ($sub_array as $tmp_key => $tmp_val) {
									$tmp_dirs[$tmp_key] = $tmp_val['dir'];
								}
							}
							else if ($this->sort_type == 'mod_order') {
                                foreach ($sub_array as $tmp_key => $tmp_val) {
									$tmp_dirs[$tmp_key] = $tmp_val['mod_order'];
								}
                            }
							else {
								foreach ($sub_array as $tmp_key => $tmp_val) {
									$tmp_dirs[$tmp_key] = $tmp_val['title'];
								}
							}
							array_multisort($tmp_dirs, SORT_ASC, $sub_array);

							// Add module to array
							if (isset($mod_title)) {
								$mod_order = (isset($mod_order)) ? ($mod_order) : (100);
								$mod_array = array(
									'title' => $mod_title, 
									'dir' => $file, 
									'mod_order' => $mod_order,
									'mods' => $sub_array
								);

								// Rewrite / Long URL Nav Style
								if ($this->nav_type == 'rewrite' || $this->nav_type == 'long_url') {
									$main_array[$file] = $mod_array;
								}
								// Numeric Nav Style
								else {
									array_push($main_array, $mod_array);
								}

								// Clear variables
								unset($mod_title);
								unset($mod_order);
							}
						}
					}
				}
				closedir($dh);
			}
			return $main_array;
		}
		else {
			print "This is not a Directory!!\n";
			return '';
		}
	}

	/**
	* Reindex the Navigation for the current user (recursive function)
	**/
	//*************************************************************************
	// Reindex Nav function
	//*************************************************************************
	private function reindex_nav($curr_mods, $index)
	{
		$tmp_mods = array();
		$tmp_sub_mods = array();
		foreach ($curr_mods as $key => $value) {
			if ($key == 'mods') {
				foreach ($curr_mods['mods'] as $key2 => $value2) {
					$tmp_sub_mods[$curr_mods['mods'][$key2]['dir']] = $this->reindex_nav($curr_mods['mods'][$key2], $key2);
				}
			}
			else { $tmp_nav[$key] = $value; }
		}
		$tmp_nav['index'] = $index;
		$tmp_nav['mods'] = $tmp_sub_mods;
		
		return $tmp_nav;
	}

	/**
	* Export the Navigation Array
	* @return array navigation array built to for current user, complete with access privileges and module structure
	**/
	//*************************************************************************
	// Export Nav Function
	//*************************************************************************
	public function export() { return $this->nav_array; }
	
	/**
	* Export the Navigation Array indexed by directory name
	* @return array navigation array built for current user, complete with access privileges and module structure
	**/
	//*************************************************************************
	// Export Nav Function [2]
	//*************************************************************************
	public function export2()
	{
		$this->nav_array2 = $this->reindex_nav($this->nav_array, -1);
		return $this->nav_array2;
	}
}

?>