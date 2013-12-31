<?php
/**
* Record Set List Object class for contructing tabular lists
* @package		phpOpenFW
* @subpackage	Objects
* @author		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version		Started: 1-27-2006 Updated: 2-17-2013
*/

//***************************************************************
/**
 * Record Set List Class
 * @package		phpOpenFW
 * @subpackage	Objects
 */
//***************************************************************
class rs_list
{

	//*********************************************************************
	// Class variables
	//*********************************************************************
	/**
	* @var array An array that describes how to layout the list
	**/
	protected $data_outline;

	/**
	* @var array The record set to build the list from
	**/
	protected $record_set;

	/**
	* @var integer Number of columns in a recordset
	**/
	protected $cols;

	/**
	* @var string Label to be used for the list
	**/
	protected $label;

	/**
	* @var string The XML derived from the record set
	**/
	protected $xml;

	/**
	* @var string The XSL template to be used during transformation
	**/
	protected $xsl_template;

	/**
	* @var string The CSS id of the table
	**/
	protected $table_id;

	/**
	* @var string The CSS class of the table
	**/
	protected $table_class;

	/**
	* @var bool Display the column headers (TRUE - On, FALSE - Off)
	**/
	protected $display_col_headers;

	/**
	* @var string Multi-part Cell Separator
	**/
	protected $separator;

	/**
	* @var array Record set attributes
	**/
	protected $rs_attrs;

	/**
	* @var String Message Displayed upon empty record set.
	**/
	protected $empty_msg;

	/**
	* @var String Column to group by.
	**/
	protected $group_by;

	/**
	* @var Array Column Header Values (Key => Val).
	**/
	protected $group_by_vals;

	/**
	* @var Array Record Set Keys.
	**/
	protected $rs_keys;

	/**
	* @var Bool Escape Data (CDATA) Default = true.
	**/
	protected $escape_data;

	/**
	* @var Bool Use the default template
	**/
	protected $use_default_template;

	//****************************************************************************
	/**
	* rs_list contstructor function
	* @param array an associative array that describes the field layout of the list
	* [format: "field" => "label"]
	* @param array the record set to be displayed
	**/
	//****************************************************************************
	public function __construct($do, $rs)
	{
		$this->data_outline = $do;
		$this->record_set = $rs;
		$this->display_col_headers = true;
		$this->separator = "<br/>\n";
		$this->empty_msg = '[!] No results found.';
		$this->group_by = false;
		$this->group_by_vals = false;
		$this->xml = '';
		$this->rs_keys = (is_array($this->record_set)) ? (array_keys($this->record_set)) : (false);
		$this->escape_data = true;
		$this->use_default_template = true;;

		//---------------------------------------------------
		// Validate Data Order
		//---------------------------------------------------
		if (!is_array($this->data_outline)) {
			$this->display_error(__FUNCTION__, 'Data Display Order must be an array.');
			$this->data_outline = array();
		}

		//---------------------------------------------------
		// Number of Columns in Record Set
		//---------------------------------------------------
		if (count($this->record_set) > 0) {
            $first_key = key($this->record_set); 
            $this->cols = count($this->record_set[$first_key]);
        }
		else {
			$this->cols = 0;
		}

		//---------------------------------------------------
		// Default Template
		//---------------------------------------------------
		if (isset($_SESSION['rs_list_template']) && file_exists($_SESSION['rs_list_template'])) {
			$this->xsl_template = $_SESSION['rs_list_template'];
		}
		else if (isset($_SESSION['frame_path'])) {
			$this->xsl_template = $_SESSION['frame_path'] . '/default_templates/rs_list.xsl';
		}
	}

	//*************************************************************************
	/**
	* Display Error Function
	* @param string Function name where error occurred
	* @param string Error message
	**/
	//*************************************************************************
    protected function display_error($function, $error_msg)
    {
    	$tmp_msg = 'Class [' . __CLASS__ . "]::{$function}() - ";
    	$tmp_msg .= "Error: {$error_msg}";
    	trigger_error($tmp_msg);
    }

	//****************************************************************************
	/**
	* Set XSL template function
	* @param string The file path to the XSL template
	**/
	//****************************************************************************
	public function set_xsl($stylesheet)
	{
		if (file_exists($stylesheet)) {
			$this->xsl_template = $stylesheet;
			$this->use_default_template = false;
			return true;
		}
		return false;
	}

	//****************************************************************************
	/**
	* Set the label to be used for the list
	* @param string List Label
	**/
	//****************************************************************************
	public function label($label) { $this->label = (string)$label; }

	//****************************************************************************
	/**
	* Set the CSS id and class of the table
	* @param string CSS id of the table
	* @param string CSS class of the table
	**/
	//****************************************************************************
	public function identify($id=false, $class=false)
	{
		if (!empty($id)) { $this->table_id = $id; }
		if (!empty($class)) { $this->table_class = $class; }
	}

	//****************************************************************************
	/**
	* Turn the display headers property on/off
	* @param bool Turn the display headers property on/off
	**/
	//****************************************************************************
	public function display_headers($bool)
	{
		if ($bool) { $this->display_col_headers = true; }
		else { $this->display_col_headers = false; }
	}

	//****************************************************************************
	/**
	* Set Multi-part Cell Separator
	* @param string Separator String
	**/
	//****************************************************************************
	public function set_separator($string) { $this->separator = (string)$string; }

	//***************************************************************************************
	/**
	* Turn off xsl transformation
	**/
	//***************************************************************************************	
	public function no_xsl() { $this->xsl_template = ''; }
	
	//***************************************************************************************
	/**
	* Set Empty Message
	**/
	//***************************************************************************************	
	public function empty_message($msg) { $this->empty_msg = (string)$msg; }
	
	//***************************************************************************************
	/**
	* Set Group By
	**/
	//***************************************************************************************	
	public function group_by($col, $col_vals='')
	{
		$first_index = ($this->rs_keys) ? ($this->rs_keys[0]) : (0);
		if (isset($this->record_set[$first_index]) && array_key_exists($col, $this->record_set[$first_index])) {
			$this->group_by = $col;
		}
		else if (count($this->record_set) > 0) {
			$this->display_error(__FUNCTION__, "'{$col}' is not a vaild column in the current record set!");
		}
		if (gettype($col_vals) == 'array') { $this->group_by_vals = $col_vals; }
	}
	
	//****************************************************************************
	/**
	* Escape Cell Data with XML "CDATA"
	* @param bool Escape = True (Default), Do Not Escape = False
	**/
	//****************************************************************************
	public function escape_data($bool) { $this->escape_data = ($bool) ? (true) : (false); }

	//****************************************************************************
	/**
	* Set a row attribute
	* @param mixed Row Key
	* @param string Attribute
	* @param mixed Value
	**/
	//****************************************************************************
	public function set_row_attr($row, $attr, $val, $overwrite=false)
	{
		if (!empty($attr)) {

			//---------------------------------------------------
			// Escape Attribute Value
			//---------------------------------------------------
			$val = htmlentities($val);

			if (isset($this->rs_attrs[$row]['attributes'][$attr]) && !$overwrite) {
				$this->rs_attrs[$row]['attributes'][$attr] .= ' ' . $val;
			}
			else {
				$this->rs_attrs[$row]['attributes'][$attr] = $val;
			}
		}
		else {
			$this->display_error(__FUNCTION__, 'Empty Attribute!');
		}
	}

	//****************************************************************************	
	/**
	* Set a cell attribute
	* @param mixed Row Key
	* @param mixed Cell Key
	* @param string Attribute
	* @param mixed Value
	**/
	//****************************************************************************
	public function set_cell_attr($row, $cell, $attr, $val, $overwrite=false)
	{
		if (!empty($attr)) {

			//---------------------------------------------------
			// Escape Attribute Value
			//---------------------------------------------------
			$val = htmlentities($val);

			if (isset($this->rs_attrs[$row]['cells'][$cell]['attributes'][$attr]) && !$overwrite) {
				$this->rs_attrs[$row]['cells'][$cell]['attributes'][$attr] .= ' ' . $val;
			}
			else {
				$this->rs_attrs[$row]['cells'][$cell]['attributes'][$attr] = $val;
			}
		}
		else {
			$this->display_error(__FUNCTION__, 'Empty Attribute!');
		}
	}

	//****************************************************************************
	/**
	* Set a column attribute
	* @param mixed Column Key
	* @param string Attribute
	* @param mixed Value
	**/
	//****************************************************************************
	public function set_col_attr($col, $attr, $val, $overwrite=false, $add_to_header=false)
	{
		$first_index = ($this->rs_keys) ? ($this->rs_keys[0]) : (0);
		if (!empty($attr)) {
			if (isset($this->record_set[$first_index][$col])) {

				//---------------------------------------------------
				// Escape Attribute Value
				//---------------------------------------------------
				$val = htmlentities($val);

				//---------------------------------------------------
				// Add to Header?
				//---------------------------------------------------
				if ($add_to_header) {
					$this->set_header_attr($col, $attr, $val, $overwrite);
				}

				//---------------------------------------------------
				// Add Attribute
				//---------------------------------------------------
				if (isset($this->rs_attrs['col_attrs'][$col][$attr]) && !$overwrite) {
					$this->rs_attrs['col_attrs'][$col][$attr] .= ' ' . $val;
				}
				//---------------------------------------------------
				// Overwrite Attribute
				//---------------------------------------------------
				else {
					$this->rs_attrs['col_attrs'][$col][$attr] = $val;
				}
			}
			else if (count($this->record_set) > 0) {
				$this->display_error(__FUNCTION__, "'{$col}' is not a vaild column in the current record set!");
			}
		}
		else {
			$this->display_error(__FUNCTION__, 'Empty Attribute!');
		}
	}

	//****************************************************************************
	/**
	* Set a header column attribute
	* @param mixed Column Key
	* @param string Attribute
	* @param mixed Value
	**/
	//****************************************************************************
	public function set_header_attr($col, $attr, $val, $overwrite=false)
	{
		$first_index = ($this->rs_keys) ? ($this->rs_keys[0]) : (0);
		if (!empty($attr)) {
			if (isset($this->record_set[$first_index][$col])) {

				//---------------------------------------------------
				// Escape Attribute Value
				//---------------------------------------------------
				$val = htmlentities($val);

				if (isset($this->rs_attrs['header_attrs'][$col][$attr]) && !$overwrite) {
					$this->rs_attrs['header_attrs'][$col][$attr] .= ' ' . $val;
				}
				else {
					$this->rs_attrs['header_attrs'][$col][$attr] = $val;
				}
			}
			else if (count($this->record_set) > 0) {
				$this->display_error(__FUNCTION__, "'{$col}' is not a vaild column in the current record set!");
			}
		}
		else {
			$this->display_error(__FUNCTION__, 'Empty Attribute!');
		}
	}

	//****************************************************************************
	/**
	* Render the rs_list
	**/
	//****************************************************************************
	public function render($buffer=false)
	{
		//---------------------------------------------------
		// Buffer?
		//---------------------------------------------------
		if ((bool)$buffer) { ob_start(); }

		//---------------------------------------------------
		// Data Order
		//---------------------------------------------------
		$order = array();
		foreach ($this->data_outline as $key => $val) {
			array_push($order, $key);
		}

		//---------------------------------------------------
		// Start Table Buffering if NOT Grouping
		//---------------------------------------------------
		ob_start();
		if (!$this->group_by) {
			$table_label = $this->label;
			ob_start();
		}

		//---------------------------------------------------
		// Loop Through Rows
		//---------------------------------------------------
		$row_count = 0;
		$gbv = NULL;
		foreach ($this->record_set as $row_key => $row) {

			//---------------------------------------------------
            // Set row_key to string
			//---------------------------------------------------
            settype($row_key, 'string');

			//---------------------------------------------------
            // Start Group By Table (if group_by being used)
			//---------------------------------------------------
            if ($this->group_by && isset($row[$this->group_by]) && $gbv !== $row[$this->group_by]) {

				//---------------------------------------------------
        		// End Current Table
				//---------------------------------------------------
        		if (!is_null($gbv)) {
					print $this->table(ob_get_clean(), $table_label, $row_count);
        		}

				//---------------------------------------------------
        		// Start New Table
				//---------------------------------------------------
				ob_start();
        		$table_label = isset($this->group_by_vals[$row[$this->group_by]]) ? ($this->group_by_vals[$row[$this->group_by]]) : ($row[$this->group_by]);
        		$row_count = 0;
        		$gbv = $row[$this->group_by];
            }

			//---------------------------------------------------
			// Start Row Content
			//---------------------------------------------------
			ob_start();

			//---------------------------------------------------
			// Loop through Row Cells
			//---------------------------------------------------
			$cell_count = 0;
			foreach ($order as $key => $val) {

				//---------------------------------------------------
				// Print Cell
				//---------------------------------------------------
				print $this->body_cell($row_key, $val, $row[$val]);
				$cell_count++;
			}

			//---------------------------------------------------
			// Print Row
			//---------------------------------------------------
			print $this->body_row($row_key, $row_count, ob_get_clean());
			$row_count++;
		}
		
		//---------------------------------------------------
		// End Table
		//---------------------------------------------------
		print $this->table(ob_get_clean(), $table_label, $row_count);
		$this->xml = ob_get_clean();

		//---------------------------------------------------
		// Table Group
		//---------------------------------------------------
		if ($this->group_by) { $this->xml = xhe('table_group', $this->xml); }

		//---------------------------------------------------
		// Perform XML Transformation
		//---------------------------------------------------
		$sxoe = (!empty($_SESSION['show_xml_on_error'])) ? (true) : (false);
		xml_transform($this->xml, $this->xsl_template, $sxoe);

		//---------------------------------------------------
		// Buffer? Return Content
		//---------------------------------------------------
		if ($buffer) { return ob_get_clean(); }
	}

	//***************************************************************************************
	/**
	* Start Table Function
	**/
	//***************************************************************************************	
	protected function table($table_content, $label, $row_count)
	{
		ob_start();

		//---------------------------------------------------
		// Attributes
		//---------------------------------------------------
		$attrs = array();
		if (isset($this->table_id) && !$this->group_by) { $attrs['id'] = $this->table_id; }
		if (isset($this->table_class)) { $attrs['class'] = $this->table_class; }

		//---------------------------------------------------
		// Label
		//---------------------------------------------------
		if (!empty($label)) {
			if ($this->escape_data) {
				print xhe('label', xml_escape($label));
			}
			else {
				print xhe('label', htmlentities($label));
			}
		}

		//---------------------------------------------------
		// Header
		//---------------------------------------------------
		if ($this->display_col_headers) {
			ob_start();
			ob_start();
			foreach ($this->data_outline as $key => $val) {
				print $this->header_cell($key, $val);
			}
			print xhe('row', ob_get_clean());
			print xhe('header', ob_get_clean());
		}
		
		//---------------------------------------------------
		// Content
		//---------------------------------------------------
		ob_start();
		if ($row_count == 0) {
			$ecols = count($this->data_outline);
			print xhe('row', xhe('cell', xml_escape($this->empty_msg), array('colspan' => $ecols)));
		}
		else {
			print $table_content;
		}
		print xhe('content', ob_get_clean());

		//---------------------------------------------------
		// Row Count
		//---------------------------------------------------
		print xhe('count', $row_count);

		//---------------------------------------------------
		// Return Table
		//---------------------------------------------------
		return xhe('table', ob_get_clean(), $attrs);
	}
	
	//***************************************************************************************
	/**
	* Body Row Function
	* @param mixed Row Key
	* @param mixed Row Count
	* @param mixed Row Content
	**/
	//***************************************************************************************	
	protected function body_row($row_key, $row_count, $content)
	{
		$attrs = array();

		//---------------------------------------------------
		// Alt Row
		//---------------------------------------------------
		if ($row_count % 2 == 1) { $this->set_row_attr($row_key, 'class', 'alt'); }

		//---------------------------------------------------
		// Set Row Attributes
		//---------------------------------------------------
		if (isset($this->rs_attrs[$row_key]['attributes'])) {
			foreach ($this->rs_attrs[$row_key]['attributes'] as $attr => $val) {
				$attrs[$attr] = $val;
			}
		}

		//---------------------------------------------------
		// Return "row" element
		//---------------------------------------------------
		return xhe('row', $content, $attrs);
	}
	
	//***************************************************************************************
	/**
	* Body Cell Function
	* @param mixed Row Key
	* @param mixed Cell Key
	* @param mixed Cell Content
	**/
	//***************************************************************************************	
	protected function body_cell($row_key, $cell, $content)
	{
		$attrs = array();

		//---------------------------------------------------
		// Check for global Column Attributes
		//---------------------------------------------------
		if (isset($this->rs_attrs['col_attrs'][$cell])) {
			foreach ($this->rs_attrs['col_attrs'][$cell] as $col_attr => $col_val) {
				if (isset($this->rs_attrs[$row_key]['cells'][$cell]['attributes'][$col_attr])) {
					$this->rs_attrs[$row_key]['cells'][$cell]['attributes'][$col_attr] .= ' ' . $col_val;
				}
				else {
					$this->rs_attrs[$row_key]['cells'][$cell]['attributes'][$col_attr] = $col_val;
				}
			}
		}

		//---------------------------------------------------
		// Set Cell Attributes
		//---------------------------------------------------
		if (isset($this->rs_attrs[$row_key]['cells'][$cell])) {
			foreach ($this->rs_attrs[$row_key]['cells'][$cell]['attributes'] as $attr_key => $attr_val) {
				$attrs[$attr_key] = $attr_val;
			}
		}

		//---------------------------------------------------
		// Return "cell" element
		//---------------------------------------------------
		if ($this->escape_data) {
			return xhe('cell', xml_escape($content), $attrs);
		}
		else {
			return xhe('cell', htmlentities($content), $attrs);
		}
	}

	//***************************************************************************************
	/**
	* Header Cell Function
	* @param mixed Cell Key
	* @param mixed Cell Content
	**/
	//***************************************************************************************	
	protected function header_cell($cell, $content)
	{
		$attrs = array();

		//---------------------------------------------------
		// Check for global Column Attributes
		//---------------------------------------------------
		if (isset($this->rs_attrs['header_attrs'][$cell])) {
			$attrs = $this->rs_attrs['header_attrs'][$cell];
		}

		//---------------------------------------------------
		// Return "cell" element
		//---------------------------------------------------
		if ($this->escape_data) {
			return xhe('cell', xml_escape($content), $attrs);
		}
		else {
			return xhe('cell', htmlentities($content), $attrs);
		}
	}
	
}

?>