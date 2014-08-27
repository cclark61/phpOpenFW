<?php
//*************************************************************************
/**
* A simple core class to construct the basic page framework
*
* @package		phpOpenFW
* @subpackage	Application-Logic-2-Structure
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 2/18/2013, Last updated: 8/27/2014
**/
//*************************************************************************

//*************************************************************************
/**
 * Page Class
 * @package		phpOpenFW
 * @subpackage	Application-Logic-2-Structure
 */
//*************************************************************************
class page
{

	//*************************************************************************
	// Class Variables
	//*************************************************************************
	protected $root_node;
	protected $data_format;
	protected $data_formats;
	protected $template;
	protected $data;
	protected $show_data_only;
	protected $no_escape_elements;
	protected $js_files;
	protected $css_files;

	//*************************************************************************
	// Constructor Function
	//*************************************************************************
    public function __construct($root_node='page')
    {
    	$this->set_root_node($root_node);
    	$this->data_formats = array('xml', 'json', 'array');
    	$this->data_format = 'xml';
    	$this->template = false;
	    $this->data = array();
	    $this->show_data_only = false;
	    $this->no_escape_elements = array(
	    	'render-function' => 'render-function',
	    	'skip-controller' => 'skip-controller',
	    	'no-render' => 'no-render'
	    );
    }

	//*************************************************************************
	// Destructor Function
	//*************************************************************************
	public function __destruct() {}

	//*************************************************************************
	// Object Conversion to String Function
	//*************************************************************************
    public function __toString()
    {
    	ob_start();
    	$this->render();	
    	return ob_get_clean();
    }	   

	//*************************************************************************
	// Display Error Function
	//*************************************************************************
    protected function display_error($function, $error_msg)
    {
    	$tmp_msg = 'Class [' . __CLASS__ . "]::{$function}() - ";
    	$tmp_msg .= "Error: {$error_msg}";
    	trigger_error($tmp_msg);
    }

	//*************************************************************************
	// Dump Data Function
	//*************************************************************************
    public function dump_data()
    {
	    if (function_exists('print_array')) {
		    print_array($this->data);
	    }
	    else {
		    var_dump($this->data);
	    }
	}

	//*************************************************************************
	// Set Root Node Function
	//*************************************************************************
	public function set_root_node($root_node)
	{
		$new_rn = (string)$root_node;
		if ($new_rn == '' || is_numeric($new_rn)) {
			$msg = "Invalid root node name '{$new_rn}'. Root node name must be a string and not entirely numeric.";
			$this->display_error(__FUNCTION__, $msg);
			return false;
		}
		$this->root_node = $new_rn;
		return true;
	}

	//*************************************************************************
	// Set Data Format Function
	//*************************************************************************
	public function set_data_format($format)
	{
		if (!$format) { return false; }
		$format = strtolower($format);
		if (!in_array($format, $this->data_formats)) {
			$data_formats = implode(', ', $this->data_formats);
			$msg = "Invalid data format '{$format}'. Data format must be one of the following: {$data_formats}";
			$this->display_error(__FUNCTION__, $msg);
			return false;			
		}

		$this->data_format = $format;
		return true;
	}

	//*************************************************************************
	// No Escape Function
	//*************************************************************************
	public function no_escape_element($e)
	{
		settype($e, 'string');
		if ($e !== '') {
			$this->no_escape_elements[$e] = $e;
			return true;
		}
		return false;
	}

	//*************************************************************************
	// Set Template Function
	//*************************************************************************
	public function set_template($template)
	{
		$this->template = (string)$template;
		return true;
	}

	//*************************************************************************
	// Set Page Data Function
	//*************************************************************************
    public function set_data($node, $val, $append=false)
    {
		if (isset($this->data[$node]) && $append) {
			$this->data[$node] .= $val;
		}
		else { $this->data[$node] = $val; }
		return true;

	}

	//*************************************************************************
	// Get Data Function
	//*************************************************************************
	public function get_data($node)
	{
		return (isset($this->data[$node])) ? ($this->data[$node]) : (false);
	}

	//*************************************************************************
	// Get All Data Function
	//*************************************************************************
	public function get_all_data($node)
	{
		return $this->data;
	}

	//*************************************************************************
	// Delete Data Function
	//*************************************************************************
	public function delete_data($node)
	{
		if (isset($this->data[$node])) {
			unset($this->data[$node]);
			return true;
		}
		return false;
	}

	//*************************************************************************
	// Set Show Data Only Function
	//*************************************************************************
	public function set_show_data_only($flag=true) { $this->show_data_only = (bool)$flag; }

	//*************************************************************************
	/**
	* Add a Javascript File to be included
	* @param string Javascript File
	**/
	//*************************************************************************
	public function add_js_file($file)
	{
		if ($file) { $this->js_files[] = $file; }
	}

	//*************************************************************************
	/**
	* Add a CSS File to be included
	* @param array CSS link attributes
	**/
	//*************************************************************************
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

	//*************************************************************************
	//*************************************************************************
	// Render Function
	//*************************************************************************
	//*************************************************************************
	public function render()
	{

		//-------------------------------------------------------------
		// No Render?
		//-------------------------------------------------------------
		if ($this->get_data('no-render')) {
			return true;
		}

		//-------------------------------------------------------------
		// JavaScript / CSS Add-in Files
		//-------------------------------------------------------------
		if (!empty($this->js_files)) {
			$this->set_data('js_files', $this->js_files);
		}
		if (!empty($this->css_files)) {
			$this->set_data('css_files', $this->css_files);
		}

		//-------------------------------------------------------------
		// Escape Data (or not)
		//-------------------------------------------------------------
		if ($this->data_format == 'xml') {
			foreach ($this->data as $dkey => &$dval) {
				if (!isset($this->no_escape_elements[$dkey])) {
					$dval = xml_escape_array($dval);
				}
			}
		}

		//-------------------------------------------------------------
		// Create Data
		//-------------------------------------------------------------
		if ($this->data_format == 'xml') {
			$data = array2xml($this->root_node, $this->data);
		}
		else if ($this->data_format == 'json') {
			$data = json_encode($this->data);
		}
		else {
			$data = $this->data;
		}

		//-------------------------------------------------------------
		// Output
		//-------------------------------------------------------------
		if ($this->show_data_only) {
			if (is_array($data)) {
				print_array($data);
			}
			else {
				print $data;
			}
			return true;
		}
		else {
			$render_function = $this->get_data('render-function');

			//----------------------------------------------------
			// XML
			//----------------------------------------------------
			if ($this->data_format == 'xml') {
				if ($render_function) {
					return $render_function($data, $this->template);
				}
				else {
					if (file_exists($this->template)) {
						return xml_transform($data, $this->template);
					}
					else {
						if (empty($this->template)) {
							print "No template file has been specified.";
						}
						else {
							print "Invalid template file specified.";
						}
					}
				}
			}
			//----------------------------------------------------
			// JSON / Array
			//----------------------------------------------------
			else {
				if ($render_function) {
					return $render_function($data, $this->template);
				}
				else {
					print "No valid render function was specified.";
				}				
			}
		}

		return false;
	}

}

?>