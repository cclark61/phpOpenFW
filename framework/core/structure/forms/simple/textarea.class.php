<?php
/**
* A class for constructing a textarea
*
* @package		phpOpenFW
* @subpackage	Form_Engine
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 3-21-2006 Updated: 1-20-2010
**/

//**************************************************************************
/**
 * Textarea Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class textarea extends element
{	
	//************************************************************************
	// Constructor Function
	//************************************************************************
	public function __construct($name, $value='', $cols=20, $rows=3)
	{
		$this->element = 'textarea';
		$this->set_attribute('name', $name);
		$this->inset_val = $value;
		$this->attributes['cols'] = $cols;
		$this->attributes['rows'] = $rows;
		$this->endtag = true;
	}

}

?>
