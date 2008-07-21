<?php
/**
* @copyright (C) 2005 Hugo Ferreira da Silva. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author Hugo Ferreira da Silva <eu@hufersil.com.br>
* @link http://www.hufersil.com.br/lumine/ Lumine Project
* Lumine is Free Software
**/

define('LUMINE_OTM', 'one-to-many');
define('LUMINE_MTM', 'many-to-many');
define('LUMINE_MTO', 'many-to-one');

if(!defined('LUMINE_INCLUDE_PATH')) {
	/** 
	* Dinamically defines the include path for Lumine files
	*/
	define('LUMINE_INCLUDE_PATH', dirname(__FILE__) . '/');
}

/** Lumine Logger */
require_once LUMINE_INCLUDE_PATH . 'LumineLog.php';
/** Base class for all entity classes */
require_once LUMINE_INCLUDE_PATH . 'LumineBase.php';
/** DOM XML Parser */
require_once LUMINE_INCLUDE_PATH . 'domit/xml_domit_parser.php';
/** Entity class - holds the class configuration */
require_once LUMINE_INCLUDE_PATH . 'Entity.php';

/**
* Configuration for Lumine's System
* @package Lumine
* @author Hugo Ferreira da Silva
*/
class LumineConfiguration {
	/** @var array $config Holds the configuration options */
	var $config;
	/** @var array $tables List of tables (entities) */
	var $tables;
	/** @var string $id Internal ID */
	var $id;
	/** @var object $conn object connection to database */
	var $conn;
	
	/**
	* Constructor for the configuration class
	* @author Hugo Ferreira da Silva
	* @param string $confFile XML file for configuration
	* @access public
	*/
	function LumineConfiguration($confFile = null) {
		$this->tables = array();
		$this->conn = null;
		
		// se for um array
		if(is_array($confFile)) {
			// pega a parte de configura��o e coloca neste objeto
			$this->config = $confFile['configuration'];
			// se estiver definido a parte de mapeamentos e for um array
			if(isset($confFile['maps']) && is_array($confFile['maps'])) {
				// se n�o estiver usando cache ou se o arquivo de cache n�o existir
				if(!isset($this->config['use-cache'])) {
					// analisa o mapeamentos
					$this->_parseArrayMaps($confFile['maps']);
				// se estiver usando cache
				} else if(isset($this->config['use-cache']) && $this->config['use-cache'] != '') {
					// se o arquivo n�o existir
					if(!file_exists($this->config['use-cache'])) {
						// abalisa os mapeamentos
						$this->_parseArrayMaps($confFile['maps']);
						// escreve o arquivo
						$this->writeCache( $this->config['use-cache'] );
					// se o arquivo existir
					} else {
						// recupera o conteudo em forma de objeto
						$obj = unserialize( file_get_contents($this->config['use-cache']) );
						// se a data for diferente do arquivo de configura��o
						if(!isset($obj->config['fileDate']) || $obj->config['fileDate'] != $this->config['fileDate']) {
							// analisa os mapeamentos
							$this->_parseArrayMaps($confFile['maps']);
							// escreve o arquivo
							$this->writeCache( $this->config['use-cache'] );
						// mas se estiver atualizado
						} else {
							// pega os dados do arquivo
							$this->tables = &$obj->tables;
						}
					}
				}
			}
		// se n�o estiver nulo e for um arquivo existente
		} else if($confFile != null && file_exists($confFile)) {
			// cria uma nova instancia do DOMIT
			$conf = &new DOMIT_Document();
			// analisar os erros
			$conf->resolveErrors( true );
			// se h� erros no XML
			if(!$conf->loadXML ( $confFile )) {
				// exibe o erro
				echo "<pre><strong>Erro no XML de configura��o:</strong> " . $conf->getErrorString(). " (". $conf->getErrorCode().")</pre>";
				// termina a execu��o do script
				exit;
			}
			// analisa a configura��o
			$this->_parseConfiguration( $conf );
			// se o usu�rio n�o estiver usando cache
			if(!isset($this->config['use-cache']) || $this->config['use-cache'] == '') {
				// analisa os arquivos de mapeamento
				$this->_parseXMLMaps( $conf );
			// mas se estiver usando cache
			} else {
				// a data do arquivo
				$this->config['xmlDate'] = filemtime($confFile);
				// pega o arquivo indicado
				$file = $this->config['use-cache'];
				// se o arquivo existir
				if(file_exists($file)) {
					// recupera os dados do arquivo
					$obj = unserialize( file_get_contents($file) );
					// se a data de modifica��o do XML for diferente da do cache
					if(!isset($obj->config['xmlDate']) || $obj->config['xmlDate'] != $this->config['xmlDate']) {
						// analiza os mapeamentos
						$this->_parseXMLMaps( $conf );
						// cria um novo cache
						$this->writeCache( $file );
					// do contr�rio (se estiver atualizado)
					} else {
						// pega as tabelas do cache
						$this->tables =&$obj->tables;
					}
				// se o arquivo de cache n�o existir
				} else {
					// analisa os mapeamentos
					$this->_parseXMLMaps( $conf );
					// escreve o cache
					$this->writeCache( $file );
				}
			}
		} else {
			LumineLog::logger(2, 'Voc� deve informar um arquivo de configura��o', __FILE__, __LINE__);
			exit;
		}
		
		// importa o dialeto escolhido
		LumineLog::logger(1,'Incluindo arquivo de conex�o com o banco: ' . $this->config['dialect'] . '.php', __FILE__, __LINE__);
		// importa a classe ADODB
		include_once LUMINE_INCLUDE_PATH.'/adodb/adodb.inc.php';
		// se n�o conseguir criar a inst�ncia do banco
		if(!($conn = &ADONewConnection($this->config['dialect']))) {
			LumineLog::logger(3,'Classe de conex�o <i>'.$this->config['dialect'].'</i> n�o encontrada!', __FILE__, __LINE__);
			exit;
		}
		$this->conn = &$conn;
		$this->conn->SetFetchMode(ADODB_FETCH_ASSOC);
		if(!defined('LUMINE_RANDOM_FUNC')) {
			define('LUMINE_RANDOM_FUNC', $this->conn->random);
		}
		// $this->conn->debug = true;

		$this->id = md5( is_array($confFile) ? serialize($confFile) : $confFile );
		if(!isset($GLOBALS['__LumineConf'])) {
			$GLOBALS['__LumineConf'] = array();
		}
		$GLOBALS['__LumineConf'][$this->id] = &$this;
	}
	
	/**
	* Parses the configuration file provided in the constructor
	* @access private
	* @author Hugo Ferreira da Silva
	*/
	function _parseConfiguration( &$conf ) {
		
		// Configura��o do banco
		LumineLog::logger(1,'iniciando a configura��o do banco', __FILE__, __LINE__);
		$x =& $conf->getElementsByPath("/lumine-configuration/configuration");
		if($x->getLength() == 0) {
			LumineLog::logger(3, 'Se��o de configura��o n�o encontrada', __FILE__, __LINE__);
			return;
		}
		LumineLog::logger(1,'Analisando elementos de configura��o do banco', __FILE__, __LINE__);
		$node = $x->item( 0 );
		for($node = $node->firstChild; $node != null; $node = &$node->nextSibling) {
			if($node->nodeType == 1) {
				$this->config[$node->nodeName] =  isset($node->firstChild->nodeValue) ? $node->firstChild->nodeValue : '';
			}
		}

		
		if(!array_key_exists('class-path', $this->config)) LumineLog::logger(3, 'Class-Path n�o informado', __FILE__, __LINE__);
		if(!array_key_exists('host', $this->config)) LumineLog::logger(3, 'Host n�o informado', __FILE__, __LINE__);
		if(!array_key_exists('password', $this->config)) LumineLog::logger(3, 'Senha n�o informada', __FILE__, __LINE__);
		if(!array_key_exists('user', $this->config)) LumineLog::logger(3, 'Usu�rio n�o informado', __FILE__, __LINE__);
		if(!array_key_exists('port', $this->config)) LumineLog::logger(3, 'Porta de conex�o n�o informada', __FILE__, __LINE__);
	}
	
	function writeCache( $file ) {
		if($fp = fopen($file, 'wb')) {
			$cp = array();
			foreach($this->config as $key => $val) {
				if($key != 'fileDate' && $key != 'xmlDate') {
					$cp[$key] = $val;
					$this->config[$key] = str_repeat('*', strlen($val));
				}
			}
			
			fwrite($fp, serialize($this));
			fclose($fp);
			foreach($cp as $key => $val) {
				$this->config[ $key ] = $val;
			}
			return true;
		}
		return false;
	}
	
	function _parseArrayMaps(&$arr) {
		if(is_array($arr)) {
			foreach($arr as $map) {
				$file = $this->config['class-path'] . '/' . str_replace('.','/',$map) . '.xml';
				if(!file_exists($file)) {
					LumineLog::logger(3, 'Arquivo de mapeamento n�o encontrado('. $file . ')', __FILE__, __LINE__);
				} else {
					LumineLog::logger(1,'Incluindo mapeamento '.$file, __FILE__, __LINE__);
					array_push($this->tables, new Entity($file, $this));
				}
			}
		}
	}
	
	/**
	* Parses the maps of configuration file provided in the constructor
	* @access private
	* @author Hugo Ferreira da Silva
	*/
	function _parseXMLMaps (&$conf) {
		// Configura��o dos mapeamentos
		LumineLog::logger(1,'Procurando mapeamentos', __FILE__, __LINE__);
		$maps =& $conf->getElementsByPath('/lumine-configuration/mapping');
		if($maps->getLength() == 0) {
			LumineLog::logger(3,'N�o foram encontrados mapeamentos', __FILE__, __LINE__);
			return ;
		}
		
		LumineLog::logger(1,'Analisando mapeamentos...', __FILE__, __LINE__);
		$maps = $maps->item( 0 );
		for($i=0; $i<count($maps->childNodes); $i++) {
		
			$map = $maps->childNodes[ $i ];

			if($map->nodeType == 1) {
				$file = $this->config['class-path'] . '/' . str_replace(".","/", $map->getAttribute("src")) . ".xml";
				
				if(!file_exists($file)) {
					LumineLog::logger(3, 'Arquivo de mapeamento n�o encontrado('. $file . ')', __FILE__, __LINE__);
					return;
				}
				LumineLog::logger(1,'Incluindo mapeamento '.$file, __FILE__, __LINE__);
				array_push($this->tables, new Entity($file, $this));
			}
		}
	}
	
	/**
	* Get the current connection with a database
	* @access public
	* @author Hugo Ferreira da Silva
	* @return object The connection objet
	*/
	function getConnection() {
		LumineLog::logger(1, 'Retornando o objeto de conex�o utilizado', __FILE__, __LINE__);
		return $GLOBALS['__LumineConf'][$this->id]->conn;
	}
	
	/**
	* Set the connection with a database
	* @access public
	* @author Hugo Ferreira da Silva
	* @param object $cn A connection object
	*
	*/
	function setConnection (&$cn) {
		LumineLog::logger(1, 'Alterando o tipo de objeto de conex�o para ' . get_class($cn), __FILE__, __LINE__);
		$GLOBALS['__LumineConf'][$this->id]->conn = &$cn;
	}
	
	/**
	* Get's the entity by name or false if no entity with provided name was found
	*
	* @param string $eName Name of desired entity
	* @access public
	* @return mixed False on failure, Entity on success
	*/
	function getEntity($eName) {
		for($i=0, $max=count($this->tables); $i<$max; $i++) {
			if($this->tables[$i]->class == $eName) {
				LumineLog::logger(1, 'Retornando a entidade para ' . $eName, __FILE__, __LINE__);
				return $this->tables[$i];
			}
		}
		LumineLog::logger(2, 'N�o foram encontradas entidades com o nome ' . $eName, __FILE__, __LINE__);
		return false;
	}
	
	/**
	* Get's the entity by tablename or false if no entity with provided name was found
	*
	* @param string $table Tablename of desired entity
	* @access public
	* @return mixed False on failure, Entity on success
	*/
	function getEntityByTable($table) {
		for($i=0, $max=count($this->tables); $i<$max; $i++) {
			if($this->tables[$i]->tablename == $table) {
				LumineLog::logger(1, 'Retornando a entidade para a tabela <b>' . $table . '</b>', __FILE__, __LINE__);
				return $this->tables[$i];
			}
		}
		
		LumineLog::logger(2, 'N�o foram encontradas entidades para a tabela ' . $table, __FILE__, __LINE__);
		return false;
	}
}

?>