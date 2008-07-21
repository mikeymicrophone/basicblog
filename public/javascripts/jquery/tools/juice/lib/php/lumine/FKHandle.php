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
* A class to handle foreign keys for database that dont suport them.
* This class NEVER be instantiated by end user.
*
* @package Lumine
* @author Hugo Ferreira da Silva
**/

class FKHandle {
	
	/**
	* checa se o objeto possui relacionamentos e remove os objetos relacionados ou restringe.
	* Se houver relacionamentos no modo RESTRICT (ao menos um), Lumine nгo irб remover.
	* Se todos forem CASCADE, Lumine irб remover todos.
	* Se o relacionamento no mapeameanto for definido de qualquer outra forma a nгo ser RESTRICT ou CASCADE,
	* serб interpretado como RESTRICT.
	* @author Hugo Ferreira da Silva
	* @access public
	* @param LumineBase $obj Um objeto que extende a classe LumineBase
	* @return array
	* @static 
	*/
	
	function removeAll(&$obj) {
		if(is_a($obj, 'luminebase')==false) {
			LumineLog::logger(2, 'O objeto nгo extende a classe LumineBase', __FILE__,__LINE__);
			return false;
		}
		LumineLog::logger(1,'Recuperando relacionamentos de '.get_class($obj),__FILE__,__LINE__);
		$fks=$obj->oTable->getForeignKeys();

		// var que diz q passa
		$pass = true;		
		// para cada um encontrado
		foreach ($fks as $fkname => $prop) {
			// para as que sгo OTM
			if($prop['type'] == LUMINE_OTM) {
				// recupera a classe relacionada
				$o = Util::Import( $prop['class'] );
				// pega as fk's da outra classe
				$fko = $o->oTable->getForeignKeys();
				// se for restrict
				if(isset($fko['ondelete']) && strtolower($fko['ondelete']) != 'cascade') {
					// pega o valor do relacionamento
					$v = $obj->{$fko[ $prop['linkOn'] ]['linkOn']};
					// se o valor for nulo, passa para a prуxima iteraзгo
					if($v == '') {
						continue;
					}
					$o->$prop['linkOn'] = $v;
					// se o valor nгo for nulo, procura por elementos
					$total = $o->count();
					// se encontrar
					if($total > 0) {
						LumineLog::logger(1,'Objeto relacionado encontrado em '.$prop['class'] .': parando remoзгo',__FILE__,__LINE__);
						// pбra o loop e nгo deixa remover
						$pass = false;
						break;
					}
				}
			}
			// olhando pelas many-to-many
			if($prop['type'] == LUMINE_MTM) {
				if(!isset($prop['ondelete']) || strtolower($prop['ondelete']) != 'cascade') {
					// tenta recuperar o link
					$x = $obj->getLink( $fkname );
					// se estiver vazio
					if(count($x) == 0) {
						LumineLog::logger(1,'Objeto relacionado encontrado em '.$prop['class'].': parando remoзгo',__FILE__,__LINE__);
						// passa para a prуxima iteraзгo
						continue;
					}
					// diz que nгo pode remover
					$pass = false;
					// pбra o loop
					break;
				}
			}
		}
		
		// se passou
		if($pass) {
			// remove todos os objetos relacionados a este
			reset($fks);
			foreach($fks as $fkname => $prop) {
				if($prop['type'] == LUMINE_OTM) {
					LumineLog::logger(1,'Removendo objetos de '.$prop['class'],__FILE__,__LINE__);
					$o = Util::Import( $prop['class'] );
					$fko = $o->oTable->getForeignKeys();
					$v = $obj->$fko[ $prop['linkOn'] ]['linkOn'];
					
					if($v != '') {
						$o->$prop['linkOn'] = $v;
						$o->delete();
					}
				}
				if($prop['type'] == LUMINE_MTM) {
					LumineLog::logger(1,'Removendo objetos de '.$prop['class'],__FILE__,__LINE__);
					$obj->removeAll($fkname);
				}
			}
			return true;
		}
		return false;
	}

}

?>