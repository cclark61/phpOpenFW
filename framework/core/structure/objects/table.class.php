<?php
/**
* A class to construct XHTML tables (Requires XSLT Support)
*
* @package		phpOpenFW
* @subpackage	Objects
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 4-21-2008 Updated: 3-24-2010
**/

//***************************************************************
/**
 * (XHTML) Table Class
 * @package		phpOpenFW
 * @subpackage	Objects
 */
//***************************************************************
class table extends element
{
	//*************************************************************************
	// Table class variables
	//*************************************************************************
		
	/**
	* @var array An array of all of the data for this table
	**/
	private $table_data;		// Array of table data
	
	/**
	* @var array An array of elements in the table
	**/
	private $table_elements;		// Array of fields in the table
	
	/**
	* @var integer number of columns in the table
	**/
	private $columns;				// Number of columns in the table

	/**
	* @var Flag: true = put "alt" class in row element, false = don't
	**/	
	private $alt_rows;

	//*************************************************************************
	/**
	* Table constructor function
	**/
	//*************************************************************************
	public function __construct()
	{
		// Class Data
		$this->attributes = array();
		$this->element = 'table';
		
		// Table Data
		$this->table_data = array();
		$this->table_data['elements'] = array();
		$this->table_data['elements']['header'] = array();
		$this->table_data['elements']['body'] = array();
		$this->table_data['elements']['footer'] = array();
		$this->table_data['columns'] = 2;
		$this->table_data['alt_rows'] = 0;
		
		// Temporary Elements
		$this->table_elements = array();
		$this->table_elements['header'] = array();
		$this->table_elements['body'] = array();
		$this->table_elements['footer'] = array();
		
		// Default Template
		if (isset($_SESSION['frame_path'])) {
			$this->xsl_template = $_SESSION['frame_path'] . '/default_templates/table.xsl';
		}
	}
	
	/**
	* Table render function
	**/
	//*************************************************************************
	// Construct and output the table we have gathered info about.
	//*************************************************************************
	public function render($buffer=false)
	{
		// Build Element Structure
		$this->build_element_structure($this->table_elements['header'], $this->table_data['elements']['header']);
		$this->build_element_structure($this->table_elements['body'], $this->table_data['elements']['body']);
		$this->build_element_structure($this->table_elements['footer'], $this->table_data['elements']['footer']);
		
		// Convert Table Data to XML
		$this->inset_val = array2xml('table_data', $this->table_data);

		// Render Table Element
		parent::render($buffer);
	}
	
	//*************************************************************************
	// Build Elements Array Function
	//*************************************************************************
	private function build_element_structure(&$source, &$destination)
	{
		$curr_row = 0;
		$curr_col = 0;
		foreach ($source as $te) {
			$row_name = 'row_' . $curr_row;
			$col_name = 'cell_' . $curr_col; 
			$destination[$row_name][$col_name] = $te;
			
			// Increment / Calculate Columns and Rows
			$curr_col += $te['cols'];
			if ($curr_col >= $this->table_data['columns']) {
				$curr_row++;
				$curr_col = 0;
			}
		}
	}
	
	//*************************************************************************
	// Variable Setting Functions
	//*************************************************************************

	//*************************************************************************
	/**
	* Set the number of columns in the table
	* @param integer Number of columns in the table
	**/
	//*************************************************************************
	public function set_columns($cols) {
		$this->columns = $cols + 0;
		$this->table_data['columns'] = $cols + 0;
	}
	
	//*************************************************************************
	/**
	* Cell adding functions
	* @param mixed New Table element
	* @param integer Number of columns to span
	* @param string Location (header, body, footer)
	* @param string Type (header_cell, data_cell)
	**/
	//*************************************************************************
	public function td_header($table_element, $num_cols=1, $attrs=null) { $this->add_element($table_element, $num_cols, $attrs, 'header'); }
	public function th_header($table_element, $num_cols=1, $attrs=null) { $this->add_element($table_element, $num_cols, $attrs, 'header', 'header_cell'); }
	public function td($table_element, $num_cols=1, $attrs=null) { $this->add_element($table_element, $num_cols, $attrs); }
	public function th($table_element, $num_cols=1, $attrs=null) { $this->add_element($table_element, $num_cols, $attrs, 'body', 'header_cell'); }
	public function td_footer($table_element, $num_cols=1, $attrs=null) { $this->add_element($table_element, $num_cols, $attrs, 'footer'); }
	public function th_footer($table_element, $num_cols=1, $attrs=null) { $this->add_element($table_element, $num_cols, $attrs, 'footer', 'header_cell'); }

	//*************************************************************************
	/**
	* Add a element to the table
	* @param mixed New Table element
	* @param integer Number of columns to span
	**/
	//*************************************************************************
	private function add_element($table_element, $num_cols=1, $attrs=null, $location='body', $type='data_cell')
	{
		// Process Element
		$processed_content = $this->process_element($table_element);

		// Build Element Array
		$tmp_element = array('content' => $processed_content, 'cols' => $num_cols, 'type' => $type);
		
		// Element Attributes
		if (!is_null($attrs) && is_array($attrs)) {
			$new_attrs = array();
			foreach ($attrs as $key => $val) {
				if (!is_numeric($key)) { $new_attrs[$key] = $val; }
			}
			if (count($new_attrs) > 0) { $tmp_element['attrs'] = $new_attrs; } 
		}

		// Push Element onto Array of Elements
		array_push($this->table_elements[$location], $tmp_element);
	}

	//*************************************************************************
	/**
	* Process the current table element passed (string, object, array)
	* @param mixed The table element to process
	**/
	//*************************************************************************
	private function process_element($element)
	{
		$return_content = '';
		switch (gettype($element)) {				
			case 'array':
				foreach ($element as $sub_element) {
					$return_content .= $this->process_element($sub_element);
				}
				break;

			case 'object':
			default:
				$return_content = $element;
				break;
		}

		// Return Processed Element
		if (!empty($this->xsl_template)) { return $processed_content = xml_escape($return_content); }
		else { return $return_content; }
	}
	
	//*************************************************************************
	/**
	* Set Table Caption
	* @param string Table Caption
	**/
	//*************************************************************************
	public function caption($caption) { $this->table_data['caption'] = xml_escape($caption); }
	
	//*************************************************************************
	/**
	* Set Table Fieldset
	* @param string Table Fieldset
	**/
	//*************************************************************************
	public function fieldset($legend='', $id='', $class='')
	{ 
		$this->table_data['fieldset']['legend'] = xml_escape($legend);
		if (!empty($id)) { $this->table_data['fieldset']['id'] = $id; }
		if (!empty($class)) { $this->table_data['fieldset']['class'] = $class; }
	}	

	//*************************************************************************
	/**
	* Set alternating row class "alt"
	**/
	//*************************************************************************
	public function set_alt_rows() { $this->table_data['alt_rows'] = 1; }

	//*************************************************************************
	/**
	* Turn off xsl transformation
	**/
	//*************************************************************************
	public function no_xsl() { $this->xsl_template = ''; }

}

?>
