<?php
//**************************************************************************
//**************************************************************************
/**
* A class for constructing Simple Selects from Array (SSA)
*
* @package		phpOpenFW
* @subpackage	Form_Engine
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 9-19-2005 Updated: 12-31-2013
**/
//**************************************************************************
//**************************************************************************

//***************************************************************
// Contributions by Lucas Hoezee ( http://thecodify.com/ )
// 5/10/2011
//***************************************************************

//**************************************************************************
/**
 * Simple Select from Array Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class ssa extends select_form_element
{
	private $select_vals;	// Values of the select
	
	//*************************************************************************
	// Constructor Function
	//*************************************************************************
	public function __construct($name, $val_arr)
	{
		$this->attributes = array();
		$this->element = 'select';
		$this->attributes['name'] = $name;
		$this->blank = array();
		$this->select_vals = $val_arr;
	}

	//*************************************************************************
	// Construct and output the SSA.
	//*************************************************************************
	public function render($buffer=false)
	{
		$this->inset_val = '';
		ob_start();

		if (!is_array($this->select_value)) {
			settype($this->select_value, 'string');
		}
	
		//============================================
		// Added "Blank" Options
		//============================================
		foreach ($this->blank as $bv) {

			//-----------------------------------------
			// Option Attributes: Value
			//-----------------------------------------
			$o_attrs = array('value' => $bv[0]);

			//-----------------------------------------
			// Selected Value
			//-----------------------------------------
			if (isset($this->select_value)) {
                if (is_array($this->select_value) && isset($this->select_value[$bv[0]])) {
	                $o_attrs['selected'] = 'selected';
                }
				else {
					settype($bv[0], 'string');
					if ($this->select_value === $bv[0]) {
						$o_attrs['selected'] = 'selected';
					}
				}
			}

			//-----------------------------------------
			// Create Option Element
			//-----------------------------------------
			$o = new gen_element('option', $bv[1], $o_attrs);
			$o->force_endtag(1);

			//-----------------------------------------
			// Element Attributes
			//-----------------------------------------
			if (isset($this->elements_attrs[$bv[0]])) {
				$o->attrs($this->elements_attrs[$bv[0]]);
			}

			$o->render();
		}

		//============================================
		// Options
		//============================================
		$opt_group = null;
		foreach ($this->select_vals as $key => $value) {

			//-----------------------------------------
			// Option Attributes: Value
			//-----------------------------------------
			$o_attrs = array('value' => $key);

			//-----------------------------------------
			// Option Group
			//-----------------------------------------
			if (is_array($value)) { 
				$tmp_val_arr = $value;
				$value = (isset($tmp_val_arr[0])) ? ($tmp_val_arr[0]) : (''); 
				if (isset($tmp_val_arr[1]) && $tmp_val_arr[1] !== $opt_group) {
					$opt_group = $tmp_val_arr[1];
					print new gen_element('optgroup', '', array('label' => $tmp_val_arr[1]));
				} 
			}

			//-----------------------------------------
			// Selected Value
			//-----------------------------------------
			if (isset($this->select_value)) {
                if (is_array($this->select_value) && isset($this->select_value[$key])) {
	                $o_attrs['selected'] = 'selected';
                }
				else {
					settype($key, 'string');
					if ($this->select_value === $key) {
						$o_attrs['selected'] = 'selected';
					}
				}
			}

			//-----------------------------------------
			// Create Option Element
			//-----------------------------------------
			$o = new gen_element('option', $value, $o_attrs);
			$o->force_endtag(1);

			//-----------------------------------------
			// Element Attributes
			//-----------------------------------------
			if (isset($this->elements_attrs[$key])) {
				$o->attrs($this->elements_attrs[$key]);
			}

			//-----------------------------------------
			// Output
			//-----------------------------------------
			$o->render();
		}
		
		$this->inset_val .= ob_get_clean();
		parent::render($buffer);
	}	

}

