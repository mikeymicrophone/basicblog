<?php
/**
* @copyright (C) 2005 Hugo Ferreira da Silva. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author Hugo Ferreira da Silva <eu@hufersil.com.br>
* @link http://www.hufersil.com.br/lumine/ Lumine Project
* Lumine is Free Software
**/

if(!defined("LUMINE_INCLUDE_PATH")) {
	/** Dinamycally defines the Lumine include path */
	define("LUMINE_INCLUDE_PATH", dirname(__FILE__) . '/');
}

if(!class_exists('LumineConfiguration')) {
	require_once LUMINE_INCLUDE_PATH . 'LumineConfiguration.php';
}
/**
* Class for reverse engineering
* 
* <code>
* $params['class-path'] = '/my/application/root/pah';
* $params['maps'] = 'my/map/folder/insied/class-path';
* $params['package'] = 'com.domain.packagename';
* $params['dialect'] = 'MySQLInnodb'; // PostgreSQL is also available
* $params['user'] = 'root';
* $params['pass'] = '*******';
* $params['database'] = 'databasename';
* $params['create-classes'] = 1;
* $params['generate-accessors'] = 1;
* $params['create-maps'] = 1;
* 
* require 'path/to/lumine/LumineReverse.php';
* $oReverse = new LumineReverse($params);
* $oReverse->doReverse();
* </code>
* @author Hugo Ferreira da silva
* @access public
*/
class LumineReverse {
	/** @var object $cn Holds the connection to the database */
	var $cn = false;
	/** @var array List of parameters to reverse engineering */
	var $params;
	
	/**
	* Creates the new instance for LumineReverse
	* @author Hugo Ferreira da Silva
	* @access public
	*/
	function LumineReverse (&$params) {
		if(array_key_exists('dialect', $params) == true) {
			include_once LUMINE_INCLUDE_PATH . '/adodb/adodb.inc.php';
			$cn= &ADONewConnection( $params['dialect'] );
			$this->cn = &$cn;
		} else {
			LumineLog::logger(3, 'Dialeto não informado', __FILE__, __LINE__);
			echo 'Você deve informar o dialeto para ser usado na engenharia reversa';
			exit;
		}
		if($params['class-path'] == '') {
			LumineLog::logger(3, 'Class-path não inforamado', __FILE__, __LINE__);
			echo 'Você deve informar a pasta raiz da aplicação (class-path)';
			exit;
		}
		if(file_exists($params['class-path']) && !is_dir($params['class-path'])) {
			LumineLog::logger(3, 'Class-path deve ser um diretório', __FILE__, __LINE__);
			echo 'Você deve informar uma pasta para o class-path, não um diretório';
			exit;
		}
		if(!file_exists($params['class-path']) && (!isset($params['auto-create']) || $params['auto-create'] != 1)) {
			echo 'A pasta ' . $params['class-path'] . ' não existe e a opção auto-create não está habilitada';
			exit;
		}

		if(!isset($params['package']) || trim($params['package']) == '') {
			LumineLog::logger(3, 'Pacote das classes não informado', __FILE__, __LINE__);
			echo 'Você deve informar o pacote das classes';
			exit;
		}
		if($params['maps'] == '') {
			LumineLog::logger(1, 'Diretório para mapeamentos não informado; utilizando o mesmo das classes', __FILE__, __LINE__);
			$params['maps'] = $params['package'];
		}
		$this->params = &$params;
	}
	
	/**
	* Executes the reverse engineering proccess
	*
	* @author Hugo Ferreira da Silva
	* @access public
	*/
	function doReverse () {
		$db = &$this->cn;
		LumineLog::logger(1, 'Efetuando conexão com o banco', __FILE__, __LINE__);
		$params = &$this->params;
		
		$db->connect($params['host'],$params['user'],$params['password'], $params['database']);

		// pega as tabelas
		LumineLog::logger(1, 'Pegando as tabelas do banco', __FILE__, __LINE__);
		$tables = $db->MetaTables();
		
		// relacionamentos muitos-pra-muitos, para não gerarmos estes tipos de entidades
		$many_to_many = array();
		
		// verificamos os relacionamentos many-to-many
		// para tal, é o nome de duas tabelas separados por underscore (table1_table2, persons_itens, etc...)
		LumineLog::logger(1, 'Analizando tabelas que servem de relacionamento many-to-many ', __FILE__, __LINE__);
		for($i=0; $i<count($tables); $i++) {
			for($j=0; $j<count($tables); $j++) {
				$rel = $tables[$i].'_'.$tables[$j];
				if(in_array($rel, $tables)) {
					if(!array_key_exists($rel, $many_to_many)) {
						$many_to_many[] = $rel;
					}
					continue;
				}
			}
		}
		
		$entities = array();
		// agora iremos criar a configuracao para cada entidade que não seja many-to-many
		foreach($tables as $table) {
			if(!in_array($table, $many_to_many)) {
				LumineLog::logger(1, 'Analizando relacionamentos many-to-one e one-to-many de ' . $table, __FILE__, __LINE__);
				$entities[$table]['class'] = $params['package'] . "." . ucfirst($this->removePrefix($table));
				$entities[$table]['fields'] = $this->obj2array( $db->MetaColumns($table) );
				
				$mto = $db->MetaForeignKeys($table);

				if(is_array($mto)) {
					foreach($mto as $from => $links) {
						foreach($links as $link) {
							list($col, $rcol) = explode('=', $link);
							if(!isset($entities[$from]['one-to-many'])) {
								$entities[$from]['one-to-many'] = array();
							}
							$id = count($entities[$from]['one-to-many']);
							$entities[$from]['one-to-many'][$id]['class'] = $entities[$table]['class'];
							$entities[$from]['one-to-many'][$id]['column'] = $rcol;
							$entities[$from]['one-to-many'][$id]['name'] = $this->removePrefix($table) . "_list";
							$entities[$from]['one-to-many'][$id]['ref_column'] = $from;
						}
					}
				}
				
				if(is_array($mto) && count($mto) > 0) {
					$mlist = array();
					foreach($mto as $from => $links) {
						foreach($links as $link) {
							list($col, $rcol) = explode('=', $link);
							$mlist[$from]['class'] = $params['package'].'.'.ucfirst($this->removePrefix($from));
							$mlist[$from]['name'] = $this->removePrefix($from);
							$mlist[$from]['type'] = 'many-to-one';
							$mlist[$from]['references'] = $this->removePrefix($from);
							$mlist[$from]['column'] = $col;
							$mlist[$from]['ref_column'] = $rcol;
							$mlist[$from]['ondelete'] = 'NO ACTION';
							$mlist[$from]['onupdate'] = 'NO ACTION';
	
							foreach($entities[$table]['fields'] as $idf => $prop) {
								if($col == $prop['name'] && (isset($prop['primary-key']) && $prop['primary-key'] == true)) {
									$mlist[$from]['primary-key'] = true;
								}
							}
							reset($entities[$table]['fields']);
						}
					}
					$entities[$table]['many-to-one'] = $mlist;
				}
			}
		}

		// agora vejamos os many-to-many
		
		LumineLog::logger(1, 'Analizando relacionamentos many-to-many ', __FILE__, __LINE__);
		foreach($many_to_many as $mtm) {
			// pegamos as chaves estrangeiras dessa tabela
			$fk = $db->MetaForeignKeys($mtm);
			
			if(!is_array($fk)) {
				continue;
			}
			
			$first['name'] = array_shift(array_keys($fk));
			list($first['column'],$first['ref']) = explode('=', $fk[$first['name']][0]);
			
			$secon['name'] = array_pop(array_keys($fk));
			list($secon['column'],$secon['ref']) = explode('=', $fk[$secon['name']][0]);
			
			if(!isset($entities[$first['name']]['many-to-many'])) {
				$entities[$first['name']]['many-to-many'] = array();
			}
			$id = count($entities[$first['name']]['many-to-many']);
			
			$entities[$first['name']]['many-to-many'][$id]['name'] = $this->removePrefix($secon['name']);
			$entities[$first['name']]['many-to-many'][$id]['linkOn'] = $first['column'];
			$entities[$first['name']]['many-to-many'][$id]['table'] = $mtm;
			$entities[$first['name']]['many-to-many'][$id]['class'] = $entities[$secon['name']]['class'];
			$entities[$first['name']]['many-to-many'][$id]['ondelete'] = 'NO ACTION';
			$entities[$first['name']]['many-to-many'][$id]['onupdate'] = 'NO ACTION';
			
			if(!isset($entities[$secon['name']]['many-to-many'])) {
				$entities[$secon['name']]['many-to-many'] = array();
			}
			$id = count($entities[$secon['name']]['many-to-many']);
			
			$entities[$secon['name']]['many-to-many'][$id]['name'] = $this->removePrefix($first['name']);
			$entities[$secon['name']]['many-to-many'][$id]['linkOn'] = $secon['column'];
			$entities[$secon['name']]['many-to-many'][$id]['table'] = $mtm;
			$entities[$secon['name']]['many-to-many'][$id]['class'] = $entities[$first['name']]['class'];
			$entities[$secon['name']]['many-to-many'][$id]['ondelete'] = 'NO ACTION';
			$entities[$secon['name']]['many-to-many'][$id]['onupdate'] = 'NO ACTION';
		}
		
		$this->entities = &$entities;
		// faz a troca dos mto's
		foreach($this->entities as $name => $entity) {
			if(isset($entity['many-to-one']) && is_array($entity['many-to-one'])) {
				foreach($entity['many-to-one'] as $n => $mto) {
					if(isset($mto['primary-key']) && $mto['primary-key'] == true) {
						if($x = $this->getExtendedMTOField( $mto['name'], $mto['ref_column'] )) {
							$this->entities[$name]['many-to-one'][$n]['ref_column'] = $x['references'];
							$this->changeMTOFields( $mto['column'], $mto['name'] );
						}
					}
				}
			}
		}
		reset($this->entities);
		
		// campos a serem criptografados automaticamente
		$criptFields = array();
		if(isset($params['crypt-fields']) && isset($params['crypt-pass']) && $params['crypt-pass'] != '') {
			$list = explode(',',preg_replace("@(\r|\n)@","",$params['crypt-fields']));
			foreach($list as $item) {
				$item = trim($item);
				if(preg_match('@^([a-z,A-Z,0-9,_]+|\*)\.([a-z,A-Z,0-9,_]+)$@', $item)) {
					$criptFields[] = $item;
				}
			}
		}

		// temos as definições que precisamos, agora, vamos iniciar a construção do XML de cada entidade
		foreach($this->entities as $name => $entity) {
			LumineLog::logger(1, 'Construindo XML de configuração de ' . $name, __FILE__, __LINE__);
			$cname = $this->removePrefix($name);
			$xml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\r\n";
			$xml .= "<lumine-map table=\"{$name}\" class=\"{$entity['class']}\">\r\n";
			// colocamos o campo de sequencia
			foreach($entity['fields'] as $field) {
				if(isset($field['sequence-key']) && $field['sequence-key'] == true) {
					$xml .= "	<id name=\"{$field['name']}\" column=\"{$field['name']}\">\r\n";
					$xml .= "		<generator class=\"default\" />\r\n";
					$xml .= "	</id>\r\n";
					continue;
				} else {
					$pass = true;
					if(isset($entity['many-to-one']) && is_array($entity['many-to-one'])) {
						$fk = $entity['many-to-one'];
						foreach($fk as $f) {
							if($f['column'] == $field['name']) {
								$pass = false;
								continue;
							}
						}
					}
					if(!$pass) {
						continue;
					}
					$xml .= "	<property name=\"{$field['name']}\" column=\"{$field['name']}\" type=\"{$field['type']}\"";
					if(isset($field['default']) && $field['default'] != '') $xml .= " default=\"{$field['default']}\"";
					if(isset($field['primary-key']) && $field['primary-key']) $xml .= " primary-key=\"true\"";
					if(isset($field['not-null']) && $field['not-null']) $xml .= " not-null=\"true\"";

					$add = false;
					foreach($criptFields as $item) {
						list($ct, $cf) = explode('.',$item);
						if(($ct == $name || $ct=='*') && $cf==$field['name']) {
							$add = true;
							break;
						}
					}
					reset($criptFields);
					
					$xml .= " crypt=\"".($add ? "true" : "false")."\"";
					$xml .= "/>\r\n";
					
				}
			}

			if(isset($entity['many-to-one']) && is_array($entity['many-to-one'])) {
				foreach($entity['many-to-one'] as $n => $mto) {
					$c = strtolower(array_pop(explode(',', $mto['class'])));
					
					$re = $this->getColumnProperties( $entities[$this->params['remove_prefix'] . $mto['name']], $mto['column']);
					if($re && $re['type'] == 'many-to-one' && $re['name'] != $cname) {
						$ref_name = $re['name'];
					} else {
						$ref_name = $mto['ref_column'];
					}
					
					$xml .= "	<many-to-one name=\"{$mto['name']}\"". (isset($mto['primary-key']) && $mto['primary-key']==true?' primary-key="true"':''). ">\r\n";
					$xml .= "		<class name=\"{$mto['class']}\" column=\"{$mto['column']}\" linkOn=\"{$ref_name}\"";
					
					if($mto['ondelete'] != '') {
						$xml .= " ondelete=\"{$mto['ondelete']}\"";
					}
					if($mto['onupdate'] != '') {
						$xml .= " onupdate=\"{$mto['onupdate']}\"";
					}
					
					$xml .= "/>\r\n";
					$xml .= "	</many-to-one>\r\n";
				}
			}
			
			if(isset($entity['one-to-many']) && is_array($entity['one-to-many'])) {
				foreach($entity['one-to-many'] as $n => $otm) {
					$xml .= "	<one-to-many name=\"{$otm['name']}\">\r\n";
					$xml .= "		<class name=\"{$otm['class']}\" linkOn=\"{$cname}\" />\r\n";
					$xml .= "	</one-to-many>\r\n";
				}
			}
						
			if(isset($entity['many-to-many']) && is_array($entity['many-to-many'])) {
				foreach($entity['many-to-many'] as $n => $mtm) {
					if($x = $this->getExtendedMTOField($name, $mtm['linkOn'])) {
						$mtmName = $mtm['name'] . '_list';
						$mtmLinkOn = $mtm['name'];
					} else {
						$mtmName = $mtm['name'];
						$mtmLinkOn = $mtm['linkOn'];
					}
					$xml .= "	<many-to-many name=\"{$mtmName}\" table=\"{$mtm['table']}\">\r\n";
					$xml .= "		<class name=\"{$mtm['class']}\" linkOn=\"{$mtmLinkOn}\"";
					if($mtm['ondelete'] != '') {
						$xml .= " ondelete=\"{$mtm['ondelete']}\"";
					}
					if($mtm['onupdate'] != '') {
						$xml .= " onupdate=\"{$mtm['onupdate']}\"";
					}
					$xml .= " />\r\n";
					$xml .= "	</many-to-many>\r\n";
				}
			}
			
			$xml .= "</lumine-map>";
			$entities[$name]['map'] = $xml;
		}
		
		// vamos agora começar a escrever os arquivos
		if(!file_exists($params['class-path']) && $params['auto-create'] == 1) {
			Util::mkdir($params['class-path']);
		}
		
		$pack = str_replace(".","/", $params['package']);
		$maps = str_replace(".","/", $params['maps']);
		if(substr($params['class-path'], strlen($params['class-path']) - 1) == '/') {
			$pack = $params['class-path'] . $pack;
			$maps = $params['class-path'] . $maps;
			$controls = $params['class-path'] . 'controls';
		} else {
			$pack = $params['class-path'] . '/' . $pack;
			$maps = $params['class-path'] . '/' . $maps;
			$controls = $params['class-path'] . '/controls';
		}
		
		LumineLog::logger(1,'Criando diretórios', __FILE__, __LINE__);
		Util::mkdir($pack);
		Util::mkdir($maps);
		
		reset($entities);

		foreach($entities as $name => $prop) {
			// SE for para tirar um prefixo
			$cname = $this->removePrefix($name);
			if(!in_array($name, $many_to_many)) {
				// escrevendo o arquivo de mapeamento
				$file = array();
				if($params['create-maps'] ==  1) {
					Util::write($maps . '/' . ucfirst($cname) . '.xml', $prop['map']);
				}
				if(isset($params['create-controls']) && $params['create-controls'] == 1) {
					Util::mkdir($controls);
					$str = "<?php\r\n";
					$str .= "require_once '".LUMINE_INCLUDE_PATH."LumineConfiguration.php';\r\n";
					if(isset($params['file-type'])&& $params['file-type'] == 'PHP') {
						$str .= "require_once '../lumine-conf.php';\r\n";
						$strFile = '$lumineConfig';
					} else {
						$strFile = "'../lumine-conf.xml'";
					}
					$str .= "\$entidade = '{$prop['class']}';\r\n";
					$str .= "\$conf = new LumineConfiguration( " . $strFile . " );\r\n";
					$str .= "if(Util::handleAction('{$prop['class']}', @\$_REQUEST['lumineAction']) == 1 && @\$_REQUEST['lumineAction'] == 'Save') {\r\n";
					$str .= "	header(\"Location:\" . \$_SERVER['PHP_SELF']);\r\n";
					$str .= "	exit;\r\n";
					$str .= "}\r\n";
					$str .= "?>\r\n";
					$str .= file_get_contents(dirname(__FILE__)."/Templates/modelo_cima.html");
					$str .= "<?php\r\n";
					$str .= "echo Util::createForm('{$prop['class']}');\r\n";
					$str .= "echo Util::createEditList('{$prop['class']}', false, 20, sprintf('%d',@\$_REQUEST['offset']));\r\n";
					$str .= "?>";
					$str .= file_get_contents(dirname(__FILE__)."/Templates/modelo_baixo.html");
					Util::write($controls . '/' . ucfirst($cname).'.php', $str);
				}
				
				if($params['create-classes'] == 1) {
					$file['acessors'] = '';
					$file['name'] = $pack . '/' . ucfirst($cname) . '.php';
					$file['comment'] = "/** Created By LumineReverse\r\n";
					$file['comment'] .= " * in ".date("Y-m-d"). "\r\n";
					$file['comment'] .= " * @author Hugo Ferreira da Silva\r\n";
					$file['comment'] .= " * @link http://www.hufersil.com.br/lumine Lumine\r\n";
					$file['comment'] .= " */\r\n";
					$file['definition'] = "class " . ucfirst($cname) . " extends " . (!isset($params['extends']) || $params['extends'] == '' ? 'LumineBase' : $params['extends']). " {\r\n";
					if(isset($params['extends_location']) && $params['extends_location'] != '') {
						$file['extends_location'] = "require_once \"{$params['extends_location']}\";\r\n";
					} else {
						$file['extends_location'] = "\r\n";
					}
					
					$file['vars'] = "	var \$__tablename = '$name';\r\n";
					$file['vars'] .= "	var \$__database = '{$params['database']}';\r\n";
					
					foreach($prop['fields'] as $field) {
						$pass = true;
						
						if(isset($prop['many-to-one']) && is_array($prop['many-to-one'])) {
							//for($j=0, $max=count($prop['many-to-one']); $j<$max; $j++) {
							foreach($prop['many-to-one'] as $f) {
								// $f = $prop['many-to-one'][$j];
								if($field['name'] == $f['column']) {
									$pass = false;
									break;
								}
							}
						}
						if(!$pass) {
							continue;
						}
						
						$file['vars'] .= "	var \${$field['name']};\r\n";
						if(isset($params['generate-accessors']) && $params['generate-accessors'] == 1) {
							$x = ucfirst($field['name']);
							$file['acessors'] .= "	/***\r\n";
							$file['acessors'] .= "	 * Acessors for {$field['name']}\r\n";
							$file['acessors'] .= "	 ***/\r\n";
							$file['acessors'] .= "	function get$x() {\r\n";
							$file['acessors'] .= "		return \$this->{$field['name']};\r\n";
							$file['acessors'] .= "	}\r\n";
							
							$file['acessors'] .= "	function set$x(\${$field['name']}) {\r\n";
							$file['acessors'] .= "		return \$this->{$field['name']} = \${$field['name']};\r\n";
							$file['acessors'] .= "	}\r\n";
						}
					}
					
					// many-to-one
					if(isset($prop['many-to-one']) && is_array($prop['many-to-one'])) {
						foreach($prop['many-to-one'] as $mto) {
							$file['vars'] .= "	var \${$mto['references']};\t\t// many-to-one\r\n";
							if(isset($params['generate-accessors']) && $params['generate-accessors'] == 1) {
								$x = ucfirst($mto['references']);
								$file['acessors'] .= "	function get$x () {\r\n";
								$file['acessors'] .= "		return \$this->{$mto['references']};\r\n";
								$file['acessors'] .= "	}\r\n";
								
								$v = "\${$mto['references']}";
								$file['acessors'] .= "	function set$x (&$v) {\r\n";
								$file['acessors'] .= "		\$this->{$mto['references']} = $v;\r\n";
								$file['acessors'] .= "	}\r\n";
								
							}
						}
						reset($prop['many-to-one']);
					}
					// one-to-many
					if(isset($prop['one-to-many']) && is_array($prop['one-to-many'])) {
						foreach($prop['one-to-many'] as $otm) {
							$file['vars'] .= "	var \${$otm['name']} = array();\t\t// one-to-many\r\n";
							if(isset($params['generate-accessors']) && $params['generate-accessors'] == 1) {
								$x = ucfirst($otm['name']);
								$file['acessors'] .= "	function get$x () {\r\n";
								$file['acessors'] .= "		return \$this->{$otm['name']};\r\n";
								$file['acessors'] .= "	}\r\n";
								
								$v = "\${$otm['name']}";
								$file['acessors'] .= "	function set$x (&$v) {\r\n";
								$file['acessors'] .= "		\$this->{$otm['name']} = $v;\r\n";
								$file['acessors'] .= "	}\r\n";
								
								$x = substr($x, 0, strlen($x)-5);
								$file['acessors'] .= "	function add$x (&\$item) {\r\n";
								$file['acessors'] .= "		array_push(\$this->{$otm['name']},\$item);\r\n";
								$file['acessors'] .= "	}\r\n";

								$file['acessors'] .= "	function remove$x (&\$toDel) {\r\n";
								$file['acessors'] .= "		\$list = array();\r\n";
								$file['acessors'] .= "		foreach(\$this->{$otm['name']} as \$item) {\r\n";
								$file['acessors'] .= "			if(\$item != \$toDel) {\r\n";
								$file['acessors'] .= "				array_push(\$list, \$item);\r\n";
								$file['acessors'] .= "			}\r\n";
								$file['acessors'] .= "		}\r\n";
								$file['acessors'] .= "		\$this->{$otm['name']} = \$list;\r\n";
								$file['acessors'] .= "	}\r\n";
							}
						}
						reset($prop['one-to-many']);
					}
					
					// many-to-many
					if(isset($prop['many-to-many']) && is_array($prop['many-to-many'])) {
						foreach($prop['many-to-many'] as $mtm) {
							if($x = $this->getExtendedMTOField($name, $mtm['linkOn'])) {
								$file['vars'] .= "	var \${$mtm['name']}_list = array();\t\t// many-to-many\r\n";
							} else {
								$file['vars'] .= "	var \${$mtm['name']} = array();\t\t// many-to-many\r\n";
							}
							
							if(isset($params['generate-accessors']) && $params['generate-accessors'] == 1) {
								$x = ucfirst($mtm['name']);
								
								if($x = $this->getExtendedMTOField($name, $mtm['linkOn'])) {
									$x = ucfirst($this->removePrefix($mtm['name']) . '_list');
									$mName = $this->removePrefix($mtm['name']) . '_list';
								} else {
									$x = ucfirst($this->removePrefix($mtm['name']));
									$mName = $this->removePrefix($mtm['name']);
								}
								
								$file['acessors'] .= "	function get$x () {\r\n";
								$file['acessors'] .= "		return \$this->{$mName};\r\n";
								$file['acessors'] .= "	}\r\n";
								
								$v = "\${$mtm['name']}";
								$file['acessors'] .= "	function set$x (&$v) {\r\n";
								$file['acessors'] .= "		\$this->{$mName} = $v;\r\n";
								$file['acessors'] .= "	}\r\n";
								
								//$x = substr($x, 0, strlen($x)-5);
								$file['acessors'] .= "	function add$x (&\$item) {\r\n";
								$file['acessors'] .= "		array_push(\$this->{$mName},\$item);\r\n";
								$file['acessors'] .= "	}\r\n";

								$file['acessors'] .= "	function remove$x (&\$toDel) {\r\n";
								$file['acessors'] .= "		\$list = array();\r\n";
								$file['acessors'] .= "		foreach(\$this->{$mName} as \$item) {\r\n";
								$file['acessors'] .= "			if(\$item != \$toDel) {\r\n";
								$file['acessors'] .= "				array_push(\$list, \$item);\r\n";
								$file['acessors'] .= "			}\r\n";
								$file['acessors'] .= "		}\r\n";
								$file['acessors'] .= "		\$this->{$mName} = \$list;\r\n";
								$file['acessors'] .= "	}\r\n";
							}
						}
						reset($prop['many-to-many']);
					}
					
					$file['vars'] .= "\r\n\t// método estatico de recuperação\r\n";
					$file['vars'] .= "\tfunction staticGet(\$p, \$k=false) {\r\n";
					$file['vars'] .= "\t\t\$cl = new ".ucfirst($cname).";\r\n";
					$file['vars'] .= "\t\t\$cl->get(\$p, \$k);\r\n";
					$file['vars'] .= "\t\treturn \$cl;\r\n";
					$file['vars'] .= "\t}\r\n\r\n";
					
					// se é para gerar getters/setters
					$ini = "#### START AUTOCODE\r\n";
					$end = "\t#### END AUTOCODE\r\n";
					
					$class = "<?php\r\n";
					$class .= $ini;
					$class .= $file['comment'];
					$class .= $file['extends_location'];
					$class .= $file['definition'];
					$class .= $file['vars'];
					$class .= $end;
					$class .= $file['acessors'];
					$class .= "}\r\n";
					$class .= "?>";
					
					if(file_exists($file['name'])) {
						$replace = $file['comment'] . $file['extends_location'] . $file['definition'] . $file['vars'];
						
						if(!isset($params['overwrite-class']) || $params['overwrite-class'] != 1) {
							$class = file_get_contents($file['name']);
							$class = preg_replace("/$ini(.*?)$end/s", "$ini$replace$end", $class);
							Util::write($file['name'], $class);
						} else {
							Util::write($file['name'], $class);
						}
					} else {
						Util::write($file['name'], $class);
					}
				}
				$mapfile = str_replace("/",".",$params['maps']);
				$mapfile = str_replace("\\",".",$mapfile);
				$mapfile .= "."  . ucfirst($this->removePrefix($name));
					
				$mapeamentos[] = $mapfile;
			}
		}
		// inicia a escrita do arquivo de configuração
		$denied = array('file-type','acao','remove_prefix','crypt-fields');
		if(!isset($params['file-type']) || $params['file-type'] == 'XML') {
			LumineLog::logger(1, 'Criando XML de configuração Geral', __FILE__, __LINE__);
			$conf = "<lumine-configuration>\r\n";
			$conf .="	<configuration>\r\n";
			foreach($params as $key => $value) {
				if(!in_array($key, $denied)) {
					$conf .= "		<$key>$value</$key>\r\n";
				}
			}
			$conf .= "	</configuration>\r\n";
			$conf .= "	<mapping>\r\n";
			foreach($mapeamentos as $map) {
				$conf .= "		<item src=\"$map\" />\r\n";
			}
			$conf .= "	</mapping>\r\n";
			$conf .= "</lumine-configuration>";
			Util::write($params['class-path'].'/lumine-conf.xml', $conf);
			LumineLog::logger(1, 'Arquivo de configuração criado com sucesso em ' . $params['class-path'] .'/lumine-conf.xml', __FILE__, __LINE__);
		}
		if(isset($params['file-type']) && $params['file-type'] == 'PHP') {
			LumineLog::logger(1, 'Criando PHP de configuração Geral', __FILE__, __LINE__);
			$conf = "<?php\r\n";
			$conf .= "\$lumineConfig = array (\r\n";
			$conf .="	'configuration' => array (\r\n";
			$parts = array();
			foreach($params as $key => $value) {
				if(!in_array($key, $denied)) {
					$parts[] = "		'$key' => '$value'";
				}
			}
			$parts[] = "		'fileDate' => filemtime(__FILE__)";
			$conf .= implode(",\r\n", $parts)."\r\n";
			
			$conf .= "	),\r\n";
			$conf .= "	'maps' => array (\r\n";
			
			$parts = array();
			foreach($mapeamentos as $map) {
				$parts[] = "		'$map'";
			}
			$conf .= implode(",\r\n", $parts)."\r\n";
			$conf .= "	)\r\n";
			$conf .= ");\r\n";
			$conf .= "?>";
			Util::write($params['class-path'].'/lumine-conf.php', $conf);
			LumineLog::logger(1, 'Arquivo de configuração criado com sucesso em ' . $params['class-path'] .'/lumine-conf.php', __FILE__, __LINE__);
		}
		
		LumineLog::logger(1, 'Engenharia reversa completa', __FILE__, __LINE__);
	}
	
	function getExtendedMTOField( $rname, $cname ) {
		$entity = $this->entities[$rname];
		if(isset($entity['many-to-one']) && is_array($entity['many-to-one'])) {
			foreach($entity['many-to-one'] as $n => $mto) {
				if($cname == $mto['column']) {
					return $mto;
				}
			}
		}

		return false;
	}
	
	function changeMTOFields($from, $to) {
		foreach($this->entities as $name => $entity) {
			if(isset($entity['many-to-one'])) {
				foreach($entity['many-to-one'] as $num => $prop) {
					if($prop['ref_column'] == $from) {
						$this->entities[$name]['many-to-one'][$num]['ref_column'] = $to;
					}
				}
			}
		}
	//	reset($this->entities);
	}
	
	function getColumnProperties($entity, $column) {
		if(isset($entity['many-to-one']) && is_array($entity['many-to-one'])) {
			foreach($entity['many-to-one'] as $mto) {
				if($mto['column'] == $column) {
					LumineLog::logger(1, 'Retornando many-to-one para '.$column, __FILE__, __LINE__);
					return $mto;
				}
			}
		}
		// agora nos campos
		foreach($entity['fields'] as $field) {
			// se for igual
			if($field['name'] == $column) {
				//retorna
				LumineLog::logger(1, 'Retornando campo para '.$column, __FILE__, __LINE__);
				return $field;
			}
		}
		return false;
	}
	
	// converte os campos objeto para campo-array
	function obj2array($arrObjects) {
		$return = array();
		foreach($arrObjects as $obj) {
			switch($obj->type) {
				case 'int4':
				case 'int8':
					$obj->type = 'int';
				break;
				case 'float4':
				case 'float8':
					$obj->type = 'float';
				break;
			}
			$item = array();
			$item['name'] = $obj->name;
			$item['type'] = $obj->type;
			$item['primary-key'] = isset($obj->primary_key) ? (boolean)$obj->primary_key : false;
			$item['not-null'] = isset($obj->not_null) ? (boolean)$obj->not_null : false;
			$item['sequence-key'] = $this->checkForSK( $obj );
			
			$return[] = $item;
		}
		return $return;
	}
	
	function checkForSK( &$obj ) {
		return LumineTypes::checkIDField( $obj, $this->params['dialect']);
	}
	
	function removePrefix($name) {
		// SE for para tirar um prefixo
		if(isset($this->params['remove_prefix']) && $this->params['remove_prefix'] != '') {
			$cname = preg_replace('@^'.$this->params['remove_prefix'].'@i', "", $name);
		} else {
			$cname = $name;
		}
		return $cname;
	}
}

?>