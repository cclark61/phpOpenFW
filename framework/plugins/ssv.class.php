<?php

/**
* Server Side Validation Plugin
*
* @package		phpOpenFW
* @subpackage	Plugin
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 2-25-2008, Last updated: 1-29-2013
**/

//***************************************************************
/**
 * Server Side Validation Class
 * @package		phpOpenFW
 * @subpackage	Plugin
 */
//***************************************************************
class server_side_validation
{
	//==============================================================
	// Member Variables
	//==============================================================
	
	/**
	* @var array Check $_POST for variables to validate
	**/
	private $check_post;

	/**
	* @var array Check $_GET for variables to validate
	**/
	private $check_get;

	/**
	* @var array Validation Types
	**/
	private $valid_types;
	
	/**
	* @var bool Validation Status
	**/
	private $validation_status;

	/**
	* @var array Failed Validations
	**/
	private $failed_checks;

	/**
	* @var array Validations to perform
	**/
	private $validations;

	/**
	* @var array Validation error messages
	**/
	private $fail_messages;

	/**
	* @var bool Debug Mode (Default: false)
	**/
	private $debug_mode;
	
	/**
	* @var string Transformation XSL Stylesheet
	**/
	private $xsl;
	
	/**
	* @var string POST Sub Arrays to check
	**/
	private $post_sub_arrays;
	
	//======================================================================================
	//======================================================================================
	// Member Functions
	//======================================================================================
	//======================================================================================
	
	//**************************************************************************************
	/**
	* Constructor function
	* @param string 
	**/
	//**************************************************************************************
	// Constructor Function
	//**************************************************************************************
	public function __construct($check_post=true, $check_get=false)
	{
		$this->check_post = ($check_post) ? (true) : (false);
		$this->check_get = ($check_get) ? (true) : (false);
		$this->validation_status = null;
		$this->num_failed_checks = 0;
		$this->failed_checks = array();
		$this->validations = array();
		$this->fail_messages = array();
		$this->debug_mode = false;
		$this->post_sub_arrays = array();
		$this->xsl = $_SESSION['frame_path'] . '/default_templates/ssv_messages.xsl';
		$this->xsl_default = $this->xsl;
		
		// Validation Types
		$this->valid_types = array();
		$this->valid_types['is_empty'] = '';
		$this->valid_types['is_not_empty'] = '';
		$this->valid_types['is_numeric'] = '';
		$this->valid_types['is_not_numeric'] = '';
		$this->valid_types['is_date'] = '';
		$this->valid_types['fields_match'] = '';
		$this->valid_types['fields_not_match'] = '';
		$this->valid_types['custom'] = '';
		$this->valid_types['checkbox_is_checked'] = '';
		$this->valid_types['radio_is_checked'] = '';
	}
	
	//**************************************************************************************
	/**
	* Add Check
	* @param string Field name (First field if more than one)
	* @param string Validation Type
	* @param string Error Message
	* @param string Field name 2 (or other parameter) 
	**/
	//**************************************************************************************
	// Add Check Function
	//**************************************************************************************
	public function add_check($field_name, $valid_type, $valid_txt='', $field2_name='')
	{
		if (!isset($this->valid_types[$valid_type])) {
			trigger_error('[Server Side Validation]::add_check() - Invalid validation type!');			
		}
		else if ($field_name == '') {
			trigger_error('[Server Side Validation]::add_check() - Invalid field name (1)!');
		}
		else {
			$this->validations[] = array($field_name, $valid_type, xml_escape($valid_txt), $field2_name);
		}
	}
	
	//**************************************************************************************
	/**
	* Add Post Sub Array to Check
	* @param string POST Sub Array Name
	**/
	//**************************************************************************************
	// Add Post Sub Array to Check Function
	//**************************************************************************************
	public function check_post_sub_array($sub_array)
	{
		if ($sub_array == '' || is_null($sub_array)) {
			trigger_error('[Server Side Validation]::check_post_sub_array() - Invalid POST Sub Array!');			
		}
		else {
			$this->post_sub_arrays[] = $sub_array;
		}
	}

	/**
	* Validate Function
	* @return bool Success - True, Failure - False
	**/
	//**************************************************************************************
	// Validate Function
	//**************************************************************************************
	public function validate()
	{
		foreach ($this->validations as $key => $check) {
			
			// Set / Reset variable values
			$var_val1 = null;
			$var_val2 = null;
			$vr = null;
			
			//*************************************
			// Pull variable value(s)
			//*************************************
			
			if ($check[1] == 'custom') {
				$var_val1 = (isset($check[0])) ? ($check[0]) : (false);
				$var_val2 = (isset($check[3])) ? ($check[3]) : (false);
			}
			else {
				// POST
				if ($this->check_post) {
					$var_val1 = (isset($_POST[$check[0]]) && $_POST[$check[0]] != '') ? ($_POST[$check[0]]) : (null);
					$var_val2 = (isset($_POST[$check[3]]) && $_POST[$check[3]] != '') ? ($_POST[$check[3]]) : (null);
				}
				
				// GET
				if ($this->check_get && is_null($var_val1)) {
					$var_val1 = (is_null($var_val1) && isset($_GET[$check[0]]) && $_GET[$check[0]] != '') ? ($_GET[$check[0]]) : (null);
					$var_val2 = (is_null($var_val2) && isset($_GET[$check[3]]) && $_GET[$check[3]] != '') ? ($_GET[$check[3]]) : (null);
				}
				
				// Check POST Sub Arrays
				if (is_null($var_val1)) {
					foreach ($this->post_sub_arrays as $sub_array) {
						if (!is_null($var_val1)) { break; }
						$var_val1 = (is_null($var_val1) && isset($_POST[$sub_array][$check[0]]) && $_POST[$sub_array][$check[0]] != '') ? ($_POST[$sub_array][$check[0]]) : (null);
						$var_val2 = (is_null($var_val2) && isset($_POST[$sub_array][$check[3]]) && $_POST[$sub_array][$check[3]] != '') ? ($_POST[$sub_array][$check[3]]) : (null);
					}
				}
			}
			
			// Perform Validation
			switch ($check[1]) {
				case 'is_not_empty':
					$vr = ($var_val1 != '');
					break;
					
				case 'is_empty':
					$vr = ($var_val1 == '');
					break;

				case 'is_numeric':
					$vr = ($var_val1 !== '' && $var_val1 !== false) ? (is_numeric($var_val1)) : (true);
					break;

				case 'is_not_numeric':
					$vr = ($var_val1 !== '' && $var_val1 !== false) ? (!is_numeric($var_val1)) : (true);
					break;

				case 'fields_match':
					$vr = ($var_val1 == $var_val2);
					break;

				case 'fields_not_match':
					$vr = ($var_val1 != $var_val2);
					break;

				case 'is_date':
					$regex = '/^\d{1,2}\/\d{1,2}\/\d{4}$/';
					$vr = preg_match($regex, $var_val1);
					if (strlen($var_val1) > 10) { $vr = false; }
					break;
				
				case 'custom':
					if ($var_val1) { eval("\$vr = ($var_val1);"); }
					else if ($var_val2) { eval("\$vr = ($var_val2);"); }
					else { trigger_error('[Server Side Validation]::validate() - Invalid custom expression!'); }
					break;

				case 'checkbox_is_checked':
					$vr = (!is_null($var_val1) && $var_val1 !== false && $var_val1 != '');
					break;

				case 'radio_is_checked':
					$vr = (!is_null($var_val1) && $var_val1 !== false && $var_val1 != '');
					break;
			}
			
			// Result of current validation
			$this->check_status[$key] = $vr;
			if (!$vr) {
				$this->validation_status = false;
				$this->num_failed_checks++; 
				if ($check[2] != '') { $this->fail_messages[$key] = $check[2]; }
			}
			else if ($vr && is_null($this->validation_status)) {
				$this->validation_status = true;
			}
		}
		
		if ($this->debug_mode && !$this->validation_status) {
			print "<pre>\n";
			print_r($this->validations);
			print_r($this->fail_messages);
			print_r($_POST);
			print "</pre>\n";
		}
		return $this->validation_status;
	}

	/**
	* Status Function
	* @return bool Returns the status of the server side validation (null before / true or false after)
	**/
	//**************************************************************************************
	// Status Function
	//**************************************************************************************
	public function status() { return $this->validation_status; }
	
	/**
	* Fail Messages Function
	* @return array Returns the failure messages produced by the server side validation
	**/
	//**************************************************************************************
	// Status Function
	//**************************************************************************************
	public function fail_messages() { return $this->fail_messages; }
	
	/**
	* Number of failed validations Function
	* @return integer Returns the number of failed validations produced by the server side validation
	**/
	//**************************************************************************************
	// Status Function
	//**************************************************************************************
	public function failed_checks() { return $this->num_failed_checks; }
	
	/**
	* Toggle Debug Mode
	* @param bool True - On, False - Off
	**/
	//**************************************************************************************
	// Debug Mode function
	//**************************************************************************************
	public function debug_mode($tmp_bool) { $this->debug_mode = ($tmp_bool) ? (true) : (false); }
	
	/**
	* Display failed check messages using XSL
	* @param string File path to other XSL Stylesheet
	**/
	//**************************************************************************************
	// Display failed check messages function
	//**************************************************************************************
	public function display_fail_messages($xsl=false)
	{
		$xsl = trim((string)$xsl);

		//--------------------------------------------------------
		// Explicitly use the "default" template that comes 
		// with phpOpenFW
		//--------------------------------------------------------
		if (strtolower($xsl) == 'default') {
			$this->xsl = $this->xsl_default;
		}
		//--------------------------------------------------------
		// Use a specified template
		//--------------------------------------------------------
		else if (!empty($xsl) && file_exists($xsl)) {
			$this->xsl = $xsl;
		}
		//--------------------------------------------------------
		// Use a template defined in the session
		//--------------------------------------------------------
		else if (!empty($_SESSION['ssv_template']) && file_exists($_SESSION['ssv_template'])) {
			$this->xsl = $_SESSION['ssv_template'];
		}
		//--------------------------------------------------------
		// Everything else uses the default template
		//--------------------------------------------------------
		// ..

		//--------------------------------------------------------
		// Create XML
		//--------------------------------------------------------
		$xml = array2xml('failed_checks', $this->fail_messages());

		//--------------------------------------------------------
		// Transform
		//--------------------------------------------------------
		xml_transform($xml, $this->xsl);
	}
}

?>
