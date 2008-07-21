<?php
/**
* @package Lumine
* @copyright (C) 2005 Hugo Ferreira da Silva. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author Hugo Ferreira da Silva <eu@hufersil.com.br>
* @link http://www.hufersil.com.br/lumine/ Lumine Project
* Lumine is Free Software
**/

/**
 * This is a file to hold the types for dialects
 */

class LumineTypes {
	function LumineTypes($dialect) {
		start( $dialect );
	}
	function start( $dialect ) {
		if(!defined('LUMINE_TYPES_STARTED')) {
			switch($dialect) {
				// mysql
				case 'mysqlt':
				case 'mysqli':
					define("SEQUENCE_TYPE", "int4");
					define("SEQUENCE_DEFINITION", "int4 not null auto_increment");
					define("TABLE_DEFINITION", "TYPE=InnoDB");
					define("CREATE_INDEX_FOR_FK", true);
					define("LUMINE_RANDOM_FUNC",'rand()');
					define("LUMINE_FK_SUPORT", true);
				break;
				
				case 'mysql':
					define("SEQUENCE_TYPE", "int4");
					define("SEQUENCE_DEFINITION", "int4 not null auto_increment");
					define("TABLE_DEFINITION", "TYPE=MyISAM");
					define("CREATE_INDEX_FOR_FK", false);
					define("LUMINE_RANDOM_FUNC",'rand()');
					define("LUMINE_FK_SUPORT", false);
				break;
				
				// postgresql
				case 'postgres':
				case 'postgres6':
				case 'postgres7':
				case 'postgres8':
					define("SEQUENCE_TYPE", "int4");
					define("SEQUENCE_DEFINITION", "serial");
					define("TABLE_DEFINITION", "");
					define("CREATE_INDEX_FOR_FK", false);
					define("LUMINE_RANDOM_FUNC",'random()');
					define("LUMINE_FK_SUPORT", true);
				break;
			}
			define('LUMINE_TYPES_STARTED', 1);
		}
	}
	function getSKDef( $dialect ) {
		if(!defined('LUMINE_TYPES_STARTED')) {
			LumineTypes::start($dialect);
		}
		return defined('SEQUENCE_DEFINITION') ? SEQUENCE_DEFINITION : false;
	}
	
	/**
	 * Gets the seguence fields type for FK to selected dialect
	 * @param string $dialect String of dialect used
	 * @return string String with definition
	 * @author Hugo Ferreira da Silva
	 */
	function getFKType( $dialect ) {
		if(!defined('LUMINE_TYPES_STARTED')) {
			LumineTypes::start($dialect);
		}
		return defined('SEQUENCE_TYPE') ? SEQUENCE_TYPE : false;
	}
	/**
	 * Check if the object is ID field
	 * @param object $obj Object with properties of a field
	 * @param string $dialect String of dialect used
	 * @return boolean True if is an ID field otherwise false;
	 * @author Hugo Ferreira da Silva
	 */
	function checkIDField( &$obj, $dialect ) {
		if(!defined('LUMINE_TYPES_STARTED')) {
			LumineTypes::start($dialect);
		}
		switch($dialect) {
			// mysql
			case 'mysqlt':
			case 'mysqli':
			case 'mysql':
				if(isset($obj->auto_increment) && $obj->auto_increment) {
					return true;
				}
				return false;
			break;
			
			// postgresql
			case 'postgres':
			case 'postgres6':
			case 'postgres7':
			case 'postgres8':
				if(isset($obj->default_value) && preg_match('#nextval#i',$obj->default_value)) {
					return true;
				}
				return false;
			break;
		}
	}
	
	/**
	 * Disable foreign key checks 
	 */
	function disableForeignKeys( $dialect ) {
		if(!defined('LUMINE_TYPES_STARTED')) {
			LumineTypes::start($dialect);
		}
		switch($dialect) {
			// mysql
			case 'mysqlt':
			case 'mysqli':
			case 'mysql':
				return 'SET FOREIGN_KEY_CHECKS=0';
			break;
		}
		return '';
	}
}

?>