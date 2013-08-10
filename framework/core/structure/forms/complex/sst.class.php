<?php
//**************************************************************************
//**************************************************************************
/**
* A class for constructing Simple Selects from a Table (SST)
*
* @package		phpOpenFW
* @subpackage	Form_Engine
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 9-21-2005 Updated: 11-20-2012
**/
//**************************************************************************
//**************************************************************************

//***************************************************************
// Contributions by Lucas Hoezee ( http://thecodify.com/ )
// 5/10/2011
//***************************************************************

//**************************************************************************
/**
 * Simple Select from Table Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class sst extends select_form_element
{
	private $data_src;		// Data Source
	private $strsql;		// SQL string to query
	private $opt_key;
	private $opt_val;
	private $opt_group;
	
	//*************************************************************************
	// Constructor Function
	//*************************************************************************
	public function __construct($name, $data_src, $strsql, $key, $val, $group=false)
	{
		$this->attributes = array();
		$this->element = 'select';
		$this->attributes['name'] = $name;
		$this->blank = array();
		$this->data_src = $data_src;
		$this->strsql = $strsql;
		$this->opt_key = $key;
		$this->opt_val = $val;
		$this->opt_group = $group;
	}

	//*************************************************************************
	// Construct and output the SST.
	//*************************************************************************
	public function render($buffer=false)
	{
		//============================================
		// Pull items from database
		//============================================
		$data = new data_trans($this->data_src);
		$data->data_query($this->strsql);
		$result = $data->data_assoc_result();
		
		ob_start();
		settype($this->select_value, 'string');

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
                settype($bv[0], 'string');
				if ($this->select_value === $bv[0]) { $o_attrs['selected'] = 'selected'; }
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
		foreach ($result as $row) {

			//-----------------------------------------
			// Option Attributes: Value
			//-----------------------------------------
			$o_attrs = array('value' => $row[$this->opt_key]);

			//-----------------------------------------
			// Option Group
			//-----------------------------------------
			if ($this->opt_group && isset($row[$this->opt_group]) && $row[$this->opt_group] !== $opt_group) {
				$opt_group = $row[$this->opt_group];
				print new gen_element('optgroup', '', array('label' => $row[$this->opt_group]));
			} 
			
			//-----------------------------------------
			// Selected Value
			//-----------------------------------------
			if (isset($this->select_value)) {
				settype($row[$this->opt_key], 'string');
				if ($this->select_value === $row[$this->opt_key]) { $o_attrs['selected'] = 'selected'; }
			}	
			
			//-----------------------------------------
			// Create Option Element
			//-----------------------------------------
			$o = new gen_element('option', $row[$this->opt_val], $o_attrs);
			$o->force_endtag(1);

			//-----------------------------------------
			// Element Attributes
			//-----------------------------------------
			if (isset($this->elements_attrs[$row[$this->opt_key]])) {
				$o->attrs($this->elements_attrs[$row[$this->opt_key]]);
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

?>
