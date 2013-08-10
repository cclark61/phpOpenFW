<?php
/**
* A class for constructing a textbox
*
* @package		phpOpenFW
* @subpackage	Form_Engine
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 2-26-2008 Updated: 1-20-2010
**/

//**************************************************************************
/**
 * File Upload Field Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class file_upload extends element
{	
	//************************************************************************
	// Constructor Function
	//************************************************************************
	public function __construct($name, $size=20)
	{
		$this->element = 'input';
		$this->set_attribute('type', 'file');
		$this->set_attribute('name', $name);
		$this->attributes['size'] = $size;
	}

}

?>
