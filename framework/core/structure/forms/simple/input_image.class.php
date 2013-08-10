<?php
/**
* A class for constructing a input image button
*
* @package		phpOpenFW
* @subpackage	Form_Engine
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 5-12-2008 Updated: 1-20-2010
**/

//**************************************************************************
/**
 * Image Button Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class input_image extends element
{	
	//***********************************************************************
	// Constructor Function
	//***********************************************************************
	public function __construct($source, $name=false)
	{
		$this->element = 'input';
		$this->set_attribute('type', 'image');
		$this->set_attribute('src', $source);
		if ($name) { $this->set_attribute('name', $name); }
	}

}

?>
