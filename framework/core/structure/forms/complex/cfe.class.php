<?php
//**************************************************************************
//**************************************************************************
/**
* An abstract core classes for constructing complex form elements
*
* @package		phpOpenFW
* @subpackage	Form_Engine
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 11-20-2012 Updated: 12-28-2012
**/
//**************************************************************************
//**************************************************************************

//**************************************************************************
/**
 * Select Form Element Class (Abstract)
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
abstract class select_form_element extends element
{
	/**
	* @var array An array of arrays. The attributes for elements by key.
	**/
	protected $elements_attrs;

	/**
	* @var mixed The selected value.
	**/
	protected $select_value;

	/**
	* @var Array An array of values to be prepended to the list of added items.
	**/
	protected $blank;

	//*************************************************************************
	// String Conversion Function
	//*************************************************************************
	public function __toString()
	{
		ob_start();
		$this->render();
		return ob_get_clean();
	}

	//*************************************************************************
	// Set the selected value
	//*************************************************************************
	public function selected_value($value)
	{
		$this->select_value = (string)$value;
	}
	
	//*************************************************************************
	// Add a Blank or Default Select Option
	//*************************************************************************
	public function add_blank($value='', $desc='')
	{
		$this->blank[] = array($value, $desc);
	}

	//*************************************************************************
	/**
	* Add Elements Attributes
	* @param array An array of arrays containing the key/value attributes, indexed by the key of the items.
	**/
	//*************************************************************************
	// Add Elements Attributes
	//*************************************************************************
	public function elements_attrs($elem_attrs)
	{
		if (!is_array($elem_attrs)) {
			trigger_error(__FUNCTION__ . ': Non-array value passed.');
			return false;
		}
		$this->elements_attrs = $elem_attrs;
		return true;
	}

}

//**************************************************************************
/**
 * Group Form Element Class (Abstract)
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
abstract class group_form_element
{
	/**
	* @var array An array of arrays. The attributes for elements by key.
	**/
	protected $elements_attrs;

	/**
	* @var mixed Checked item or items.
	**/
	protected $checked_value;

	/**
	* @var string The display style for the group of items (inline, newline (default))
	**/
	protected $style;

	/**
	* @var string The Custom Style to be used to separate each element
	**/
	protected $custom_style;

	//*************************************************************************
	// String Conversion Function
	//*************************************************************************
	public function __toString()
	{
		ob_start();
		$this->render();
		return ob_get_clean();
	}

	//*************************************************************************
	/**
	* Set the value of the checked item
	* @param mixed The value of the checked item
	**/
	//*************************************************************************
	// Set the checked value
	//*************************************************************************
	public function checked_value($value)
	{
		$this->checked_value = $value;
	}

	//*************************************************************************
	/**
	* Set the value of the checked item
	* @param mixed The value of the checked item
	**/
	//*************************************************************************
	// Set the checked value
	//*************************************************************************
	public function checked($checked)
	{
		$this->checked_value = $checked;
	}
	
	//*************************************************************************
	/**
	* Set the display style of item group
	* @param string The display style of the item group (inline, newline (default))
	**/
	//*************************************************************************
	// Set the style
	//*************************************************************************
	public function style($style, $custom_format=false)
	{
		$style = strtolower($style);
		if ($style == 'inline' || $style == 'newline' || $style == 'custom') {
			$this->style = $style;
			if ($style == 'custom') {
				$this->custom_style = $custom_format;
			}
		}
	}

	//*************************************************************************
	/**
	* Add Elements Attributes
	* @param array An array of arrays containing the key/value attributes, indexed by the key of the items.
	**/
	//*************************************************************************
	// Add Elements Attributes
	//*************************************************************************
	public function elements_attrs($elem_attrs)
	{
		if (!is_array($elem_attrs)) {
			trigger_error(__FUNCTION__ . ': Non-array value passed.');
			return false;
		}
		$this->elements_attrs = $elem_attrs;
		return true;
	}

}

?>
