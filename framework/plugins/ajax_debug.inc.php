<?php
/**
 * AJAX Debug Plugin
 *
 * @package		phpOpenFW
 * @subpackage	Plugin
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		http://www.gnu.org/licenses/gpl-2.0.txt
 * @version 		Started: 7-12-2006, Last updated: 3-20-2010
 **/

// Typical use:
//
// load_plugin("ajax_debug");
// start_ajax_debug();
// [ CODE HERE ]
// stop_ajax_debug(dirname(__file__) . "/debug.txt");
//

//***********************************************************
/**
 * Start AJAX debugging (i.e. Start buffering)
 */
//***********************************************************
function start_ajax_debug()
{
	return ob_start();
}

//***********************************************************
/**
 * Stop AJAX debug (i.e. Write buffer content to file)
 */
//***********************************************************
function stop_ajax_debug($file)
{
	$debug = ob_get_clean();
	$wh = fopen($file, 'w');
	if ($wh) {
		fwrite($wh, $debug);
		fclose($wh);
		return true;
	}
	else {
		return false;
	}
}

?>
