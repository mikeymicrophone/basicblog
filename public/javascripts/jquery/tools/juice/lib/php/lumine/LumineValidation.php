<?php
/**
* @copyright (C) 2005 Hugo Ferreira da Silva. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author Hugo Ferreira da Silva <eu@hufersil.com.br>
* @link http://www.hufersil.com.br/lumine/ Lumine Project
* Lumine is Free Software
**/

/** DOMIT! */
require_once LUMINE_INCLUDE_PATH . 'domit/xml_domit_parser.php';

/** Import de package to get messagse */
require_once LUMINE_INCLUDE_PATH . 'Messages.php';

/**
* Validation for Entities
* 
* This class allow you to validate each field of your by two ways:<br>
* - Using XML files that describe your desired validate method
* - Creating validateFieldname methos inside your classes
* @author Hugo Ferreira da Silva
* @package Lumine
*/
class LumineValidation  {
	/**
	* Constructor
	* @author Hugo Ferreira da Silva
	* @access public
	*/
	function LumineValidation() {
	}
	
	/**
	* Validate a given class
	* A static method to validate your classes<br>
	* <code>
	* require_once 'path_to_lumine_package/LumineValidation.php';
	* $person = new Person;
	* $results = LumineValidation::validate($person);
	* if($results === true) {
	*     $person->save();
	* } else {
	*     print_r( $results );
	* }
	* </code>
	* or
	* <code>
	* require_once 'path_to_lumine_package/LumineValidation.php';
	* $person = new Person;
	* $results = $person->validate();
	* </code>
	* @return mixed True on sucess, false if $class is not a LumineBase object and associative array of field with errors
	* @author Hugo Ferreira da Silva
	* @access public
	* @param object $class A class that extends LumineBase
	*/
	function validate(&$class) {
		// não é da classe LumineBase
		if(!is_a($class, "luminebase")) {
			return false;
		}
		//pega os campos da classe
		$table = $class->table();
		$table = array_merge($table, $class->oTable->getForeignKeys());
		
		// procura pelo arquivo de validaçao classname-validation.xml dentro do diretorio de mapeamentos
		$maps =  $class->oTable->config->config['maps'];
		$maps = str_replace(".","/",$maps);
		$xml = $class->oTable->config->config['class-path'] . '/' . $maps;
		$xml .= '/' . array_pop(explode(".",$class->oTable->class)).'-validation.xml';

		// erros
		$errors = array();
		
		// se encontrou o arquivo, iremos fazer a validaçao pelo arquivo
		if(file_exists($xml)) {
			LumineLog::logger(1, "XML encontrado: " . $xml,__FILE__,__LINE__);
			// criamos uma nova instancia do DOMIT_Document
			$val =& new DOMIT_Document();
			$val->resolveErrors( true );
			if(!$val->loadXML($xml)) {
				// erro no XML
				LumineLog::logger(1, "XML com erro: " . $val->getErrorString(),__FILE__,__LINE__);
				return false;
			}
			
			// pega o primeiro nó
			$first= $val->getElementsByPath("/lumine-validator", 1);
			if($first) {
				$filename = $first->getAttribute("messages_file");
				$fileparts = explode('.',$filename);
				$ext = array_pop($fileparts);
				$file = implode('/', $fileparts) . '.'.$ext;
				
				$file = $class->oTable->config->config['class-path'] .'/' . $file;
				if(file_exists($file) && is_file($file)) {
					Messages::parseFile($file);
				}
			}
			
			
			// Pega os validator
			$validator_list = $val->getElementsByPath("/lumine-validator/field");
			
			// para cada validator
			for ($i=0; $i<$validator_list->getLength(); $i++) {
				
				$node = $validator_list->item( $i );
				$itens = $node->getElementsByPath("validator");
				
				// pega o nome do campo a ser validado
				$key = $node->getAttribute("name");
				
				// se não encontrar
				if($key == '') {
					LumineLog::logger(1, "Informe o nome do campo para validação no arquivo <b>$xml</b>");
					return false;
				}
				
				// se o campo no validator não existe na entidade
				if(!array_key_exists($key, $table)) {
					continue;
				}
				
				// pega o valor na entidade
				$value = $class->$key;
				
				// para cada validator encontrado para este campo
				for($j=0; $j<$itens->getLength(); $j++) {
					$vnode = $itens->item ($j);
					$type = $vnode->getAttribute("type");
					$msg_key = Messages::getMessage( $vnode->getAttribute("msg-key") );
					$msg = $msg_key == false ? $vnode->getAttribute("msg") : $msg_key;
					$rule = $vnode->getAttribute("rule");
					$cname = $vnode->getAttribute("name");
					$cmethod = $vnode->getAttribute("method");
					$minlength = $vnode->getAttribute("minlength");
					$maxlength = $vnode->getAttribute("maxlength");
					$minvalue = $vnode->getAttribute("minvalue");
					$maxvalue = $vnode->getAttribute("maxvalue");
					
					// faz um switch no tipo
					switch($type) {
						case "requiredString":
							if(!array_key_exists($key, $errors) || $errors[$key] === true) {
								if($class->$key == '') {
									$errors[$key] = $msg;
									continue;
								}
								if($minlength != '' && strlen($class->$key) < $minlength) {
									$errors[$key] = $msg;
									continue;
								}
								if($maxlength != '' && strlen($class->$key) > $maxlength) {
									$errors[$key] = $msg;
									continue;
								}
								$errors[$key] = true;
							}
						break;
						
						case "requiredNumber":
							if(!array_key_exists($key, $errors) || $errors[$key] === true) {
								if(!is_numeric($class->$key)) {
									$errors[$key] = $msg;
									continue;
								}
								if($minvalue != '' && $class->$key < $minvalue) {
									$errors[$key] = $msg;
									continue;
								}
								if($maxvalue != '' && $class->$key > $maxvalue) {
									$errors[$key] = $msg;
									continue;
								}
								$errors[$key] = true;
							}
						break;
						
						
						case 'unique':
							if(!array_key_exists($key, $errors) || $errors[$key] === true) {
								
								$testClass = Util::Import($class->oTable->class);
								// se não encontrou a classe
								if($testClass === false) {
									$erros[$key] = $msg;
									continue;
								}
								// se já existir um registro com este valor
								$testClass->$key = $class->$key;
								if($testClass->find() > 0) {
									// pega as chaves primárias
									$pks = $testClass->oTable->getPrimaryKeys();
									$todos = true;
									
									while($testClass->fetch()) {
										// para cada pk
										foreach($pks as $p => $prop) {
											// se ao menos UMA chave primária for diferente
											if($testClass->$p != $class->$p) {
												// não passou na validação
												$todos = false;
												break;
											}
										}
										if($todos == false) {
											break;
										}
									}
									// se houver uma chave diferente
									if($todos == false) {
										// não passou
										$errors[$key] = $msg;
										continue;
									}
								}
								
								$errors[$key] = true;
							}
						break;
						
						case "rule":
							if(!array_key_exists($key, $errors) || $errors[$key] === true) {
								if($rule != '') {
									// coloca os valores para a regra
									$regra = preg_replace("/#(.*?)#/e","\$class->\\1", $rule);
									// executa o código
									$x = eval('if('.$regra.') return true;');
									// se for falso, significa que não passou no teste
									if(!$x) {
										$errors[$key] = $msg;
										continue;
									} else {
										$errors[$key] = true;
										continue;
									}
								} else {
									$errors[$key] = '-- You must provide a rule to validate this field (' . $key . ')';
								}
							}
						break;
						
						case "class":
							if(!array_key_exists($key, $errors) || $errors[$key] === true) {
								if($cname != '') {
									$classname = array_pop(explode(".", $cname));
									if(!class_exists($classname)) {
										Util::Import($cname);
									}
									$x = new $classname;
									if(method_exists($x, $cmethod)) {
										$r = $x->$cmethod( $class->$key );
										if($r === true) {
											$errors[ $key ] = true;
											continue;
										} else {
											$errors[ $key ] = $msg;
											continue;
										}
									} else {
										$errors[$key] = '-- You must provida a valid method for class '.$classname.' to validate the field '. $key;
										continue;
									}
								} else {
									$errors[$key] = '-- You must provide a class to validate this field(' . $key . ')';
									continue;
								}
							}
						break;
						
						case "requiredEmail":
							if(!array_key_exists($key, $errors) || $errors[$key] === true) {
								if(Util::validateEmail($class->$key)) {
									$errors[$key] = true;
									continue;
								} else {
									$errors[$key] = $msg;
									continue;
								}
							}
						break;
						
						case 'requiredDate':
							if(!array_key_exists($key, $errors) || $errors[$key] === true) {
								$reg = array();
								if(preg_match('@^([0-9]{2})/([0-9]{2})/([0-9]{4})$@', $class->$key, $reg)) {
									if(checkdate($reg[2], $reg[1], $reg[3]) || checkdate($reg[1], $reg[2], $reg[3])) {
										$errors[$key] = true;
										continue;
									} else {
										$errors[$key] = $msg;
										continue;
									}
								} else {
									$errors[$key] = $msg;
									continue;
								}
							}
						break;
						
						case 'requiredISODate':
							if(!array_key_exists($key, $errors) || $errors[$key] === true) {
								$reg = array();
								if(preg_match('@^([0-9]{4})-([0-9]{2})-([0-9]{2})$@', $class->$key, $reg)) {
									if(checkdate($reg[2], $reg[3], $reg[1])) {
										$errors[$key] = true;
										continue;
									} else {
										$errors[$key] = $msg;
										continue;
									}
								} else {
									$errors[$key] = $msg;
									continue;
								}
							}
						break;
					}
				}
			}
			
			// checa validações na classse
			foreach($table as $key => $prop) {
				$m = "validate" . ucfirst($key);
				if(method_exists($class, $m)) {
					$x = $class->$m();
					if($x !== true) {
						if(is_bool($x)) {
							$errors[$key] = Messages::getMessage( $class->tablename() . "." . $key );
						} else {
							$errors[$key] = $x;
						}
					} else {
						$errors[$key] = true;
					}
				}
			}
			
			foreach($errors as $key => $msg) {
				if($msg !== true) {
					$_REQUEST[$key . '_error'] = $msg;
				}
			}
			
			reset($errors);
			
			// terminou a validação dessa classe, porém verificamos se ela é uma classe extendida
			if(isset($class->oTable->extends) && $class->oTable->extends != '') {
				$x = Util::Import($class->oTable->extends);
				if($x !== false) {
					$x->setFrom($class->toArray());
					$r = $x->validate();
					
					if($r !== true) {
						$errors = array_merge($errors, $r);
					}
				}
			}
			foreach($errors as $key => $msg) {
				if(isset($errors[$key]) && $errors[$key] !== true) {
					return $errors;
				}
			}
			
			return true;
		}
		LumineLog::logger(1, "XML não encontrado: " . $xml,__FILE__,__LINE__);
		/* somente entrará nesta parte se não encontrar o arquivo de validação */
		// para cada campo (não sendo chaves many-to-many e one-to-many)
		$errors = array();
		foreach($table as $field => $prop) {
			if((isset($prop['not-null']) && $prop['not-null'] == 'true') && $field != $class->oTable->sequence_key) {
				if(!isset($class->$field) || $class->$field == '') {
					$errors[$field] = false;
				}
			} else {
				$errors[$field] = true;
			}
			/*
			if(isset($prop['foreign'])) {
				if($prop['foreign'] == false || ($prop['foreign'] && $prop['type'] == 'many-to-one')) {
					
				}
			}
			*/
		}
		
		foreach($errors as $key => $value) {
			if($value === false) {
				reset($errors);
				return $errors;
			}
		}
		return true;
	}
}

?>