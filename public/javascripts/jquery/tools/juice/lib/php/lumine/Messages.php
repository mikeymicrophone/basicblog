<?php
/**
* Lumine Project
* @copyright (C) 2005 Hugo Ferreira da Silva. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author Hugo Ferreira da Silva <eu@hufersil.com.br>
* @link http://www.hufersil.com.br/lumine/ Lumine Project
* Lumine is Free Software
**/

/** List of messages */
$__LUMINE_MESSAGES = array();
/** 
* Class to get messages from a file
*
* Used by LumineValidation
* @package Lumine
*/
class Messages {
	/**
	* Parses a given file to get the messages from
	* 
	* @return void
	* @author Hugo Ferreira da Silva
	* @access public
	*/
	function parseFile($file) {
		global $__LUMINE_MESSAGES;
		if(file_exists($file)) {
			$fp = fopen($file,'r+');
			while($line = fgets($fp)) {
				if($line != '' && substr($line, 0, 1) != ';') {
					list($key, $value) = explode("=", $line);
					$key = trim($key);
					$value = trim($value);
					$__LUMINE_MESSAGES[$key] = $value;
				}
			}
			fclose($fp);
		}
	}
	
	/**
	* Get a message from a file
	* <code>
	* $msg = Messages::getMessage( "person.invalid_name" );
	* </code>
	* @param string $key The key of message in file
	* @return string The value of key
	*/
	function getMessage( $key ) {
		global $__LUMINE_MESSAGES;
		if(isset($__LUMINE_MESSAGES[$key])) {
			return $__LUMINE_MESSAGES[$key];
		}
		return false;
	}
}


?>