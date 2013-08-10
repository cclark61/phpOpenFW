<?php
/**
* A group of simple classes for constructing a form elements
*
* @package		phpOpenFW
* @subpackage	Form_Engine
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 3-20-2006 Updated: 12-29-2011
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

//**************************************************************************
/**
 * Secret Textbox Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class secret extends element
{	
	//***********************************************************************
	// Constructor Function
	//***********************************************************************
	public function __construct($name, $value='', $size=20)
	{
		$this->element = 'input';
		$this->set_attribute('type', 'password');
		$this->set_attribute('name', $name);
		$this->set_attribute('value', $value);
		$this->attributes['size'] = $size;
	}

}

//**************************************************************************
/**
 * Hidden Field Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class hidden extends element
{	
	//***********************************************************************
	// Constructor Function
	//***********************************************************************
	public function __construct($name, $value)
	{
		$this->element = 'input';
		$this->set_attribute('type', 'hidden');
		$this->set_attribute('name', $name);
		$this->set_attribute('value', $value);
	}

}

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

//**************************************************************************
/**
 * Submit Input Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class submit extends element
{	
	//************************************************************************
	// Constructor Function
	//************************************************************************
	public function __construct($name, $value)
	{
		$this->element = 'input';
		$this->set_attribute('type', 'submit');
		$this->set_attribute('value', $value);
	}

}

//**************************************************************************
/**
 * Button Class
 * @package		phpOpenFW
 * @subpackage	Form_Engine
 */
//**************************************************************************
class button extends element
{	
	//************************************************************************
	// Constructor Function
	//************************************************************************
	public function __construct($content, $type=false)
	{
		$this->element = 'button';
		if ($type) { $this->set_attribute('type', $type); }
		$this->inset($content);
	}

}

?>
