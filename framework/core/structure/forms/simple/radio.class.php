<?php
/**
* A class for constructing a radio button
*
* @package		phpOpenFW
* @subpackage	Form_Engine
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 5-17-2006 Updated: 1-20-2010
**/

//**************************************************************************
/**
 * Radio Button Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class radio extends element
{	
	//***********************************************************************
	// Constructor Function
	//***********************************************************************
	public function __construct($name, $value, $checked=false)
	{
		$this->element = 'input';
		$this->set_attribute('type', 'radio');
		$this->set_attribute('name', $name);
		$this->set_attribute('value', $value);
		if ($checked) { $this->set_attribute('checked', 'checked'); }
	}

}

?>
