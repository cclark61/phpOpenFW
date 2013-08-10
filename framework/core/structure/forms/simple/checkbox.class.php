<?php
/**
* A class for constructing a checkbox
*
* @package		phpOpenFW
* @subpackage	Form_Engine
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 3-20-2006 Updated: 1-20-2010
**/

//**************************************************************************
/**
 * Checkbox Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class checkbox extends element
{	
	//************************************************************************
	// Constructor Function
	//************************************************************************
	public function __construct($name, $value, $checked=false)
	{
		$this->element = 'input';
		$this->set_attribute('type', 'checkbox');
		$this->set_attribute('name', $name);
		$this->set_attribute('value', $value);
		if ($checked) { $this->set_attribute('checked', 'checked'); }
	}

}

?>
