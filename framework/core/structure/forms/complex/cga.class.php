<?php
//**************************************************************************
//**************************************************************************
/**
* A class for constructing a Checkbox Group from Array (CGA)
*
* @package		phpOpenFW
* @subpackage	Form_Engine
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 9-22-2005 Updated: 12-28-2012
**/
//**************************************************************************
//**************************************************************************

//**************************************************************************
/**
 * Checkbox Group from Array Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class cga extends group_form_element
{
	private $checkboxes;	// Array of Array([Name], [Value], [Desc])
	private $attrs;			// Attributes of the checkboxes
	
	//*************************************************************************
	// Constructor Function
	//*************************************************************************
	public function __construct($checkboxes)
	{
		$this->checkboxes = $checkboxes;
		$this->style = 'newline';
	}

	//*************************************************************************
	// Set the checked values
	//*************************************************************************
	public function checked_value($checked)
	{
		if (is_array($checked)) { $this->checked_value = $checked; }
	}

	//*************************************************************************
	// Set the checked values
	//*************************************************************************
	public function checked($checked)
	{
		if (is_array($checked)) { $this->checked_value = $checked; }
	}

	//*************************************************************************
	// Construct and output the CGA.
	//*************************************************************************
	public function render()
	{
		foreach ($this->checkboxes as $checkbox) {
			$is_checked = false;

			//-----------------------------------------
			// Is Checked?
			//-----------------------------------------
			if (isset($this->checked_value[$checkbox[0]]) && $this->checked_value[$checkbox[0]] == 1) { $is_checked = true; }

			//-----------------------------------------
			// Create Checkbox
			//-----------------------------------------
			$c = new checkbox($checkbox[0], $checkbox[1], $is_checked);

			//-----------------------------------------
			// Element Attributes
			//-----------------------------------------
			if (isset($this->elements_attrs[$checkbox[1]])) {
				$c->attrs($this->elements_attrs[$checkbox[1]]);
			}

			//-----------------------------------------
			// Output
			//-----------------------------------------
			print $c;
			if (isset($checkbox[2])) { print '&nbsp;' . $checkbox[2]; }
			if ($this->style == 'newline') { print '<br/>'; }
			else if ($this->style == 'custom') { print $this->custom_style; }
		}			
	}	

}

