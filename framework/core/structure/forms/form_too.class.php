<?php
//**************************************************************************
//**************************************************************************
/**
* A non-table based form class to construct HTML forms styled via CSS
*
* @package		phpOpenFW
* @subpackage	Form_Engine
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 12-9-2012 Updated: 3-6-2013
**/
//**************************************************************************
//**************************************************************************

//**************************************************************************
/**
 * Form Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class form_too extends element
{
	//*************************************************************************
	// Form class variables
	//*************************************************************************

	/**
	* @var string The label of the form being created
	**/
	protected $form_label;
	
	/**
	* @var array An array of elements in the form
	**/
	protected $form_elements;
	
	/**
	* @var array An array of hidden elements in the form
	**/
	protected $hidden_elements;

	//************************************************************************
	//*************************************************************************
	/**
	* Form constructor function
	* @param string Action of the form (Where it's going)
	**/
	//*************************************************************************
	//************************************************************************
	public function __construct($form_action=false, $form_method=false)
	{
		//-------------------------------------------------
		// Class Defaults
		//-------------------------------------------------
		$this->attributes = array();
		$this->display_tree();
		$this->element = 'form';
		if ($form_action !== false) {
			$this->attributes['action'] = $form_action;
			$this->attributes['method'] = 'post';
		}
		if ($form_method !== false) {
			$this->attributes['method'] = $form_method;
		}
		$this->form_elements = array();
		$this->hidden_elements = array();
		
		//-------------------------------------------------
		// Default Form Template
		//-------------------------------------------------		
		if (isset($_SESSION['frame_path'])) {
			$default_template = $_SESSION['frame_path'] . '/default_templates/form_too.xsl';
			if (file_exists($default_template)) {
				$this->xsl_template = $default_template;
			}
		}
	}
	
	//************************************************************************
	//************************************************************************
	/**
	* Form render function
	**/
	//************************************************************************
	//************************************************************************
	public function render($buffer=false)
	{
		//============================================================
		// Form Label
		//============================================================
		if (isset($this->form_label)) {
			 $this->add_child(xhe('form_label', $this->form_label));
		}

		//============================================================
		// Hidden Form Elements
		//============================================================
		$hid_elements = new gen_element('hidden_elements');
		$hid_elements->display_tree();

		foreach ($this->hidden_elements as $hid_element) {
			ob_start();
			$this->process_element($hid_element);
			$tmp_element = (!empty($this->xsl_template)) ? ($this->xml_escape(ob_get_clean())) : (ob_get_clean());
			$tmp_element = trim($tmp_element);
			$tmp_he = new gen_element('hid_element', $tmp_element);
			$tmp_he->display_tree();
			$hid_elements->add_child($tmp_he);
		}
		$this->add_child($hid_elements);

		//============================================================
		// Process Visible Form Elements
		//============================================================
		$elements = new gen_element('form_elements');
		$elements->display_tree();

		foreach ($this->form_elements as $element) {

			//-----------------------------------------------------
			// Validate Each Element
			//-----------------------------------------------------
			if (!is_array($element) || (is_array($element) && count($element) < 2)) { continue; }

			//-----------------------------------------------------
			// Process the element 
			//-----------------------------------------------------
			ob_start();
			$this->process_element($element[0]);
			$tmp_element = ob_get_clean();

			//-----------------------------------------------------
			// Build Form Element into XML and add it to Tree
			//-----------------------------------------------------
			$fe_attrs = array();
			$fe_content = trim($tmp_element);
			$content_empty = ($fe_content == '') ? (true) : (false);
			if ($fe_content != '' && !empty($this->xsl_template)) {
				$fe_content = xml_escape($fe_content);
			}
			$fe_attrs = (isset($element[2]) && is_array($element[2])) ? ($element[2]) : (array());
			$tmp_fe = new gen_element($element[1], $fe_content, $fe_attrs);
			if (substr($element[1], 0, 4) != 'end_' && !$content_empty) {
				$tmp_fe->display_tree();
			}
			$elements->add_child($tmp_fe);
		}
		$this->add_child($elements);

		//============================================================
		// Call Parent Render Function
		//============================================================
		return parent::render($buffer);
	}

	//************************************************************************
	//*************************************************************************
	/**
	* Process the current form element passed (string, object, array)
	* @param mixed The form element to process
	**/
	//*************************************************************************
	//************************************************************************
	protected function process_element($element)
	{
		switch (gettype($element)) {
			case 'object':
				if (method_exists($element, 'render')) {
					$element->render();
				}
				break;

			case 'array':
				foreach ($element as $sub_element) {
					$this->process_element($sub_element);
				}
				break;

			default:
				print "{$element}\n";
				break;
		}
	}

	//************************************************************************
	//*************************************************************************
	// Variable Setting Functions
	//*************************************************************************
	//************************************************************************

	//*************************************************************************
	/**
	* Add a field to the form
	* @param string New Form element
	* @param string Containing XHTML Element
	* @param array Attributes of containing XHTML Element
	**/
	//*************************************************************************
	public function add_element($form_element, $container=false, $attrs=false)
	{
		if (is_array($form_element)) {
			$tmp = '';
			foreach ($form_element as $fe) { $tmp .= trim($fe); }
			$form_element = $tmp;
		}
		$form_element = trim($form_element);
		if (!is_array($attrs)) { $attrs = array(); }
		if ($container) {
			$form_element = trim(xhe($container, $form_element, $attrs));
		}
		array_push($this->form_elements, array($form_element, 'element'));
	}

	//*************************************************************************
	/**
	* Add a hidden field to the form
	* @param mixed New hidden form element
	**/
	//*************************************************************************
	public function add_hidden($name, $value='', $attrs=false)
	{
		if (!is_array($attrs)) { $attrs = array(); }
		$attrs['type'] = 'hidden';
		$attrs['name'] = (string)$name;
		$attrs['value'] = (string)$value;
		$this->hidden_elements[] = trim(xhe('input', false, $attrs));
	}

	//*************************************************************************
	/**
	* Add a label field to the form
	* @param string Label Caption
	* @param array Label Attributes
	* @param string Containing element name
	* @param string Containing element attributes
	**/
	//*************************************************************************
	public function add_label($caption, $attrs=false, $container=false, $attrs2=false)
	{
		$caption = (string)$caption;
		if (!is_array($attrs)) { $attrs = array(); }
		if (!is_array($attrs2)) { $attrs2 = array(); }
		$label = xhe('label', trim($caption), $attrs);
		if ($container) {
			$label = trim(xhe($container, $label, $attrs2));
		}
		array_push($this->form_elements, array(trim($label), 'element'));
	}	

	//*************************************************************************
	/**
	* Start a fieldset in the form
	* @param string The legend to insert after the start of the fieldset
	* @param array Fieldset Attributes
	* @param array Legend Attributes
	**/
	//*************************************************************************
	public function start_fieldset($legend='', $fs_attrs=false, $l_attrs=false)
	{
		$fs_content = '';
		if (!is_array($fs_attrs)) { $fs_attrs = array(); }
		if ($legend != '') {
			if (!is_array($l_attrs)) { $l_attrs = array(); }
			$fs_content = trim(xhe('legend', $legend, $l_attrs));
		}
		
		$this->start_section('fieldset', $fs_attrs, $fs_content);
	}

	//*************************************************************************
	/**
	* End a fieldset in the form
	**/
	//*************************************************************************
	public function end_fieldset()
	{
		$this->end_section('fieldset');
	}

	//*************************************************************************
	/**
	* Start a Div in the form
	* @param array Div Attributes
	* @param array Pre-defined div content
	**/
	//*************************************************************************
	public function start_div($attrs=false, $content=false)
	{
		if (!is_array($attrs)) { $attrs = array(); }
		$this->start_section('div', $attrs, $content);
	}

	//*************************************************************************
	/**
	* End a div in the form
	**/
	//*************************************************************************
	public function end_div()
	{
		$this->end_section('div');
	}

	//*************************************************************************
	/**
	* Start a section in the form
	**/
	//*************************************************************************
	public function start_section($tag, $attrs=false, $content=false)
	{
		if (!is_array($attrs)) { $attrs = array(); }
		$attrs['tag'] = (string)$tag;
		$this->form_elements[] = array(
			trim((string)$content),
			'start_section',
			$attrs
		);
	}

	//*************************************************************************
	/**
	* End a section in the form
	**/
	//*************************************************************************
	public function end_section($tag)
	{
		$this->form_elements[] = array(
			false,
			'end_section',
			array('tag' => (string)$tag)
		);
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
		$this->add_hidden($key, $stamp);
		return $stamp;
	}

}

?>
