<?php
/**
* @copyright (C) 2005 Hugo Ferreira da Silva. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author Hugo Ferreira da Silva <eu@hufersil.com.br>
* @link http://www.hufersil.com.br/lumine/ Lumine Project
* Lumine is Free Software
**/

if(!defined('LUMINE_INCLUDE_PATH')) {
	/** Dynamically define the Path to include Lumine files */
	define('LUMINE_INCLUDE_PATH', dirname(__FILE__) . '/');
}

/**
* A class to get XML Maps definitions of a table.
* This class NEVER be instantiated by end user.
* This class is used by the LumineConfiguration class
*
* @package Lumine
* @author Hugo Ferreira da Silva
**/

class Entity  {
	/** @var Array List o primary keys of entity */
	var $primary_keys;
	/** @var String Name of the sequence key of entity */
	var $sequence_key;
	/** @var String Class / type that sequence will use to get the value for the sequence key */
	var $sequence_generator;
	/** @var Array List of columns of the entity */
	var $columns;
	/** @var String Tablename of this class will represent */
	var $tablename;
	/** @var String Name of class */
	var $class;
	/** @var Array List of foreign keys (many-to-one relationships) of this entity */
	var $foreign_keys;
	/** @var String Name of the parent class that this entity will extends */
	var $extends;
	/** @var String Internal name of this entity */
	var $id;
	/** @var Object Configuration Object providade by LumineConfiguration Class */
	var $config;
	
	/**
	* Constructor of class
	* @param String $xmlMap XML file that describe this entity
	* @param Object $conf LumineConfiguration object, passed by reference
	* @author Hugo Ferreira da Silva
	*/
	function Entity( $xmlMap, &$conf ) {
		
		$this->columns = array();
		$this->foreign_keys = array();
		$this->primary_keys = array();
		$this->sequence_key = '';
		$this->sequence_generator = '';
		$this->sequence_generator_method = '';
		$this->tablename = '';
		$this->class = '';
		$this->extends = '';
		
		$this->parse( $xmlMap, $conf );
		
		$this->id = md5( $this->tablename );
	}
	
	/**
	* Parse the XML file provided in the contructor of class
	* This entity will be stocked in the LumineConfiguration Object created
	* @param String $xmlMap XML file that describe this entity
	* @author Hugo Ferreira da Silva
	*/
	function parse( $xmlMap, &$conf ) {
		if(!file_exists($xmlMap)) {
			LumineLog::logger(1, 'Arquivo não existente (' . $xmlMap . ')');
			return;
		}
		
		$conf =& new DOMIT_Document();
		$conf->resolveErrors( true );
		if(!$conf->loadXML( $xmlMap )) {
			echo "<pre><strong>Erro no XML de Classe:</strong> " . $conf->getErrorString(). " (". $conf->getErrorCode().")<br>";
			echo "Classe: $xmlMap</pre>";
			exit;
		}
		
		$cfg = $conf->getElementsByPath('/lumine-map');
		if($cfg->getLength() == 0) {
			LumineLog::logger(1, 'XML incorreto (não foi encontrado o elemento lumine-map)');
			return;
		}
		
		if(!$cfg->arNodeList[0]->hasAttribute('table')) {
			LumineLog::logger(1, 'nome da tabela não informado');
			return;
		}
		if(!$cfg->arNodeList[0]->hasAttribute('class')) {
			LumineLog::logger(1, 'Classe não informada)');
			return;
		}
		$this->tablename = $cfg->arNodeList[0]->getAttribute('table');
		$this->class = $cfg->arNodeList[0]->getAttribute('class');
		$this->extends = $cfg->arNodeList[0]->getAttribute('extends');
		
		// pega o campo de ID
		$idNode = &$conf->getElementsByPath('/lumine-map/id', 1);
		if($idNode != null) {
			$id = array();
			if(!$idNode->hasAttribute('name')) {
				LumineLog::logger(1, 'Você deve informar o attributo name');
				return ;
			}
			if(!$idNode->hasAttribute('column')) {
				$id['column'] = $idNode->getAttribute('name');
			} else {
				$id['column'] = $idNode->getAttribute('column');
			}
			$this->sequence_key = $idNode->getAttribute("name");
			
			$gen = &$conf->getElementsByPath('/lumine-map/id/generator');
			if($gen->getLength() > 0) {
				$gen = $gen->item( 0 );
				$this->sequence_generator = $gen->getAttribute('class');
				$this->sequence_generator_method = $gen->getAttribute('method');
			} else {
				$this->sequence_generator = 'default';
			}
			
			// verifica se linka com algum campo caso esteja extendendo uma classe
			if($idNode->hasAttribute("linkOn")) {
				$id['linkOn'] = $idNode->getAttribute("linkOn");
			}
			
			//if($idNode->getAttribute('primary-key') == 'true') {
			$this->primary_keys[] = $idNode->getAttribute('name');
			//}
			$id['primary_key'] = true;
			$this->columns[$idNode->getAttribute('name')] = $id;
		}
		
		// pega os campos normais
		$columns =& $conf->getElementsByPath('/lumine-map/property');
		if($columns->getLength() > 0){
			$total = $columns->getLength();
			for($i=0; $i < $total; $i++) {
				$node = $columns->item( $i );
				if($node->hasAttribute('name') == false) {
					LumineLog::logger(1, 'Você deve informar o attributo name para (tabela '.$this->tablename.')');
					break;
				}
				if($node->hasAttribute('type') == false) {
					LumineLog::logger(1, 'Você deve informar o attributo type para (tabela '.$this->tablename.', campo '.$node->getAttribute('name').')');
					break;
				}
				$fName = $node->getAttribute('name');
				while(list($att, $value) = each($node->attributes->arNodeMap)) {
					if($att != 'name') {
						$this->columns[$fName][$att] = $value->nodeValue;
					}
					if($att == 'primary-key' && $value->nodeValue == 'true') {
						$this->columns[$fName]['primary_key'] = true;
						$this->primary_keys[] = $fName;
					}
				}
				if(($c = $node->getAttribute('column')) != '') {
					$this->columns[$fName]['column'] = $c;
				} else {
					$this->columns[$fName]['column'] = $node->getAttribute('name');
				}
			}
		}
		
		// relacionamentos one-to-many
		$otm = $conf->getElementsByPath('/lumine-map/many-to-one');
		for($i=0, $max=$otm->getLength(); $i<$max; $i++) {
			$fk = $otm->arNodeList[ $i ]->getAttribute('name');
			$this->foreign_keys[$fk]['type'] = 'many-to-one';
			$this->foreign_keys[$fk]['class'] = $otm->arNodeList[ $i ]->childNodes[0]->getAttribute( 'name' );
			$this->foreign_keys[$fk]['column'] = $otm->arNodeList[ $i ]->childNodes[0]->getAttribute( 'column' );
			$this->foreign_keys[$fk]['ondelete'] = $otm->arNodeList[ $i ]->childNodes[0]->getAttribute( 'ondelete' );
			$this->foreign_keys[$fk]['onupdate'] = $otm->arNodeList[ $i ]->childNodes[0]->getAttribute( 'onupdate' );
			$this->foreign_keys[$fk]['linkOn'] = $otm->arNodeList[ $i ]->childNodes[0]->getAttribute( 'linkOn' );
			$this->foreign_keys[$fk]['lazy'] = $otm->arNodeList[ $i ]->getAttribute( 'lazy' );
			$this->foreign_keys[$fk]['foreign'] = true;
			$this->foreign_keys[$fk]['name'] = $fk;
			if($otm->arNodeList[ $i ]->getAttribute( 'primary-key' ) == 'true') {
				$this->primary_keys[] = $fk;
			}
			
			$this->columns[$fk] = &$this->foreign_keys[$fk];
		}
		
		// relacionamentos many-to-one
		$mto = $conf->getElementsByPath('/lumine-map/one-to-many');
		for($i=0, $max=$mto->getLength(); $i<$max; $i++) {
			$fk = $mto->arNodeList[ $i ]->getAttribute('name');
			$this->foreign_keys[$fk]['name'] = $fk;
			$this->foreign_keys[$fk]['type'] = 'one-to-many';
			$this->foreign_keys[$fk]['class'] = $mto->arNodeList[ $i ]->childNodes[0]->getAttribute( 'name' );
			$this->foreign_keys[$fk]['column'] = $mto->arNodeList[ $i ]->childNodes[0]->getAttribute( 'column' );
			$this->foreign_keys[$fk]['linkOn'] = $mto->arNodeList[ $i ]->childNodes[0]->getAttribute( 'linkOn' );
			$this->foreign_keys[$fk]['lazy'] = $mto->arNodeList[ $i ]->getAttribute( 'lazy' );
			$this->foreign_keys[$fk]['foreign'] = true;
			$this->columns[$fk] = &$this->foreign_keys[$fk];
		}
		
		// relacionamentos many-to-many
		$mtm = $conf->getElementsByPath('/lumine-map/many-to-many');
		for($i=0, $max=$mtm->getLength(); $i<$max; $i++) {
			$node = $mtm->item( $i );
			$fk = $node->getAttribute('name');
			$this->foreign_keys[$fk]['name'] = $fk;
			$this->foreign_keys[$fk]['type'] = 'many-to-many';
			$this->foreign_keys[$fk]['linkOn'] = $node->firstChild->hasAttribute('linkOn') ? $node->firstChild->getAttribute('linkOn') : $node->getAttribute('name');
			$this->foreign_keys[$fk]['table'] = $node->getAttribute('table');
			$this->foreign_keys[$fk]['foreign'] = true;
			$this->foreign_keys[$fk]['class'] = $node->firstChild->getAttribute('name');
			$this->foreign_keys[$fk]['lazy'] = $node->getAttribute('lazy');
			//$this->columns[$fk] = &$this->foreign_keys[$fk];
		}
		
	}
	
	/**
	* Get the columns described in the XML file
	* @return Array An associative array of columns in this entity
	* @author Hugo Ferreira da Silva
	*/
	function getColumns($herance = false) {
		$columns = $this->columns;
		
		// se esta for uma classe extendida
		if(isset($this->extends) && $this->extends != '' && $herance == true) {
			$e = &$this->config->getEntity($this->extends);
			$columns = array_merge($e->getColumns(), $columns);
		}
		reset($this->foreign_keys);
		return $columns;
	}
	
	/**
	* Return the foreign keys of this entity
	* @return Array Associative array with the foreign keys of this entity with their properties
	* @author Hugo Ferreira da Silva
	*/
	function getForeignKeys() {
		return $this->foreign_keys;
	}

	/**
	* Get the primary keys of this entity
	* @return Array Associative array with the primary keys of this entity with their properties
	* @author Hugo Ferreira da Silva
	*/
	function getPrimaryKeys() {
		$x = array();
		foreach($this->primary_keys as $pk) {
			$x[$pk] = $this->getFieldProperties( $pk );
		}
		return $x;
	}
	
	/**
	* Get the properties of the column 
	* @param String $fname Name of desired column properties
	* @return Mixed Associative array on sucess, false on failure
	* @author Hugo Ferreira da Silva
	*/
	function getColumnProperties ($fname) {
		$list = $this->getColumns( true );
		foreach($list as $key => $prop) {
			if($fname == $prop['column']) {
				$prop['name'] = $key;
				return $prop;
			}
		}
		
		foreach($this->foreign_keys as $fk => $prop) {
			if($fk == $fname) {
				reset($this->foreign_keys);
				$prop['name'] = $fk;
				return $prop;
			}
		}
		
		return false;
	}
	
	/**
	* Get the properties of the field 
	* @param String $fname Name of desired field properties
	* @return Mixed Associative array on sucess, false on failure
	* @author Hugo Ferreira da Silva
	*/
	function getFieldProperties($name) {
		$list = $this->getColumns( true );
		if(isset($list[$name])) {
			$r = $list[$name];
		} else {
			$r=false;
		}

		if($r) {
			return $r;
		}
		// procura nas chaves estrangeiras
		if(isset($this->columns[$name])) {
			return $this->columns[$name];
		}/*
		foreach($this->foreign_keys as $fk => $prop) {
			if($fk == $name) {
				reset($this->foreign_keys);
				return $prop;
			}
		}*/
		return false;
	}
	
	/**
	* Get the properties of the link 
	* @param String $fname Name of desired link properties
	* @return Mixed Associative array on sucess, false on failure
	* @author Hugo Ferreira da Silva
	*/
	function getLinkProperties ($fname) {
		if(isset($this->foreign_keys[$fname])) {
			return $this->foreign_keys[$fname];
		}
		return false;
	}
}
?>