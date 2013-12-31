<?php
/**
* A simple form class to construct XHTML forms
*
* @package		phpOpenFW
* @subpackage	Form_Engine
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 10-6-2004 Updated: 3-6-2013
**/

//**************************************************************************
/**
 * Form Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class form extends element
{
	//*************************************************************************
	// Form class variables
	//*************************************************************************
	/**
	* @var string The label of the form being created
	**/
	protected $form_label;
	
	/**
	* @var string The action of the current page
	**/
	protected $page_action;
	
	/**
	* @var string The text for the submit button
	**/
	protected $button;

	/**
	* @var array An array of elements in the form
	**/
	protected $form_elements;
	
	/**
	* @var array An array of hidden elements in the form
	**/
	protected $hidden_elements;
	
	/**
	* @var integer number of columns in the form
	**/
	protected $cols;

	/**
	* @var bool Flag: true = put "alt" class in row element, false = don't
	**/	
	protected $alt_rows;

	/**
	* @var array An Array of Headers to be used at the top of the form table
	**/	
	protected $headers;

	/**
	* @var array An Array, in the form of "Key" => "Value", of Button Cell Attributes
	**/	
	protected $button_cell_attrs;
	
	/**
	* @var array An Array, in the form of "Key" => "Value", of Row Attributes
	**/	
	protected $row_attrs;


	//*************************************************************************
	/**
	* Form constructor function
	* @param string Name of the form
	* @param string Action of the form (Where it's going)
	**/
	//*************************************************************************
	public function __construct($form_name, $form_action)
	{
		$this->attributes = array();
		$this->element = 'form';
		$this->attributes['name'] = $form_name;
		$this->attributes['action'] = $form_action;
		$this->attributes['method'] = 'post';
		$this->page_action = (isset($_GET['action'])) ? ($_GET['action']) : ('');
		$this->form_elements = array();
		$this->hidden_elements = array();
		$this->cols = 2;
		$this->button = 'Submit';
		$this->alt_rows = false;
		$this->headers = array();
		$this->button_cell_attrs = array();
		$this->row_attrs = array();
		
		// Default Template
		if (isset($_SESSION['frame_path'])) {
			$this->xsl_template = $_SESSION['frame_path'] . '/default_templates/form.xsl';
		}
	}
	
	/**
	* Form render function
	**/
	//************************************************************************
	// Construct and output the form we have gathered info about.
	//************************************************************************
	public function render($buffer=false)
	{
		// Inset Value Buffer
		ob_start();

		// Data Output Buffer
		ob_start();
		
		// Form Label
		if (isset($this->form_label)) {
			 print new gen_element('form_label', $this->form_label);
		}
		
		// Headers
		$headers_arr = array();
		for ($x = 0; $x < $this->cols; $x++) {
			if (isset($this->headers[$x])) { $headers_arr[] = $this->headers[$x]; }
		}
		print array2xml('headers', $headers_arr);
		
		// Button Cell Attributes
		print array2xml('button_cell_attrs', $this->button_cell_attrs);
		
		// Button(s)
		if (gettype($this->button) == 'array') {
			$buttons = array();
			foreach ($this->button as $key => $value) {
				if (gettype($key) == 'integer') { $key = 'button_' . $key; }
				$buttons[$key] = array('name' => $key, 'value' => $this->xml_escape($value));
			}
			print array2xml('buttons', $buttons);
		}
		else {
			if ($this->button !== NULL) {
				print new gen_element('button', $this->xml_escape($this->button));
			}
		}
		print new gen_element('columns', $this->cols);

		// Form content
		$curr_cols = 0;
		$colspan = 0;
		$row_begin = false;
		$row_end = false;

		// Hidden Form Elements
		$hid_elems = array();
		foreach ($this->hidden_elements as $he_key => $hid_element) {
			ob_start();
			$this->process_element($hid_element);
			$hid_elems[] = (!empty($this->xsl_template)) ? ($this->xml_escape(ob_get_clean())) : (ob_get_clean());
		}
		print array2xml('hidden_elements', $hid_elems);
		
		// Visible Form Elements
		ob_start();
		$rows = 0;
		foreach ($this->form_elements as $element) {
			// Process the element (object -> render, text -> print, array -> process elements)
			ob_start();
			$this->process_element($element[0]);
			$tmp_element = ob_get_clean();
			
			if ($element[2] == 'cell') {
				// Start ROW
				if ($curr_cols == 0) {
					ob_start();
					if ($rows % 2 == 1 && $this->alt_rows) { $this->set_row_attr($rows, 'class', 'alt'); }
					$row_attrs = (isset($this->row_attrs[$rows])) ? ($this->row_attrs[$rows]) : (array());
					$row_begin = true;
					$row_end = false;
					$rows++;
				}

				// Build Form Element
				$colspan = $element[1] + 0;
				$fe_attrs = array('colspan' => $element[1]);
				foreach ($element[3] as $fe_attr_key => $fe_attr_val) { $fe_attrs[$fe_attr_key] = $fe_attr_val; }
				$fe_content = (!empty($this->xsl_template)) ? ($this->xml_escape($tmp_element)) : ($tmp_element);
				print new gen_element('form_element', $fe_content, $fe_attrs);
				
				// End ROW
				if ($curr_cols + $colspan >= $this->cols) {
					print new gen_element('row', ob_get_clean(), $row_attrs);
					$row_begin = false;
					$row_end = true;
					$curr_cols = 0;
				}
				else { $curr_cols += $colspan; }
			}
			else {
				print (!empty($this->xsl_template) && $element[2] != 'fieldset') ? ($this->xml_escape($tmp_element)) : ($tmp_element);
			}
		}
		
		// End ROW if not already terminated
		if (!$row_end && $row_begin && count($this->form_elements) > 0) {
			print new gen_element('row', ob_get_clean(), $row_attrs);
		}		

		print new gen_element('elements', ob_get_clean());
		print new gen_element('data', ob_get_clean());
		$this->inset_val .= ob_get_clean();
		return parent::render($buffer);
	}

	//*************************************************************************
	// Variable Setting Functions
	//*************************************************************************

	//*************************************************************************
	/**
	* Set the number of columns in the form
	* @param integer Number of columns in the form table
	**/
	//*************************************************************************
	public function set_columns($cols) { $this->cols = $cols + 0; }

	//*************************************************************************
	/**
	* Set the submit button description at the bottom of the page
	* @param mixed Text or array strings to be used for the submit button(s)
	**/
	//*************************************************************************
	public function set_button($button) { $this->button = $button; }
	
	//*************************************************************************
	/**
	* Turn off the submit button from showing
	**/
	//*************************************************************************
	public function no_button() { $this->button = NULL; }

	//*************************************************************************
	/**
	* Set the attributes for the table cell containing the form button(s)
	* @param array A Key/Value array of attributes
	**/
	//*************************************************************************
	public function set_button_attrs($button_attrs) {
		if (is_array($button_attrs)) {
			$this->button_cell_attrs = $button_attrs;
		}
	}

	//*************************************************************************
	/**
	* Set the form table headers
	* @param array An array of values to be used as the headers for the form table
	**/
	//*************************************************************************
	public function set_headers($headers)
	{
		if (is_array($headers)) { $this->headers = $headers; }
	}

	//*************************************************************************
	/**
	* Set a row attribute
	* @param integer The row number to set the attribute on.
	* @param string The attribute name.
	* @param string The attribute value.
	**/
	//*************************************************************************
	public function set_row_attr($row, $attr_name, $attr_val)
	{
		$row = (int)$row;
		$attr_name = (string)$attr_name;
		$attr_val = (string)$attr_val;
		if ($attr_name == '' || $attr_val == '') { return false; }

		if (isset($this->row_attrs[$row][$attr_name])) {
			$this->row_attrs[$row][$attr_name] .= ' ' . $attr_val;
		}
		else {
			$this->row_attrs[$row][$attr_name] = $attr_val;
		}
		return true;
	}
	
	//*************************************************************************
	/**
	* Add a field to the form
	* @param mixed New Form element
	* @param integer Number of columns to span
	**/
	//*************************************************************************
	public function add_element($form_element, $num_cols=1, $attrs=false)
	{
		if (!is_array($attrs)) { $attrs = array(); }
		array_push($this->form_elements, array($form_element, $num_cols, 'cell', $attrs));
	}

	//*************************************************************************
	/**
	* Add a hidden field to the form
	* @param mixed New hidden form element
	**/
	//*************************************************************************
	public function add_hidden($hidden_element)
	{
		array_push($this->hidden_elements, $hidden_element);
	}
	
	//*************************************************************************
	/**
	* Add a text/html/markup to the form
	* @param mixed The Content to insert
	**/
	//*************************************************************************
	public function add_text($content)
	{
		array_push($this->form_elements, array($content, 0, 'text'));
	}

	//*************************************************************************
	/**
	* Add a label field to the form
	* @param string Label Caption
	* @param integer Number of columns to span
	* @param string "For" Attribute Value
	**/
	//*************************************************************************
	public function add_label($caption, $num_cols=1, $attrs=false, $attrs2=false)
	{
		if (!is_array($attrs)) { $attrs = array(); }
		if (!is_array($attrs2)) { $attrs2 = array(); }
		$obj_label = new gen_element('label', $caption, $attrs);
		$label = $obj_label->render(1);
		array_push($this->form_elements, array($label, $num_cols, 'cell', $attrs2));
	}

	//*************************************************************************
	/**
	* Process the current form element passed (string, object, array)
	* @param mixed The form element to process
	**/
	//*************************************************************************
	protected function process_element($element)
	{
		switch (gettype($element)) {
			case 'object':
				$element->render();
				break;
				
			case 'array':
				foreach ($element as $sub_element) {
					$this->process_element($sub_element);
				}
				break;
				
			default:
				print "$element\n";
				break;
		}
	}
	
	//*************************************************************************
	/**
	* Start a fieldset in the form
	* @param string The legend to insert after the start of the fieldset
	* @param string The id of the fieldset
	**/
	//*************************************************************************
	public function start_fieldset($content='', $id='', $class='')
	{
		$fs_content = '';
		$fs_attrs = array('marker' => 'start');
		if (!empty($id)) { $fs_attrs['id'] = $id;  }
		if (!empty($class)) { $fs_attrs['class'] = $class;  }
		if (!empty($content)) {
			$obj_legend = new gen_element('legend', $this->xml_escape($content));
			$fs_content = $obj_legend->render(1);
		}
		$fieldset = new gen_element('fieldset', $fs_content, $fs_attrs);
		array_push($this->form_elements, array($fieldset->render(1), 0, 'fieldset'));
	}
	
	//*************************************************************************
	/**
	* End a fieldset in the form
	**/
	//*************************************************************************
	public function end_fieldset()
	{
		$fs_attrs = array('marker' => 'end');
		$fieldset = new gen_element('fieldset', '', $fs_attrs);
		array_push($this->form_elements, array($fieldset->render(1), 0, 'fieldset'));
	}
	
	//*************************************************************************
	/**
	* Set Form Label
	* @param string Form Label
	**/
	//*************************************************************************
	public function label($label) { $this->form_label = $label; }	


	//*************************************************************************
	/**
	* Set alternating row class "alt"
	**/
	//*************************************************************************	
	public function set_alt_rows() { $this->alt_rows = true; }

	//*************************************************************************
	/**
	* Turn off xsl transformation
	**/
	//*************************************************************************	
	public function no_xsl() { $this->xsl_template = ''; }
	
	//*************************************************************************
	/**
	* Create and use a hidden unique form key 
	* @param string Key Name
	**/
	//*************************************************************************	
	public function use_key($key='form_key')
	{
		$stamp = date('U');
		$this->add_hidden(new hidden($key, $stamp));
		return $stamp;
	}

}

?>