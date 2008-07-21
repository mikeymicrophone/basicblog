<?php
/**
* Lumine Project
* @copyright (C) 2005 Hugo Ferreira da Silva. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author Hugo Ferreira da Silva <eu@hufersil.com.br>
* @link http://www.hufersil.com.br/lumine/ Lumine Project
* Lumine is Free Software
**/

/** 
* Pega as configurações para log
*/
$file = dirname(__FILE__) . "/lumine_log.properties";
$GLOBALS['__LumineLog'] = array();
if(file_exists($file)) {
	$fp = @fopen($file, "r");
	if($fp) {
		while($line = fgets($fp)) {
			if($line != '' && substr($line, 0, 1) != ';') {
				list($key, $value) = explode("=", $line);
				$key  = trim($key);
				$value = trim($value);
				$GLOBALS['__LumineLog'][$key] = $value;
			}
		}
	}
}

/**
* Class for logging actions
* @author Hugo Ferreira da Silva
*/
class LumineLog {
	/**
	*
	*/
	function logger ($level, $msg, $file = false, $line = false) {
		$p = &$GLOBALS['__LumineLog'];
		if(!isset($p['log-level']) || $p['log-level'] == 0) {
			return;
		}
		$date = date("d/m/Y H:i:s");
		switch($level) {
			case 1: // debug
				$msg = "<pre><b>$date: Debug</b>: $msg ($file, linha $line)</pre>\r\n";
			break;
			
			case 2: // warning
				$msg = "<pre><b>$date: Aviso</b>: $msg ($file, linha $line)</pre>\r\n";
			break;
			
			case 3: // Error
				$msg = "<pre><b>$date: Erro</b>: $msg ($file, linha $line)</pre>\r\n";
			break;
		}
		

		if($level <= $p['log-level']) {
			if(isset($p['log-to']) && $p['log-to'] != 'output') {
				$file = @fopen($p['class-path'] . "/" . $p['log-to'], "a+");
				if($file) {
					fwrite($file, strip_tags($msg));
					fclose($file);
				}
			}
			
			if(isset($p['log-to']) && $p['log-to'] == 'output') {
				echo $msg;
			}
		}
	}
	
	/**
	* Sets the output of logger
	* If called without arguments, set output to browser if output level is > 0
	* @param string $filename Filename to output log errors
	* @author Hugo Ferreira da Silva
	* @access public
	*/
	function setOutput ($filename = null) {
		if($filename == null || $filename == 'default' || $filename == 'browser' || $filename == 'output') {
			$GLOBALS['__LumineLog']['log-to'] = 'output';
			return;
		}
		$GLOBALS['__LumineLog']['log-to'] = $filename;
	}
	
	/**
	* Sets the log level
	* 
	* @param number $level The level o log
	* @param mixed $output Where to put the output
	* @author Hugo Ferreira da Silva
	* @access public
	*/
	function setLevel( $level, $output = false ) {
		$GLOBALS['__LumineLog']['log-level'] = $level;
		if($output !== false) {
			LumineLog::setOutput($output);
		}
	}
}

?>