<?php
/**
* A class for constructing a textbox
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
 * Textbox Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class textbox extends element
{	
	//************************************************************************
	// Constructor Function
	//************************************************************************
	public function __construct($name, $value='', $size = 20)
	{
		$this->element = 'input';
		$this->set_attribute('type', 'text');
		$this->set_attribute('name', $name);
		$this->set_attribute('value', $value);
		$this->attributes['size'] = $size;
	}

}

?>
