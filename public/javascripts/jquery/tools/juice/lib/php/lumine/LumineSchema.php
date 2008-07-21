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
* Class that write the database tables based on mapping files
* With this class, you can create first your mapping to latter create the database based on maps
* without write the SQL code
* @package Lumine
* @author Hugo Ferreira da Silva
* @access public
*/
class LumineSchema {
	/** @var object Connetion to the database */
	var $cn = false;
	/** @var string XML file of configuration file */
	var $xml;
	/** @var array List of classes found */
	var $classes = array();

	/**
	* Initiates a new LumineSchema class
	* @param string $xml the xml configuration file
	* @author Hugo Ferreira da Silva
	* @access public
	*/
	function LumineSchema ($xml) {
		if(!file_exists($xml)) {
			LumineLog::logger(3,'Arquivo de configuração ' . $xml . ' não existe!', __FILE__, __LINE__);
			$this->dienice( "Arquivo de configuração ($xml) não existe!" );
		}
		$this->xml = $xml;
	}
	
	/**
	* Create the database tables
	* <code>
	* require_once 'pah_to_lumine/LumineSchema.php';
	* $schema = new LumineSchema('your-conf-file.xml');
	* $schema->createSchema();
	* </code>
	* @author Hugo Ferreira da Silva
	* @access public
	* @return boolean True on success
	*/
	function createSchema() {
		LumineLog::logger(1,'Criando nova instancia do XML DOMIT', __FILE__, __LINE__);
		$doc =& new DOMIT_Document();
		$doc->resolveErrors( true );
		if( !$doc->loadXML($this->xml) ) {
			LumineLog::logger(3,'Arquivo de configuração com erros: ' . $doc->getErrorString(), __FILE__, __LINE__);
			$this->dienice( "Erro no XML: " . $doc->getErrorString() );
		}
		
		// item de configração
		LumineLog::logger(1,'Verificando o bloco de configuração', __FILE__, __LINE__);
		$item =& $doc->getElementsByPath("/lumine-configuration/configuration", 1);
		if(!$item) {
			LumineLog::logger(3,'Bloco de configuração não encontrado', __FILE__, __LINE__);
			$this->dienice( "Elemento de configuração (<strong>configuration</strong>) não encontrado!" );
		}
		
		// pega as configurações
		LumineLog::logger(1,'Analisando itens da configuração', __FILE__, __LINE__);
		$config=array();
		for($i=0; $i<count($item->childNodes); $i++) {
			if($item->childNodes[$i]->nodeType == 1) {
				$key = $item->childNodes[$i]->nodeName;
				$config[$key] = $item->childNodes[$i]->firstChild->nodeValue;
			}
		}
		
		// mapeamentos
		LumineLog::logger(1,'Analisando mapeamentos', __FILE__, __LINE__);
		$maplist =& $doc->getElementsByPath("/lumine-configuration/mapping", 1);
		if(!$maplist) {
			LumineLog::logger(3,'Bloco de mapeamentos não encontrado', __FILE__, __LINE__);
			$this->dienice( "Bloco de mapemaento de entidades não encontrado" );
		}
		// checa os itens do mapeamento
		LumineLog::logger(1,'Solicitando itens dos mapeamentos', __FILE__, __LINE__);
		$maps =& $maplist->getElementsByPath("item");

		if($maps->getLength() == 0) {
			LumineLog::logger(3,'Não há mapeamentos para serem criados no banco', __FILE__, __LINE__);
			$this->dienice("Não há mapeamentos dentro da lista de mapas");
		}
		
		// checa se os arquivos informados existem
		LumineLog::logger(1,'Checando existencia de itens', __FILE__, __LINE__);
		$files = array();
		for($i=0; $i<$maps->getLength(); $i++) {
			$it = &$maps->item( $i );
			$file = $config['class-path'] . "/" . str_replace(".", "/", $it->getAttribute("src")) . ".xml";
			if(!file_exists($file)) {
				LumineLog::logger(3,'Arquivo de mapeamento ' . $file . ' não existe!', __FILE__, __LINE__);
				$this->dienice("O arquivo de confiração do mapeamento <strong>$file</strong> não existe");
			}
			
			// checa o xml do mapeamento para ver se não há erros
			LumineLog::logger(1,'Checando mapeamento: ' . $file, __FILE__, __LINE__);
			$xmlMap = &new DOMIT_Document();
			$xmlMap->resolveErrors( true );
			if(!$xmlMap->loadXML( $file )) {
				LumineLog::logger(3,'Arquivo de configuração ' . $file . ' com erros: ' . $xmlMap->getErrorString(), __FILE__, __LINE__);
				$this->dienice("Erro no arquivo de configuração <strong>$file</strong>: " . $xmlMap->getErrorString());
			}
			
			// carrega o xml de cada um e coloca numa matriz
			$files[$file] =& $xmlMap;
		}
		
		// recupera os tipo
		require_once LUMINE_INCLUDE_PATH.'/LumineTypes.php';
		LumineTypes::start( $config['dialect'] );

		// checa se o dialeto não foi informado		
		if($config['dialect'] == '') {
			LumineLog::logger(3,'Dialeto não informado para conexão com o banco', __FILE__, __LINE__);
			$this->dienice( "Dialeto não informado" );
		}
		
		// checa se o dialeto existe
		LumineLog::logger(1,'Incluindo arquivo de conexão com o banco: ' . $config['dialect'] . '.php', __FILE__, __LINE__);
		// importa a classe ADODB
		include_once LUMINE_INCLUDE_PATH.'/adodb/adodb.inc.php';
		// se não conseguir criar a instância do banco
		if(!($conn = &ADONewConnection($config['dialect']))) {
			LumineLog::logger(3,'Classe de conexão <i>'.$config['dialect'].'</i> não encontrada!', __FILE__, __LINE__);
			exit;
		}
		$this->cn = &$conn;
		$this->cn->SetFetchMode(ADODB_FETCH_ASSOC);
		if(!defined('LUMINE_RANDOM_FUNC')) {
			define('LUMINE_RANDOM_FUNC', $this->cn->random);
		}
		
		$ifschema = '';
		// verifica se foi informado o schema
		if(isset($config['schema']) && $config['schema'] != '') {
			// então cria o schema no banco
			$this->cn->query("CREATE SCHEMA " . $config['schema']);
			$ifschema = $config['schema'] . '.';
		} else if(isset($config['schema-authorization']) && $config['schema-authorization'] != '') { // verifica se foi informado o schema-authorization
			// então cria o schema no banco
			$this->cn->query("CREATE SCHEMA AUTHORIZATION " . $config['schema-authorization']);
			$ifschema = $config['schema-authorization'].'.';
		}
		
		// se o schema foi informado, mudamos para ele
		if($ifschema != '') {
			$this->cn->query("SET search_path TO " . substr($ifschema, 0, strlen($ifschema)-1));
		}


		// inicia a escrita das tabelas
		$td = &$this->classes;
		
		foreach($files as $file => $entity) {
			$first =& $entity->getElementsByPath("/lumine-map", 1);
			if(!$first) {
				$this->dienice("Elemento de definição incorreto para <strong>$file</strong>");
			}
			
			// table definition
			$table = $first->getAttribute("table");
			$td[$table]['class'] = $first->getAttribute("class");
			$td[$table]['tablename'] = $table;
			$td[$table]['extends'] = $first->getAttribute("extends");

			$id =& $first->getElementsByPath("id", 1);
			if($id) {
				if($id->getAttribute("name") == '') {
					$this->dienice("O atributo <strong>name</strong> deve ser informado na definição da chave de sequencia do arquivo <strong>$file</strong>");
				}
				$generator =& $id->getElementsByPath("generator", 1);
				
				$column = @$id->getAttribute("column")==''?$id->getAttribute("name"):$id->getAttribute('column');
				$name = $id->getAttribute("name");

				//**********************************************************
				//** classes extendidas 
				//**********************************************************
				if(!$generator && $id->hasAttribute("linkOn")) {
					$td[$table]['primary-keys'][] = $column;
					
					$fk =& $this->_getDefinitionByClass( $td[$table]['extends'] );
					$fd =& $this->_getFieldDefinition( $fk, $id->getAttribute('linkOn'));
					
					$td[$table]['foreign-keys'][$name]['linkOn'] = $fd['column'];
					$td[$table]['foreign-keys'][$name]['references'] = $fk['tablename'];
					$td[$table]['foreign-keys'][$name]['ondelete'] = 'cascade';
					$td[$table]['foreign-keys'][$name]['onupdate'] = 'cascade';
					$td[$table]['foreign-keys'][$name]['not-null'] = true;
					
				} else if($generator) {
					$td[$table]['sequence-key']['column'] = $column;
					$td[$table]['sequence-key']['name'] = $id->getAttribute('name');
					$td[$table]['sequence-key']['generator'] = $generator->getAttribute("class");
					$td[$table]['primary-keys'][] = $column;
				} else {
					LumineLog::logger(3,"Erro de definição de $file", __FILE__,__LINE__);
					$this->dienice("não foi informado se a classe extende outra ou ao menos o gerador de sequencia desta classe");
				}
			}
			
			
			// propriedades
			$prop =& $first->getElementsByPath("property");
			for($i=0; $i<$prop->getLength(); $i++) {
				$node = &$prop->item( $i );
				
				$name = $node->getAttribute("name");
				$type = $node->getAttribute("type");
				$notnull = $node->getAttribute("not-null")=='true' ? true : false;
				$primary = $node->getAttribute("primary-key")=='true' ? true : false;
				$default = $node->getAttribute("default");
				$column = $node->getAttribute("column") == ''? $name : $node->getAttribute("column");
				
				if($name == '') {
					$this->dienice( "O atributo <strong>name</strong> deve ser informado no elemento $i do arquivo <strong>$file</strong>");
				}
				if($type == '') {
					$this->dienice("Você deve informa o tipo da coluna");
				}
				
				$td[$table]['columns'][$name]['column'] = $column;
				$td[$table]['columns'][$name]['type'] = $type;
				if($default != '') {
					$td[$table]['columns'][$name]['default'] = $default;
				}
				if($notnull == true) {
					$td[$table]['columns'][$name]['not-null'] = true;
				}
				if($primary == true) {
					$td[$table]['primary-keys'][] = $column;
				}
			}
		}
		
		// reinicia a matriz, para procurarmos por chaves estrangeiras
		reset($files);
		// many-to-many relationships
		$many_to_many = array();
		$chaves = array_keys($files);
		
		foreach($files as $file => $entity) {
		
			// procura many-to-one
			$first = &$entity->getElementsByPath("/lumine-map", 1);
			$table = $first->getAttribute("table");
			
			// chaves many-to-one
			$mto =& $first->getElementsByPath("many-to-one");
			for($i=0; $i<$mto->getLength(); $i++) {
				$node = &$mto->item( $i );
				$class =& $node->getElementsByPath("class", 1);
				//echo "$file: " . $class->getAttribute("name") . "<br>";
		
				if(!$class) {
					$this->dienice("A classe para a chave estrangeira <strong>$name</strong> não foi informada no arquivo <strong>$file</strong>");
				}
				
				$fk = $this->_getDefinitionByClass($class->getAttribute("name"));
				
				$name = $node->getAttribute("name");
				$reftable = $fk['tablename'];
				$linkOn = $class->getAttribute("linkOn");
				$column = $class->getAttribute("column");
				$ondelete = $class->getAttribute("ondelete");
				$onupdate = $class->getAttribute("onupdate");
				$notnull = $class->getAttribute("not-null") == 'true';
				
				//echo "$table, $name, $reftable, $linkOn, $column, $ondelete, $onupdate, $notnull<br>";
				
				if( $node->getAttribute("primary-key") == 'true' ) {
					$td[$table]['primary-keys'][] = $column;
				}
				
				$td[$table]['foreign-keys'][$name]['column'] = $column;
				
				if($linkOn == $fk['sequence-key']['column'] || $linkOn == $fk['sequence-key']['name']) {
					$td[$table]['foreign-keys'][$name]['type'] = SEQUENCE_TYPE;
					$linkOn = $fk['sequence-key']['column'];
				} else {
					$td[$table]['foreign-keys'][$name]['type'] = $fd['type'];
					$linkOn = $fd['column'];
				}
				
				$td[$table]['foreign-keys'][$name]['linkOn'] = $linkOn;
				$td[$table]['foreign-keys'][$name]['references'] = $reftable;
				$td[$table]['foreign-keys'][$name]['ondelete'] = $ondelete;
				$td[$table]['foreign-keys'][$name]['onupdate'] = $onupdate;
				$td[$table]['foreign-keys'][$name]['not-null'] = $notnull;
			}
			
			// procurando por chaves many-to-many
			$mtm = $first->getElementsByPath("many-to-many");
			for($i=0; $i<$mtm->getLength(); $i++) {
				$node = &$mtm->item( $i );
				
				$class = $node->getElementsByPath("class", 1);
				if(!$class) {
					$this->dienice("O elemento <strong>class</strong> necessário para chaves many-to-many não foi encontrado no arquivo <strong>$file</strong>");
				}
				
				$name = $node->getAttribute("name");
				$classname = $class->getAttribute("name");
				$linkOn = $class->getAttribute("linkOn");
				$reftable = $node->getAttribute("table");
				
				if($name=='') {
					$this->dienice("O atributo <strong>name</strong> para criação da chave many-to-many no arquivo <strong>$file</strong> deve ser informado");
				}
				if($classname == '') {
					$this->dienice("O atributo <strong>class.name</strong> para criação da chave many-to-many no arquivo <strong>$file</strong> deve ser informado");
				}
				if($linkOn == '') {
					$this->dienice("O atributo <strong>class.linkOn</strong> para criação da chave many-to-many no arquivo <strong>$file</strong> deve ser informado");
				}
				if($reftable == '') {
					$this->dienice("O atributo <strong>class.table</strong> para criação da chave many-to-many no arquivo <strong>$file</strong> deve ser informado");
				}
				
				$fk = $this->_getDefinitionByClass( $classname );
				
				$c = array();
				$c['column'] = $fk['sequence-key']['column'] || $column == $fk['sequence-key']['name'] ? $fk['sequence-key']['column'] : $linkOn;

				// if($linkOn == $fk['sequence-key']['column'] || $linkOn == $fk['sequence-key']['name']) {
				$c['type'] = SEQUENCE_TYPE;
				// } else {
					//$c['type'] = $fk['columns'][$column]['type'];
				// }
				$c['references'] = $fk['tablename'];
				$c['ondelete'] = $class->getAttribute("ondelete");
				$c['onupdate'] = $class->getAttribute("onupdate");
				$many_to_many[$reftable][] = $c;
				
			}
		}
		
		// agora começaremos a criar as tabelas
		$tables = array();
		$alters = array();
		foreach($this->classes as $name => $def) {
			$sql = "CREATE TABLE $ifschema$name (";
			
			// sequence key
			if(isset($def['sequence-key']) && $def['sequence-key']) {
				$sql .= $def['sequence-key']['column'] . ' ' . SEQUENCE_DEFINITION . ', ';
			}

			// foreign keys
			if(isset($def['foreign-keys']) && is_array($def['foreign-keys'])) {
				foreach($def['foreign-keys'] as $fkname => $fkp) {
					if(isset($fkp['column'])) {
						$sql .= $fkp['column'] . ' ' . $fkp['type'];
					} else if(isset($fkp['linkOn']) && isset($def['primary-keys']) && in_array($fkname, $def['primary-keys'])) {
						$class_tmp = $this->classes[ $fkp['references'] ];
						$fk_tmp = $this->_getFieldDefinition( $class_tmp, $fkp['linkOn'] );
						$sql .= $fkname . ' ' . SEQUENCE_TYPE;

					}
				
					if(isset($fkp['not-null']) && $fkp['not-null']) {
						$sql .= ' not null';
					}
					$sql .= ', ' ;
				}
			}
			
			// campos 
			if(is_array($def['columns'])) {
				foreach($def['columns'] as $cname => $cdef) {
					$sql .= $cdef['column'] . ' ' . $cdef['type'];
					if(isset($cdef['not-null']) && $cdef['not-null'] == true) {
						$sql .= ' not null';
					}
					$sql .= ', ';
				}
			}
			
			// colocando os INDEX para chaves estrangeiras
			if(isset($def['foreign-keys']) && is_array($def['foreign-keys'])) {
				reset($def['foreign-keys']);
				foreach($def['foreign-keys'] as $fname => $fkp) {
					if(CREATE_INDEX_FOR_FK) {
						$sql .= "INDEX `FK_" . strtoupper(substr(md5(rand(0,time())), 0, 10)) . "`({$fkp['column']})";
						$sql .= ', ';
					}
					
					$c = strtoupper(substr(md5(rand(0,time())), 0, 15));
					if(!isset($fkp['column'])) {
						$fkp['column'] = $fname;
					}
					$alter = "ALTER TABLE $ifschema{$name} ADD FOREIGN KEY({$fkp['column']}) REFERENCES $ifschema{$fkp['references']}({$fkp['linkOn']})";
					if($fkp['ondelete'] != '') {
						$alter .= " ON DELETE {$fkp['ondelete']}";
					}
					if($fkp['onupdate'] != '') {
						$alter .= " ON UPDATE {$fkp['onupdate']}";
					}
					
					$alters[] = $alter;
				}
			}
			
			// colocando as primary keys
			if(isset($def['primary-keys']) && is_array($def['primary-keys'])) {
				$sql .= "PRIMARY KEY(";
				foreach($def['primary-keys'] as $pname) {
					$sql .= $pname . ", ";
				}
				$sql = substr($sql, 0, strlen($sql) - 2);
				$sql .= ")";
			}
			
			if(substr($sql, strlen($sql) - 2) == ', ') {
				$sql = substr($sql, 0, strlen($sql) - 2);
			}
			
			$sql .= ") " . TABLE_DEFINITION;
			$tables[$name] = $sql;
		}
		
		// agora, as many-to-many
		foreach($many_to_many as $name => $def) {
			$sql = "CREATE TABLE $ifschema$name (";
			foreach($def as $d) {
				$sql .= $d['column'] .' '. $d['type'] . ' not null, ';
			}
			$sql .= "PRIMARY KEY(";
			
			reset($def);
			foreach($def as $d) {
				$sql .= $d['column'] . ', ';
			}
			
			$sql = substr($sql, 0, strlen($sql) - 2);
			$sql .= "), ";
			
			reset($def);
			foreach($def as $d) {
				$x = strtoupper(substr(md5(rand(0,time())), 0, 15));
				if(CREATE_INDEX_FOR_FK) {
					$sql .= "INDEX ({$d['column']}), ";
				}
				
				$alter = "ALTER TABLE " . $ifschema.$name . " ADD FOREIGN KEY({$d['column']}) REFERENCES $ifschema{$d['references']}({$d['column']})";
				if($d['ondelete'] != '') {
					$alter .= " ON DELETE {$d['ondelete']}";
				}
				if($d['onupdate'] != '') {
					$alter .= " ON UPDATE {$d['onupdate']}";
				}
				$alters[] = $alter;
			}
			$sql = substr($sql, 0, strlen($sql) - 2);
			$sql .= ") " . TABLE_DEFINITION;
			$tables[$name] = $sql;
		}
		
		// agora, cria no banco de dados
		
		/////// PARA MYSQL (POR ENQUANTO)
		$this->cn->connect($config['host'],$config['user'],$config['password'],$config['database']);
		$fkc = LumineTypes::disableForeignKeys( $config['dialect'] );
		if($fkc != '') {
			$this->cn->query( $fck );
		}
		
		$TablesInDatabase = $this->cn->MetaTables();
		
		foreach($tables as $name => $sql) {
			if(in_array($name, $TablesInDatabase)) {
				LumineLog::logger(1,'Dropando tabela existente: '.$ifschema.$name, __FILE__,__LINE__);
				$this->cn->query("DROP TABLE " . $name);
			}
			LumineLog::logger(1,'Executando SQL: '.$sql, __FILE__,__LINE__);
			$this->cn->query($sql);
		}
		foreach($alters as $sql) {
			LumineLog::logger(1,'Executando SQL: '.$sql, __FILE__,__LINE__);
			$this->cn->query($sql);
		}
		
		// se optou por criar as classes
		if($config['create-classes'] == 1) {
			LumineLog::logger(1,'Chamando rotina de criação das classes',__FILE__,__LINE__);
			$this->_createClasses ( $maps, $config );
		}
		
		// terminado
		return true;
	}
	
	/**
	* Get the definition of entity by class name
	* @author Hugo Ferreira da Silva
	* @access protected
	*/
	function _getDefinitionByClass($classname) {
		foreach($this->classes as $class) {
			if($class['class'] == $classname) {
				reset($this->classes);
				return $class;
			}
		}
		return false;
	}
	/**
	* Get the definition of field
	* @author Hugo Ferreira da Silva
	* @access private
	*/
	function _getFieldDefinition(&$entity, $fieldname) {
		foreach($entity['columns'] as $name => $prop) {
			if($name == $fieldname) {
				reset($entity['columns']);
				return $prop;
			}
		}
		if($fieldname == $entity['sequence-key']['column']) {
			return $entity['sequence-key'];
		}
		return false;
	}
	
	/**
	* Make the die window more beauty ;)
	* @author Hugo Ferreira da Silva
	* @access public
	*/
	function dienice($msg) {
		echo $msg;
		exit;
	}
	
	/** 
	* Create the classes from XML maps
	* @author Hugo Ferreira da Silva
	* @return void
	* @access private
	*/
	function _createClasses( &$maps, &$config ) {
		$ini = "#### START AUTOCODE\r\n";
		$end = "\t#### END AUTOCODE\r\n";
		
		for($i=0; $i<$maps->getLength(); $i++) {
			$item =& $maps->item( $i );

			$xml = $item->getAttribute("src");
			$xml = $config['class-path'].'/'.str_replace(".","/",$xml).".xml";
			
			$vars = '';
			
			
			$item =& new DOMIT_Document();
			$item->loadXML( $xml );
			
			$first =& $item->getElementsByPath("/lumine-map", 1);
			$d['extends'] = $first->getAttribute("extends");
			$d['table'] = $first->getAttribute("table");
			$d['className'] = array_pop(explode('.',$first->getAttribute("class")));
			$d['class'] = $first->getAttribute('class');
			
			$vars .= "\tvar \$__tablename = '".$d['table']."';\r\n";
			$vars .= "\tvar \$__database = '".$config['database']."';\r\n";
			
			/* campo ID */
			$id =& $item->getElementsByPath("id", 1);
			if($id != null || $id != false) {
				$vars .= "\tvar \$".$id->getAttribute("name")."; \r\n";
			}
			
			/* Propriedades normais */
			$props =& $item->getElementsByPath("/lumine-map/property");
			for($j=0; $j<$props->getLength(); $j++) {
				$p=&$props->item($j);
				$vars .= "\tvar \$" . $p->getAttribute("name") . "; \r\n";
			}
			
			/* manby-to-one */
			$otm = &$item->getElementsByPath("/lumine-map/many-to-one");
			for($j=0; $j<$otm->getLength(); $j++) {
				$o=$otm->item($j);
				$vars .= "\tvar \$".$o->getAttribute("name")."; // many-to-one\r\n";
			}
			
			/* one-to-many */
			$otm = &$item->getElementsByPath("/lumine-map/one-to-many");
			for($j=0; $j<$otm->getLength(); $j++) {
				$o=$otm->item($j);
				$vars .= "\tvar \$".$o->getAttribute("name")." = array(); // one-to-many\r\n";
			}

			/* many-to-many */
			$otm = &$item->getElementsByPath("/lumine-map/many-to-many");
			for($j=0; $j<$otm->getLength(); $j++) {
				$o=$otm->item($j);
				$vars .= "\tvar \$".$o->getAttribute("name")." = array(); // many-to-many\r\n";
			}
			
			/* cabeçalho da classe */
			$cima = "/**\r\n";
			$cima .= "* Auto-generated by LumineSchema\r\n";
			$cima .= "* @author Hugo Ferreira da Silva\r\n";
			$cima .= "* class for " . $d['class'] . "\r\n";
			$cima .= "*/\r\n";
			
			
			if(isset($config['extends_location']) && $config['extends_location'] != '') {
				$cima .= "require_once '" . $config['extends_location'] . "';\r\n";
			}
			
			$cima .= "class {$d['className']} extends " . (!isset($config['extends']) || $config['extends']==''?'LumineBase':$config['extends']) . " {\r\n";
			$cima .= $vars;
			
			$final = "";
			$final .= "}\r\n";
			$final .= "?>";
			
			$file = $config['class-path'] . "/" . str_replace(".","/",$d['class']) . ".php";
			if(file_exists($file)) {
				$class = file_get_contents($file);
				$class = preg_replace("/$ini(.*?)$end/s","$ini$cima$end", $class);
			} else {
				$class = "<?php\r\n" . $ini . $cima . $end . $final;
			}
			
			$fp=fopen($file,"w");
			fwrite($fp,$class);
			fclose($fp);
		}
	}
}

?>