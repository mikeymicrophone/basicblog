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
* Base Class (like a interface) for ResultSet Objects
* @author Hugo Ferreira da Silva
* @access public
**/
class ResultSet {
	var $num_rows;
	var $rid;
	var $rs;
	var $affected_rows;

	/** @see LumineBase::fetch() */
	function fetch() {}
	/** @see LumineBase::fetchRow() */
	function fetchRow($rowId) {}
	/** @see LumineBase::toArray() */
	function toArray() {}
	
}

?>