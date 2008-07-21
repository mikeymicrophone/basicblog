<?php
/**
* @copyright (C) 2005 Hugo Ferreira da Silva. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author Hugo Ferreira da Silva <eu@hufersil.com.br>
* @link http://www.hufersil.com.br/lumine/ Lumine Project
* Lumine is Free Software
**/

if(!defined("LUMINE_INCLUDE_PATH")) {
	/** Dinamically sets the include path for Lumine files */
	define("LUMINE_INCLUDE_PATH", dirname(__FILE__) . '/');
}

/** Lumine Configuration class */
require_once LUMINE_INCLUDE_PATH . 'LumineConfiguration.php';
/** DOM IT XML Parser */
require_once LUMINE_INCLUDE_PATH . 'domit/xml_domit_parser.php';


/**
* Constroi os controles para manusear os objetos
* @package Lumine
* @author Hugo Ferreira da Silva
* @access public
*/
class CreateControls {
	var $conf;
	
	function CreateControls($xmlfile) {
		LumineLog::setLevel( 3 );
		LumineLog::setOutput( 'browser' );
		
		$this->conf = new LumineConfiguration( $xmlfile );
		
	}
}

?>