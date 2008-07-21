<?php
/**
* @package Lumine
* @copyright (C) 2005 Hugo Ferreira da Silva. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author Hugo Ferreira da Silva <eu@hufersil.com.br>
* @link http://www.hufersil.com.br/lumine/ Lumine Project
* Lumine is Free Software
**/

/** Import the Util class */
require_once LUMINE_INCLUDE_PATH . 'Util.php';
/** Import the class that handles Foreign Keys in tables that dont suport them */
require_once LUMINE_INCLUDE_PATH . 'FKHandle.php';
require_once LUMINE_INCLUDE_PATH . 'LumineTypes.php';

/**
* Base class of all entites 
*
* Every class that you may want to use <b>Lumine</b> MUST extend this class, or, if you wish to
* use another class, that class must extends this class<br>
* In other words: without extend this class, Lumine will not work
*
* @author Hugo Ferreira da Silva
* @package Lumine
*/
class LumineBase {
	/** @var Array $query This array contains the parts of query (DATA SELECT, WHERE, ORDER BY, etc...) */
	var $query = array();
	/** @var Object $conn The connection object */
	var $conn = null;
	/** @var String $__tablename Name of the table that this entity represents */
	var $__tablename = null;
	/** @var String $__database Name of the database */
	var $__database = null;
	/** @var Entity $oTable Entity object for this class */
	var $oTable = null;
	/** @var ResultSet $rs RecordSet object for resulting queries */
	var $rs = null;
	/** @var String $lastQuery Last query made */
	var $lastQuery = '';
	/** @var Number $numrows Number of rows found */
	var $numrows = 0;
	/** @var String Schema of the database, if it has */
	var $schema = '';
	
	var $_joinedClasses;
	
	var $_limit = -1;
	var $_offset = -1;
	var $_currentData;
	
	
	/**
	* LumineBase Constructor
	* Will try to automatically find a configuration and get the Entity object of this child (extended) class
	* @access public
	*/
	function LumineBase() {
		LumineLog::logger(1, 'Nova instancia de LumineBase: ' . get_class($this), __FILE__, __LINE__);
		if($this->oTable == null) {
			LumineLog::logger(1, 'Procurando mapeamento para ' . get_class($this), __FILE__, __LINE__);
			if(is_array($GLOBALS['__LumineConf'])) {
				$c = &$GLOBALS['__LumineConf'];
				foreach($c as $key => $obj) {
					$this->oTable = $obj->getEntityByTable($this->__tablename);
					$this->oTable->config = &$obj;
					if($this->oTable->id == md5($this->__tablename)) {
						if(count($c)==1 || (count($c) > 1 && $this->__database == $obj->config['database'])) {
							LumineLog::logger(1, 'Mapeamento encontrado para: ' . get_class($this), __FILE__, __LINE__);
							$this->conn = &$obj->conn;
							break;
						}
					}
				}
			}
		}
		if($this->oTable == null) {
			LumineLog::logger(3, 'Mapeamento não encontrado para ' . get_class($this), __FILE__, __LINE__);
			exit;
		}
		//$this->conn = &$this->oTable->config->conn;

		$this->query['join'] ='';
		$this->query['where'] ='';
		$this->query['groupby'] ='';
		$this->query['having'] ='';
		$this->query['orderby'] ='';
		$this->query['limit'] ='';
		$this->_joinedClasses = array();
		
		
		// checando se foi informado um schema
		$c =& $this->oTable->config->config;
		if(isset($c['schema']) && $c['schema'] != '') {
			LumineLog::logger(1,'Foi informado o schema '. $c['schema'], __FILE__, __LINE__);
			$this->schema = $c['schema'];

		} else if(isset($c['schema-authorization']) && $c['schema-authorization'] != '') {
			LumineLog::logger(1,'Foi informado o schema-authorization '. $c['schema-authorization'], __FILE__, __LINE__);
			$this->schema = $c['schema-authorization'];
		}
		
		// alterna para o schema selecionado
		if($this->schema != '') {
			LumineLog::logger(1,'Alternando para o schema '. $this->schema, __FILE__, __LINE__);
			$this->query("SET search_path TO " . $this->schema);
		}
		
		// se for uma classe extendida
		$sk = $this->oTable->getFieldProperties( $this->oTable->sequence_key );
		if(isset($this->oTable->extends) && isset($sk['linkOn'])) {
			// recupera a entidade
			$x = &Util::Import($this->oTable->extends);
			if($x !== false) {
				$p = $x->oTable->getFieldProperties( $sk['linkOn'] );
				$join = " INNER JOIN " . ($this->schema!=''?$this->schema.'.':'') .
						$x->tablename() . " ON (" . $x->tablename() . ".{$p['column']}=".$this->tablename().".{$sk['column']}) ";
				$this->query['join'] .= $join;
			}
		}
	}
	
	/**
	* Connect to database
	*
	* Try to connect to the database provided by LumineConfiguration object.<br />
	* If its already connect, use the current connection
	* @return void
	* @access Public
	*/
	function connect() {
		LumineLog::logger(1, 'Método <strong>connect</strong> invocado por ' . get_class($this), __FILE__, __LINE__);
		if($this->oTable == null) {
			LumineLog::logger(3, 'Entidade não encontrada: ' . get_class($this), __FILE__, __LINE__);
			exit;
		}
		if($this->conn == null) {
			$this->conn = &$this->oTable->config->conn;
		}
		if($this->conn->IsConnected() == false) {
			LumineLog::logger(1, 'Criando uma nova conexão em ' . get_class($this), __FILE__, __LINE__);
			$c = $this->oTable->config->config;
			$this->conn->connect($c['host'],$c['user'],$c['password'],$c['database']);
		} else {
			LumineLog::logger(1, 'Usando conexão cacheada', __FILE__, __LINE__);
		}
	}
	
	/**
	* Group the result by fields provided
	*
	* @param String $groupStr The string that contains the field's that you want 
	* @return void
	* @access Public
	*/
	function groupBy($groupstr = null) {
		LumineLog::logger(1, 'Group by em ' . get_class($this) . ': ' . $groupstr, __FILE__, __LINE__);
		if($groupstr == null) {
			$re = $this->query['groupby'];
			$this->query['groupby'] = '';
			return $re;
		}
		if( !$this->query['groupby'] ) $this->query['groupby'] = "group by $groupstr";
		else $this->query['groupby'] . ", $groupstr";
		return true;
	}
	
	
	/**
	* Limits the query
	*
	* If the second param is omitted, then only will limit<br>
	* If you supply the two parameters, then the first will be the <b>offset</b>, and the second will be the <b>limit</b>
	* @param integer $offset The offset of query resultset
	* @param integer $limit The limit of query resultset 
	* @return void
	* @access Public
	*/
	function limit($offset = -1, $limit = -1) {
		LumineLog::logger(1, 'Limit em ' . get_class($this) . ': ' . $offset . ',' . $limit, __FILE__, __LINE__);
		if($limit > -1) {
			$this->_limit = $limit;
			$this->_offset = $offset;
		} else {
			$this->_limit = $offset;
		}
	}

    /**
     * Adds a select columns
     *<code>
     * $object->selectAdd(); // resets select to nothing!
     * $object->selectAdd("*"); // default select
     * $object->selectAdd("unixtime(DATE) as udate");
     * $object->selectAdd("DATE");
     *
     * //to prepend distict:
     * $object->selectAdd('distinct ' . $object->selectAdd());
     *</code>
     * @param String $field Name of field to add
     * @access public
     * @return mixed null or old string if you reset it.
     */
	function selectAdd($field = false) {
		if($field === false) {
			LumineLog::logger(1, 'Setando para default o data select de ' . get_class($this), __FILE__, __LINE__);
			$old = isset($this->query['data_select']) ? $this->query['data_select'] : '';
			$this->query['data_select'] = '';
			return $old;
		}
		if(empty($this->query['data_select'])) {
			LumineLog::logger(1, 'Alterando o data select de ' . get_class($this) . ' para ' . $field, __FILE__, __LINE__);
			$this->query['data_select'] = $field;
		} else {
			LumineLog::logger(1, 'Alterando o data select de ' . get_class($this) . ' para ' . $field, __FILE__, __LINE__);
			$this->query['data_select'] .= ', ' . $field;
		}
	}
	
    /**
     * Adds multiple Columns or objects to select with formating.
     *<code>
     * $object->selectAs(null); // adds "table.colnameA as colnameA,table.colnameB as colnameB,......"
     *                      // note with null it will also clear the '*' default select
     * $object->selectAdd($object,'prefix_%s'); // calls $object->get_table and adds it all as
     *                  objectTableName.colnameA as prefix_colnameA
     *</code>
     * @param  object|null the object to take column names from.
     * @param  string format in sprintf format (use %s for the colname)
     * @access public
	 * @author Hugo Ferreira da Silva
     * @return void
     */
	function selectAs($entity = null, $format = '%s') {
		if($entity == null) {
			LumineLog::logger(1, 'Utilizando a própria entidade para no <strong>selectAs</strong> de ' . get_class($this), __FILE__, __LINE__);
			if(!isset($this->query['data_select']) || $this->query['data_select'] == '') {
				$this->selectAdd();
			}
			$entity = &$this;
		}
		$fields = $entity->table();
		$s = array();
		
		foreach($fields as $field => $prop) {
			if(!isset($prop['foreign']) || !$prop['foreign'] || ($prop['foreign'] && $prop['type'] == 'many-to-one')) {
				$s[] = $entity->__tablename . "." . $prop['column'] . " as " . sprintf($format, $field);
			}
		}
		if(!empty($this->query['data_select'])) {
			LumineLog::logger(1, 'Adicionando itens no dataselect através do <strong>selectAs</strong> de ' . get_class($this), __FILE__, __LINE__);
			$this->query['data_select'] .= ", " . implode("," , $s);
		} else {
			LumineLog::logger(1, 'Alterando o dataselect pelo <strong>selectAs</strong> de ' . get_class($this), __FILE__, __LINE__);
			$this->query['data_select'] = implode(", ", $s);
		}
	}
	
	/**
	* JoinAdd - join another class to this class, building a joined query
	*
	* For example, let's supose that you have two entities: person and address<br>
	* and you have one person to many addresses <br>
	* <code>
	* $ad = new Address;
	* $person = new Person;
	* $person->joinAdd($ad);
	* $person->find();
	* </code>
	* @param mixed $entity The entity that will be joinned with this class OR an array
	* @param String $type Join type INNER|LEFT|RIGHT
	* @access public
	* @return void
	* @author Hugo Ferreira da Silva
	*/
	function joinAdd(&$entity, $type='INNER', $alias = '') {
		// se entity for um array
		if(is_array($entity)) {
			$from = $entity['from'];
			$to = explode(':', $entity['to']);
			
			if($alias == '') {
				$joinString = " $type JOIN {$to[0]} ON ({$to[0]}.{$to[1]}={$this->__tablename}.$from) ";
			} else {
				$joinString = " $type JOIN {$to[0]} AS $alias ON ($alias.{$to[1]}={$this->__tablename}.$from) ";
			}
			
			// coloca este inner join
			if(empty($this->query['join'])) {
				$this->query['join'] = $joinString;
			} else {
				$this->query['join'] .= $joinString;
			}
			return true;
		}
	
		// daqui pra lá
		LumineLog::logger(1,'joinAdd chamado pela classe ' . get_class($this) . ' com ' . get_class($entity), __FILE__,__LINE__);
		$fk = $this->oTable->getForeignKeys();
		$efk = $entity->oTable->getForeignKeys();
		$joinString = '';
		
		$if_DBname = '';
		$cfg = &$entity->oTable->config->config;
		
		if(isset($cfg['join-add-database-name']) && $cfg['join-add-database-name'] == 1) {
			$if_DBname = $cfg['database'] .'.';
		}
		
		// para cada foreign key
		foreach($fk as $f=>$k) {
			// se for do tipo one-to-many
			if($k['type'] == 'one-to-many' && $k['class'] == $entity->oTable->class) {
				// pega os dados da entidade estrangeira
				$ek = $efk[$k['linkOn']];
				// pega os dados desta entiade
				$tk = $this->oTable->getFieldProperties($ek['linkOn']);
				if($alias == '') {
					$joinString .= " $type JOIN $if_DBname{$entity->oTable->tablename} on({$this->oTable->tablename}.{$tk['column']}={$entity->oTable->tablename}.{$ek['column']}) ";
				} else {
					$joinString .= " $type JOIN $if_DBname{$entity->oTable->tablename} as $alias on({$this->oTable->tablename}.{$tk['column']}=$alias.{$ek['column']}) ";
				}
				break;
			}

			if($k['type'] == 'many-to-one') {
				if($k['class'] == $entity->oTable->class) {
					$linkOn = $entity->oTable->getFieldProperties($k['linkOn']);
					if($alias == '') {
						$joinString .= " $type JOIN $if_DBname{$entity->oTable->tablename} on({$this->oTable->tablename}.{$k['column']}={$entity->oTable->tablename}.{$linkOn['column']}) ";
					} else {
						$joinString .= " $type JOIN $if_DBname{$entity->oTable->tablename} as $alias on({$this->oTable->tablename}.{$k['column']}=$alias.{$linkOn['column']}) ";
					}
					break;
				}
			}
			if($k['type'] == 'many-to-many') {
				if($k['class'] == $entity->oTable->class) {
					$x = $entity->oTable->getForeignKeys();
					foreach($x as $ename => $eprop) {
						if(isset($eprop['table']) && $eprop['table'] == $k['table'] && $entity->oTable->class == $k['class']) {
							
							$efp = $entity->oTable->getFieldProperties( $eprop['linkOn'] );
							$tfp = $this->oTable->getFieldProperties( $k['linkOn'] );
							
							$table = $this->tablename();
							$etable = $entity->tablename();
							
							$joinString = " $type JOIN  $if_DBname{$k['table']} on({$k['table']}.{$tfp['column']} = $table.{$tfp['column']}) ";
							$joinString .= " $type JOIN $if_DBname$etable on({$eprop['table']}.{$efp['column']} = $etable.{$efp['column']}) ";

							break;
						}
					}
					break;
				}
			}
		}
		// não achou
		if($joinString == '') {
			// então, de lá pra cá
			$fk = $entity->oTable->getForeignKeys();
			foreach($fk as $f=>$k) {
				if($k['type'] == 'many-to-one') {
					if($k['class'] == $this->oTable->class) {
						$x = $this->oTable->getFieldProperties( $k['linkOn'] );
						$joinString = " $type JOIN $if_DBname{$entity->oTable->tablename} on({$this->oTable->tablename}.{$x['column']}={$entity->oTable->tablename}.{$k['column']}) ";
						break;
					}
				}
			}
		}
		
//		$this->_build_condition();
		
		// coloca este inner join
		if(empty($this->query['join'])) {
			$this->query['join'] = $joinString;
		} else {
			$this->query['join'] .= $joinString;
		}
		
		// pega o join add da outra classe
		if(!empty($entity->query['join'])) {
			$this->query['join'] .= $entity->query['join'];
		}
		// pega o whereAdd da outra classe
		if(!empty($entity->query['where'])) {
			$cond = preg_replace("/\sWHERE/i","",$entity->query['where']);
			$this->whereAdd('('. $cond . ')');
		}
		
		// pega o data_select da outra classe
		if(!empty($entity->query['data_select'])) {
			$this->selectAdd($entity->query['data_select']);
		}
		
		// verifica os campos da outra entidade que não estão vazios
		// para compor neste whereAdd
		$t = $entity->tablename();
		$f = $entity->table();
		
		foreach($f as $fi => $prop) {
			if(isset($entity->$fi) && !is_array($entity->$fi)) {
				$x = $entity->fromValue($fi, $prop, true);
				$this->whereAdd("{$t}.{$prop['column']} = $x");
				/*if(is_numeric($entity->$fi)) {
					if(isset($prop['type']) && ($prop['type'] == 'bool' || $prop['type'] == 'boolean')) {
						$x = "{$t}.{$prop['column']} = '{$entity->$fi}'";
					} else {
						$x = "{$t}.{$prop['column']} = {$entity->$fi}";
					}
					$this->whereAdd($x);
				} else {
					$this->whereAdd("{$t}.{$prop['column']} = '{$entity->$fi}'");
				}*/
			}
		}
		
		$c = array_pop(explode('.', $entity->oTable->class));
		$this->_joinedClasses[ $c ] = &$entity;
		if(count($entity->_joinedClasses) > 0) {
			$this->_joinedClasses = array_merge($this->_joinedClasses, $entity->_joinedClasses);
		}
		
		// $this->_build_condition();
	}
	
	/**
	* Set/remove a where clause to your query
	*
	* If the $cond param and $logic are null, so set's the where clause to null<br>
	* @param string $cond Add the condition to where clause
	* @param string $logic Add the logic for your where condition. i.e.: OR | AND (default AND)
	* @author Hugo Ferreira da Silva
	* @access public
	*/
	function whereAdd($cond = null, $logic = "AND") {
		if($cond == null ) {
			$this->query['where'] = '';
			return;
		}
		if(empty($this->query['where'])) {
			$this->query['where'] = " WHERE {$cond} ";
		} else {
			$this->query['where'] .= " {$logic} {$cond} ";
		}
	}
	
	/**
	* Set/remove the order by of query
	*
	* If called without arguments ( $objet->orderby() ) set the order by  of query to null<br>
	* Example
	* <code>
	* $person = new Person;
	* $person->orderby("name asc");
	* $person->find();
	* </code>
	* @param string $cond Fields Order to order by 
	* @return void
	* @author Hugo Ferreira da Silva
	* @access public
	*/
	function orderBy($cond = null) {
		if($cond == null ) {
			$this->query['orderby'] = '';
			return true;
		}
		if(empty($this->query['orderby'])) {
			$this->query['orderby'] = " ORDER BY {$cond} ";
		} else {
			$this->query['orderby'] .= ", {$cond} ";
		}
		return true;
	}
	
	/**
	* Set/remove the having of query
	*
	* If called without arguments ( $objet->having() ) set the having of query to null<br>
	* Example
	* <code>
	* $emp = new Employee;
	* $emp->having("sum(salary) > 1000");
	* $emp->find();
	* </code>
	* @param string $cond Condition to having clause
	* @param string $logic Logic to merge multiple HAVING clauses
	* @return void
	* @author Hugo Ferreira da Silva
	* @access public
	*/
	function having($cond = null, $logic = "AND") {
		if($cond === null ) {
			$this->query['having'] = '';
			return;
		}
		if(empty($this->query['having'])) {
			$this->query['having'] = " HAVING {$cond} ";
		} else {
			$this->query['having'] .= " {$logic} {$cond} ";
		}
	}

	/**
	* Returns the entity fields and their definitions
	*
	* @author Hugo Ferreira da Silva
	* @access public
	* @return array
	*/
	function table( $herance = false) {
		return  $this->oTable->getColumns( $herance ) ;

	}
	/**
	* Returns the tablename of this entity
	*
	* @author Hugo Ferreira da Silva
	* @access public
	* @return string
	*/
	function tablename( $name = false ) {
		if($name != false) {
			$old = $this->__tablename;
			$this->__tablename = $name;
			
			return $old;
		}
		return $this->__tablename;
	}
	
	/**
	* Find the objects in database base on where clauses and object properties 
	*
	* <code>
	* $person = new Person;
	* $person->name = "Fred";
	* $person->find();
	* $person->fetch(); // go to first entry
	* echo $person->age;
	* </code>
	* @author Hugo Ferreira da Silva
	* @access public
	* @return number The number of objects found
	* @param boolean $autoFetch Fetch's the first row (if found) automatically
	*/
	function find( $autoFetch = false ) {
		$this->num_rows = 0;
		$this->connect();
		
		$old_query = !isset($this->query) ? array() : $this->query;
		$this->_build_condition();
		
		$c = &$this->oTable->config->config;
		if(isset($c['join-add-database-name']) && $c['join-add-database-name'] == 1) {
			$dbname = $c['database'] . '.';
		} else {
			$dbname = '';
		}
	
		$sql = "SELECT " .
			(empty($this->query['data_select']) ? '*' : $this->query['data_select']) .' '.
			"FROM " . $dbname . $this->tablename() . "\r\n" . 
			$this->query['join'] . "\r\n" . 
			$this->query['where'] . "\r\n" .
			$this->query['groupby'] . "\r\n" .
			$this->query['having'] . "\r\n" .
			$this->query['orderby'] . " "
		;
		$sql = $this->parseSQL($sql);
		
		LumineLog::logger(1,'Efetuando query: '. $sql, __FILE__,__LINE__);
		// se tiver limit
		if((integer)$this->_limit > 0) {
			$this->rs = $this->conn->SelectLimit($sql, $this->_limit, $this->_offset);
		} else {
			$this->rs = $this->conn->Execute( $sql );
		}
		$error = $this->errorMsg();
		if($error != '') {
			LumineLog::logger(3,'Erro de SQL:'. $error, __FILE__,__LINE__);
		}
		if($this->rs && $this->rs->NumRows() > 0 && $autoFetch == true) {
			LumineLog::logger(1,'Indo para a primeira linha de resultado em '. get_class($this), __FILE__,__LINE__);
			$this->fetch();
		}
		
		$this->lastQuery = $sql;
		$this->query = $old_query;
		if($this->rs) {
			$this->numrows = $this->rs->NumRows();
		}
		
		return $this->numrows;
	}
	
	/**
	* Performs a count selection
	*
	* <code>
	* $person = new Person;
	* $person->name = "Fred";
	* $total = $person->count();
	* echo $total;
	* </code>
	* @author Hugo Ferreira da Silva
	* @access public
	* @return number the number of rows
	* @param string $countWhat By default, count using *, or you can count what you want, using the parameter
	*/
	function count( $countWhat = '*' ) {
		$this->num_rows = 0;
		$this->connect();
		
		$old_query = !isset($this->query) ? array() : $this->query;
		$this->_build_condition();		
	
		$sql = "SELECT count(" . $countWhat . ") AS lumine_count " .
			"FROM " . $this->tablename() . "\r\n" . 
			$this->query['join'] . "\r\n" . 
			$this->query['where'] . "\r\n" .
			$this->query['groupby'] . "\r\n" .
			$this->query['having'] . "\r\n" .
			$this->query['orderby'] . " "
		;
		
		LumineLog::logger(1,'Efetuando query de contagem: '. $sql, __FILE__,__LINE__);
		if($this->_limit > 0) {
			$rs = $this->conn->SelectLimit( $this->parseSQL($sql), $this->_limit, $this->_offset);
		} else {
			$rs = $this->conn->Execute( $this->parseSQL($sql) );
		}
		$error = $this->errorMsg();
		if($error != '') {
			LumineLog::logger(3,'Erro de SQL:'. $error, __FILE__,__LINE__);
		}
		if($rs === false) {
			return false;
		}

		return $rs->fields['lumine_count'];
	}
	
	/**
	* Goes to next object (row) in the resultset
	*
	* @author Hugo Ferreira da Silva
	* @access public
	* @return array
	* @see LumineBase::find()
	*/
	function fetch( $getLinks = true ) {
		if(!$this->rs) {
			LumineLog::logger(1,'Consulta não realizada ou falhou '.get_class($this), __FILE__,__LINE__);
			return false;
		}
		
		if($this->rs->EOF) {
			LumineLog::logger(1,'Sem dados para recuperar '.get_class($this), __FILE__,__LINE__);
			$this->rs->MoveFirst();
			return false;
		}
		LumineLog::logger(1,'recuperando dados de '. get_class($this), __FILE__,__LINE__);
		$data = $this->rs->fields;
		
		$this->_currentData = $data;
		foreach($data as $key => $value) {
			$this->$key = $value;
			$p = $this->oTable->getColumnProperties($key);
			if($p !== false) {
				$this->$p['name'] = $this->_getDecryptValue($p, $value);
			}
		}
		
		if($getLinks == true) {
			$fks = $this->oTable->getForeignKeys();
			foreach($fks as $fk => $prop) {
				if(isset($prop['lazy']) && $prop['lazy'] == 'true') {
					LumineLog::logger(1,'Recuperando os links lazy de '. get_class($this), __FILE__,__LINE__);
					$this->$fk = $this->getLink($fk);
				}
			}
		}
		$this->rs->MoveNext();
		return true;
	}
	
	/**
	* Executes a database query
	*
	* @param string $sql SQL string to execute
	* @author Hugo Ferreira da Silva
	* @access private
	*/
	function _query($sql) {
		$this->connect();
		$x = $sql;
		switch(strtoupper($x)) {
			case "BEGIN":
				$this->conn->begin();
				return true;
			break;
			case "COMMIT":
				$this->conn->commit();
				return true;
			break;
			case "ROLLBACK":
				$this->conn->rollback();
				return true;
			break;
		}
		
		$res = $this->conn->Execute( $this->parseSQL($sql) );
		$erro = $this->errorMsg();
		if($erro != '') {
			LumineLog::logger(3,'Erro de SQL: '. $erro,__FILE__,__LINE__);
		}
		
		return $res;
	}
	
	/**
	* Executes a raw query
	* <code>
	* $person = new Person;
	* $person->query("SELECT * FROM person where name = 'John'");
	* while($person->fetch()) {
	*     echo $person->name;
	* }
	* </code>
	* @param string $sql The SQL to execute
	* @return mixed $sql Number of affected rows or false on failure
	* @author Hugo Ferreira da Silva
	* @access public
	*/
	function query($sql) {
		$this->connect();
		LumineLog::logger(1,'Executando SQL: '. $sql, __FILE__,__LINE__);
		$rs = $this->conn->Execute( $this->parseSQL($sql) );
		$error = $this->errorMsg();
		if($error != '') {
			LumineLog::logger(3,'Erro de SQL: '. $error, __FILE__,__LINE__);
		}
		if($rs === false) {
			return false;
		}
		
		$this->rs = &$rs;
		$this->numrows = method_exists($rs, 'NumRows') ? $rs->NumRows() : 0;
		return $this->numrows;
	}
	
	/**
	* Create the conditions (where clause) based on object properties
	*
	* @param array $arFields Desired field to check out
	* @author Hugo Ferreira da Silva
	* @access private
	* @return void
	*/
	function _build_condition ($arFields = false) {
		$t = $this->tablename();

		if(is_array($arFields)) {
			$f = $arFields;
		} else {
			$f = $this->table();
		}

		foreach($f as $fi => $prop) {
			if(isset($this->$fi) && !is_array($this->$fi)) {
				if(is_numeric($this->$fi)) {
					$x = $this->fromValue($fi, $prop, true);
					if(is_numeric($x)) {
						$this->whereAdd("{$t}.{$prop['column']} = {$x}");
						continue;
					} else {
						if($prop['type'] == 'bool' || $prop['type'] == 'boolean') {
							$this->whereAdd("{$t}.{$prop['column']} = " . $x);
						} else {
							$this->whereAdd("{$t}.{$prop['column']} = " .  $x );
						}
						continue;
					}
				} else {
					$v = &$this->$fi;
					if(is_a($v, "luminebase")) {
						$p = $this->oTable->getFieldProperties($fi);
						$x = $v->oTable->getColumnProperties($p['column']);
						
						$value = $v->fromValue($x['name'], $x, true);
						$this->whereAdd("{$t}.{$prop['column']} = " . $value);
					} else {
						$this->whereAdd("{$t}.{$prop['column']} = " . $this->fromValue($fi, $prop, true));
						//$this->whereAdd("{$t}.{$prop['column']} = " . $this->conn->qstr($this->$fi));
					}
				}
			}
		}
		
		// se esta classe extende alguma outra
		if(isset($this->oTable->extends)) {
			$clazz = Util::import($this->oTable->extends);
			if($clazz !== false) {
				/*>>>>>>>*/
				$t = $clazz->tablename();
		
				if(count($arFields) > 0) {
					$f = $arFields;
				} else {
					$f = $clazz->table();
				}
				
				if(!is_array($f)) {
					$f=array();
				}
	
				foreach($f as $fi => $prop) {
					if(isset($this->$fi) && !is_array($this->$fi)) {
						if(is_numeric($this->$fi)) {
							$x = $this->fromValue($fi, $prop);
							if(is_numeric($x)) {
								$this->whereAdd("{$t}.{$prop['column']} = {$x}");
							} else {
								$this->whereAdd("{$t}.{$prop['column']} = '" . $this->conn->qstr( $x ) . "'");
							}
						} else {
							$v = &$this->$fi;
							if(is_a($v, "luminebase")) {
								$p = $clazz->oTable->getFieldProperties($fi);
								$x = $v->oTable->getColumnProperties($p['column']);
								
								$value = $v->{$x['name']};
								if(is_numeric($value)) {
									$this->whereAdd("{$t}.{$prop['column']} = {$value}");
								} else {
									$this->whereAdd("{$t}.{$prop['column']} = '" . $this->conn->qstr($value) . "'");
								}
							} else {
								$this->whereAdd("{$t}.{$prop['column']} = '" . $this->conn->qstr($this->$fi) . "'");
							}
						}
					}
				}
				
				/*<<<<<<<<*/
			}
		}
	}
	
	/**
	* Set object properties from an associative array
	*
	* <code>
	* $_POST['name'] = 'John Silver';
	* $_POST['age'] = 21;
	* $_POST['city'] = 'Texas';
	*
	* $person = new Person;
	* $person->setFrom($_POST);
	* $person->save();
	* </code>
	* @author Hugo Ferreira da Silva
	* @access public
	* @return void
	*/
	function setFrom(&$ar) {
		$f = $this->table();
		foreach($f as $key => $prop) {
			if(isset($ar[$key])) {
				//$this->$key = $ar[$key];
				$method = "set".ucfirst($key);
				if(method_exists($this, $method)) {
					$this->$method( $ar[$key] );
				} else {
					$this->$key = $ar[$key];
				}
			}
		}
	}
	
	/**
	* Return the current row in a array format
	*
	* <code>
	* $person = new Person;
	* $person->get( 1 );
	* print_r($person->toArray());
	* array (
	*    [name] => 'John',
	*    [age] => 21
	* )
	* </code>
	* @author Hugo Ferreira da Silva
	* @access public
	* @return array
	*/
	function toArray($str='%s') {
		// se o cara ainda não fez uma consulta
		if(!$this->rs) {
			// cria uma nova matriz
			$vars = array();
			// pega os campos dessa entidade (inclusive herdados)
			$t = $this->table( true );
			// para cada campo
			foreach($t as $key => $prop) {
				// se não for uma chave estrageira ou se for e ser uma chave many-to-one
				if(!isset($prop['foreign']) || ($prop['foreign'] && $prop['type'] == 'many-to-one')) {
					$vars[$key] = $prop;
				}
			}
		} else {
			// do contrario, pega o toArray da consulta
			$vars = $this->_currentData;
		}
		$ar = array();
		
		if(is_array($vars)) {
			foreach($vars as $key => $value) {
				$prop = $this->oTable->getColumnProperties($key);
				$field = $prop['name'];
				$method = "get" . ucfirst($field);
				$to = sprintf($str, $key);

				if($field != '') {
					$to = sprintf($str, $field);
					if(method_exists($this, $method)) {
						$ar[$to] = $this->$method();
					} else {
						if($this->$field != '') {
							// se for uma instancia de lumine base
							if(is_a($this->$field,'LumineBase')) {
								// converte para um array também
								$ar[$to] = $this->$field->toArray();
								
								// para cada foreign key
								$fks = $this->$field->oTable->getForeignKeys();
								foreach($fks as $fk => $prop) {
									// se for o-t-m ou m-t-m
									if($prop['type'] == 'one-to-many' || $prop['type'] == 'many-to-many') {
										// para cada um
										foreach($this->$field->$fk as $item) {
											// se for objeto
											if(is_a($item, 'LumineBase')) {
												$ar[$to][$fk][] = $item->toArray();
											} else {
												$ar[$to][$fk][] = $item;
											}
										}
									} 
								}
								
								/*
								// pega o tipo o campo de link
								$f = $this->$field->oTable->getColumnProperties( $prop['linkOn'] );
								// pega o valor desse campo
								if(isset($this->$field->$f['name'])) {
									$ar[$to] = $this->$field->$f['name'];
								} else {
									$ar[$to] = '';
								}
								*/
							// se não for
							} else {
								// verifica se as propriedades não é falsa
								if($prop !== false) {
									$ar[$to] = $this->toValue($field, $prop);
								} else {
									$ar[$to] = $this->$field;
								}
	
							}
						} else {
							$ar[$to] = '';
						}
					}
				} else {
					$ar[$to] = $value;
				}
			}
			
			// agora, varre as chaves estrangeiras o-t-m e m-t-m
			$links = $this->oTable->getForeignKeys();
			foreach($links as $name => $prop) {
				if(($prop['type'] == 'one-to-many' || $prop['type'] == 'many-to-many')) {
					$to = sprintf($str, $name);
	
					if(isset($this->$name) && is_array($this->$name) && count($this->$name) > 0) {
						$ar[$to] = array();
						foreach($this->$name as $value) {
						
							if(is_a($value, 'LumineBase')) {
								// pega somente em array
								$ar[$to][] = $value->toArray();
							} else {
								// se não for, pega o valor
								$ar[$to][] = $value;
							}
						}
						reset($this->$name);
					}
					
					/* else {
						$ar[$to] = array();
					}
					*/
				}
			}
			
			return $ar;
		} else {
			return false;
		}
	}
	
	/**
	* Converts the value to a format specified in the XML map or provided in an get{Field} method of child class
	*
	* @author Hugo Ferreira da Silva
	* @access public
	* @return mixed The formated value
	*/
	function toValue( $key, $prop = null ) {
		if($prop == null) {
			return $this->$key;
		}
		if(isset($prop['format']) && isset($prop['type'])) {
			switch(strtolower($prop['type'])) {
				case "date":
					return Util::FormatDate($this->$key, $prop['format']);
				break;
				
				case "time":
					return Util::FormatTime($this->$key, $prop['format']);
				break;
				
				case "datetime":
				case "timestamp":
					return Util::FormatDateTime($this->$key, $prop['format']);
				break;
				default:
					return sprintf($prop['format'], $this->$key);
				break;
			}
		}
		
		if(isset($prop['type'])) {
			switch(strtolower($prop['type'])) {
				case 'bool':
				case 'boolean':
					return $this->$key == 't' || $this->$key == 1 || $this->$key === true ? 1 : 0;
				break;
			}
		}
		
		return $this->$key;
	}
	
	/**
	* Converts the value to a format for database storage
	*
	* @author Hugo Ferreira da Silva
	* @access public
	* @return mixed The formated value
	*/
	function fromValue($key, $prop = null, $quoteString = false) {
		if($prop == null) {
			return $this->$key;
		}
		
		if(isset($prop['type']) && $prop['type'] == 'many-to-one') {
			if(isset($prop['class'])) {
				$class = Util::Import( $prop['class'] );
				$prop = $class->oTable->getFieldProperties( $prop['linkOn'] );
			}
		}
		
		$v = '';
		$formats = "/\b(";
		$formats .= "int|integer|float|float4|float8|double|double precision|real";
		$formats .= "|bpchar|text|char|varchar|blob|longblob|tinyblob|longtext|tinytext|mediumtext|mediumblob";
		$formats .= "|date|time|datetime|timestamp";
		$formats .= "|bool|boolean";
		$formats .= "|bytea";
		$formats .= ")\b/i";
		if(isset($prop['type']) && preg_match($formats, $prop['type'], $reg) && $key != $this->oTable->sequence_key) {
			if(!is_bool($this->$key) && !is_numeric($this->$key) && empty($this->$key) && isset($this->oTable->config->config['empty-as-null']) && $this->oTable->config->config['empty-as-null'] == '1') {
				$v = 'NULL';
			} else {
				switch(strtolower($reg[1])) {
					// numeros
					case "int":
					case "integer":
						if($this->$key != 'NULL') $v = sprintf("%d", $this->$key);
					break;
					
					case "real":
					case "float":
					case "float4":
					case "float8":
					case "double":
					case "double precision":
						if($this->$key != 'NULL') $v = sprintf("%0.f",$this->$key);

					break;
					
					// strings
					case "mediumtext":
					case "bpchar":
					case "varchar":
					case "char":
					case "text":
					case "longtext":
					case "tinytext":
						if(isset($this->oTable->config->config['escape']) && $this->oTable->config->config['escape'] == 1) {
							$v = $this->escape( $this->$key );
						} else {
							$v = $this->$key;
						}
						$v = $this->_encryptValue( $v, $prop );
						if($quoteString) {
							$v = "'$v'";
						}
					break;
					
					// data
					case "date":
						if($this->$key != '') {
							if(!isset($prop['default']) || $this->$key != $prop['default']) {
								$v = Util::FormatDate($this->$key, "%Y-%m-%d");
							}
						}
						if($quoteString && $this->$key != 'NULL') {
							$v = "'$v'";
						}
					break;
					// hora
					case "time":
						if($this->$key != '' && !isset($prop['default']) || $this->$key != $prop['default']) $v = Util::FormatTime($this->$key, "%H:%M:%S");
						if($quoteString && $this->$key != 'NULL') {
							$v = "'$v'";
						}
					break;
					
					// data e hora
					case "datetime":
					case "timestamp":
						if($this->$key != '' && !isset($prop['default']) || $this->$key != $prop['default']) {
							$v = Util::FormatDateTime($this->$key, "%Y-%m-%d %H:%M:%S");
						}
						if($quoteString && $this->$key != 'NULL') {
							$v = "'$v'";
						}
					break;
					
					case "bool":
					case "boolean":
						if(is_numeric($this->$key) || $this->$key != '') {
							$v = $this->$key;
						}
						if($v !== 'false' && $v !== 'true') {
							$v = sprintf("%d", $v);
						} 
						if($v === true || $v === false) {
							$v =sprintf("%d", $v);
						}
						$v="'".$v."'";
					break;
					
					case "blob":
					case "longblob":
					case "tinyblob":
					case "bytea": // PostgreSQL
						if(isset($this->$key) && $this->$key != '') {
							$v = $this->escape( $this->$key );
						}
						if($quoteString && $this->$key != 'NULL') {
							$v = "'$v'";
						}
					break;
				}
			}
			return $v;
		
		}
		if($key == $this->oTable->sequence_key && $this->$key != '') {
			$v = intval($this->$key);
			if(!$v) $v='0';
			return $v;
		}
		return $this->$key;
	}
	
	/**
	* Insert the current objects variables into the database
	*
	* Returns the ID of the inserted element (if auto increment or sequences are used.)
	*
	* for example
	*
	* Designed to be extended
	*
	* $object = new mytable();
	* $object->name = "fred";
	* echo $object->insert();
	*
	* @access public
	* @return mixed false on failure, int when auto increment or sequence used, otherwise true on success
	*/
	function insert() {
		$this->connect();
		$t = $this->table();
		$fields = array();
		$values = array();

		// pega a sequence_key (se o valor for igual a nulo)
		$sk = $this->oTable->sequence_key;
		if(isset($this->oTable->sequence_key) && !empty($this->oTable->sequence_key) && $this->$sk == '') {
			switch($this->oTable->sequence_generator) {
				// Padrão
				case "default":
					// verifica se é uma classe extendida
					if(isset($this->oTable->extends) && $this->oTable->extends != '') {
						// tenta recuperar a classe que ela extende
						$clazz = Util::Import($this->oTable->extends);
						// se existir...
						if($clazz !== false) {
							/// verifica se o campo de IDentificação desta entidade linka com a outra
							$idfield = $this->oTable->getFieldProperties($this->oTable->sequence_key);
							
							// se existir o link
							if(isset($idfield['linkOn']) && $idfield['linkOn'] != '') {

								// se não estiver vazio
								if(isset($this->$idfield['linkOn']) && $this->$idfield['linkOn'] != '') {
									$fields[] = $this->$idfield['column'];
									$values[] = $this->$idfield['linkOn'];
									break;
									
								// tenta salvar o objeto, setando a partir das propriedades dessa classe
								} else {
									$clazz->setFrom( $this->toArray() );
								
									// insere o objeto
									$res = $clazz->insert();
									// se deu tudo certo na inserção
									if($res !== false) {
										//  pega o valor e coloca nesta classe
										if($clazz->$idfield['linkOn'] != '') {
											$fields[] = $idfield['column'];
											$values[] = $clazz->$idfield['linkOn'];
											break;
										}
									} 
									
									// não conseguiu pegar o valor para a subclasse
									LumineLog::logger(2, "O valor para setar na subclass " .$this->oTable->class . " não encontrado na superclasse " . $clazz->oTable->class, __FILE__, __LINE__);
									return false;
								}
							}
						}
					}
				break;
				
				// uma sequencia: o nome da sequencia sempre será $table_$field_seq
				case "sequence":
					// $fields[] = $this->oTable->sequence_key;
					// $values[] = $this->conn->nextId($this->tablename(), $this->oTable->sequence_key);
				break;
				
				// se o gerador de sequencia for uma classe personalizada
				default:
					$fields[] = $this->oTable->sequence_key;
					
					// tenta usar a classe de geração informada pelo usuário
					$gen = &Util::Import($this->oTable->sequence_generator);
					// se encontrou a classe
					if($gen !== false) {
						// passa como parâmetro a entidade para o gerador
						$values[] = $gen->$method($this->oTable);
						
					} else { //se não encontrar a classe
						// dá um alert
						LumineLog::logger(1, "Classe {$this->oTable->sequence_generator} não encontrada", __FILE__, __LINE__);
						return false;
					}
				break;
			}
		// se não tiver chave de sequencia
		}
		// se a chave de sequencia tiver um valor
		if(isset($this->$sk) && $this->$sk != '') {
			// pega os dados da chave
			$prop = $this->oTable->getColumnProperties($sk);
			// coloca para inserir
			$fields[] = $sk;
			$values[] = $this->fromValue($sk, $prop, true);
		}
		
		foreach($t as $key => $prop) {
			if(!isset($prop['foreign'])) {
				if($prop['column'] != $this->oTable->sequence_key) {
					if($this->$key != '' || ($this->$key=='' && isset($this->oTable->config->config['empty-as-null']) && $this->oTable->config->config['empty-as-null'] == '0')) {
						$fields[] = $prop['column'];
						$values[] = $this->fromValue($key, $prop, true);
					}
				}
			}
		}
		
		// verifica o tipo da chave 
		$keys = $this->oTable->getForeignKeys();
		foreach($keys as $key => $prop) {
			if($prop['type'] == 'many-to-one') {
				$x = $this->$key;

				// se for um objeto
				if(is_object($x) && is_a($x, 'luminebase')) {
					// pega o valor
					$v = $x->$prop['linkOn'];
				
					// mas se este objeto ainda não está salvo
					if($v == '') {
						// verifica se tem o método de salvar
						if(method_exists($x, 'save')) {
							// salva
							$x->save();
							// pega a chave
							$v = $x->$prop['linkOn'];
						}
					}
				} else {
					// importa a classe de referencia
					$clazz = Util::Import($prop['class']);
					$pr = $clazz->oTable->getFieldProperties( $prop['linkOn'] );
					// pega o valor
					$clazz->$prop['linkOn'] = $x;
					
					$v = $clazz->fromValue($prop['linkOn'], $pr, true);
				}
				
				$old = $this->$key;
				$this->$key = $v;
				
				if($this->$key != '') {
					$fields[] = $prop['column'];
					$values[] = $v != 'NULL' ? $this->fromValue($key, $prop, true) : $v;
				}
				$this->$key = $old;
			}
		}
		
		// campos preparados para inserção, agora inserimos :)
		if(count($fields) > 0 && count($values) > 0) {
			$sql = "INSERT INTO " . $this->tablename() . " (" .implode(", ", $fields) .") values (".implode(", ", $values).")";
			LumineLog::logger(1, 'Executando a SQL: ' . $sql, __FILE__, __LINE__);
			
			
			if( ($x = $this->conn->query($sql)) !== false) {
				$key = $this->oTable->sequence_key;
				if($key && $this->$key == ''){
					$this->$key = $this->conn->Insert_ID($this->tablename(), $key);
					LumineLog::logger(1, 'teste: '.$this->$key , __FILE__, __LINE__);
				}
				$this->_saveAttachedObjects();
				return $key ? $this->$key : true;
			}
			LumineLog::logger(3,"Erro de SQL: " . $this->conn->ErrorMsg(),__FILE__,__LINE__);
		}
		return false;
	}
	
	/**
	* Retrives objects from database based on primary keys or key => value pair
	*
	* <code>
	* $person = new Person;
	* $person->get( 20 );
	* // or
	* $person = new Person;
	* $person->get('email','eu@hufersil.com.br');
	* </code>
	* @author Hugo Ferreira da Silva
	* @access public
	* @return mixed The number of rows found or false on fail
	*/
	function get($pk, $va = false) {
		if(trim($pk) == '') {
			LumineLog::logger(2,'Chave inválida', __FILE__, __LINE__);
			return false;
		}
		if($va === false) {
			$f = &$this->oTable->sequence_key;
			if($f!='') {
				$this->$f = $pk;
				return $this->find( true );
			}
			
			$keys = $this->oTable->getPrimaryKeys();
			if(count($keys) == 0) {
				return false;
			}
			list($k) = each($keys);
			$this->$k = $pk;
			return $this->find( true );
		}
		$this->$pk = $va;
		return $this->find( true );
	}
	
	/**
	* Saves the current object on database or insert it if no sequence id value is found
	*
	* @author Hugo Ferreira da Silva
	* @access public
	* @return mixed
	* @param boolean $whereAdd Use only the WHERE clause or object's properties
	*/
	function save($whereAdd = false) {
		$this->connect();
		
		$key = $this->oTable->sequence_key;
		$keys = $this->oTable->getPrimaryKeys();
		if($key == '') {
			if(count($keys) == 0 && $whereAdd === false && empty($this->query['where'])) {
				LumineLog::logger(1, 'Não foram encontradas chaves para atualização e não há condições (whereAdd)', __FILE__, __LINE__);
				return false;
			}
			list($key) = each($keys);
		}
		
		// este objeto ainda não foi salvo, i.e. não tem um valor para sua chave e não é para atualizar somente
		// pelo whereAdd
		if(!isset($this->$key) || $this->$key == '') {
			// verifica se é uma classe extendida
			/*
			$id =& $this->oTable->getFieldProperties( $this->oTable->sequence_key );
			if(isset($this->oTable->extends) && $this->oTable->extends != '' && isset($id['linkOn'])) {
				$x = Util::Import($this->oTable->extends);
				if($x !== false) {
					// coloca os dados dessa classe na classe pai
					$x->setFrom($this->toArray());
					// salva
					$x->save();
					
					if($x->$id['linkOn'] != '') {
						// pega o campo de chave e coloca nesta classe
						$this->$key = $x->$id['linkOn'];
						return $this->insert();
					}
				}
			}
			*/
			
			
			if($whereAdd === false && (!isset($this->$key) || $this->$key == '')) {
				return $this->insert();
			} else {
				if(isset($whereAdd->$key)) {
					$this->$key = $whereAdd->$key;
				} else {
					LumineLog::logger(2, "Não é possível atualizar sem chaves e sem condições!", __FILE__, __LINE__);
					return false;
				}
			}
		} else {
			$id = $this->oTable->getFieldProperties( $this->oTable->sequence_key );
			
			if(isset($this->oTable->extends) && $this->oTable->extends != '' && isset($id['linkOn'])) {
				$x = Util::Import($this->oTable->extends);
				if($x !== false) {
					// coloca os dados dessa classe na classe pai
					$x->setFrom($this->toArray());
					// salva
					$x->save();
					
					if($x->$id['linkOn'] != '') {
						// pega o campo de chave e coloca nesta classe
						$name = $this->oTable->sequence_key;
						$this->$name = $x->$id['linkOn'];
					}
				}
			}
		}
		
		$cp = $this->oTable->getFieldProperties($key);
		
		$column = $cp['column'];
		
		if(is_object($this->$key)) {
			$v = $this->$key->$column;
		} else {
			// $v = $this->$key;
			$v = $this->fromValue($key, $cp, true);
			/*
			if(!is_numeric($v)) {
				$v = "'" . $v . "'";
			}
			*/
		}
		
		$where = $whereAdd === true && !empty($this->query['where'])? $this->query['where'] : "WHERE $column = " . $v;
		
		$fields = array();
		$values = array();
		
		$list = $this->table();
		foreach($list as $field => $prop) {
			if(!in_array($field, $keys) && $key != $field && (!isset($prop['foreign']) || !$prop['foreign'])) {
				if($whereAdd != false && isset($whereAdd->$field) && $whereAdd->$field == $this->$field) {
					continue;
				}
				
				if(!isset($this->$field)) {
					continue;
				} 
				
				$fields[] = $prop['column'];
				$values[] = $this->fromValue($field, $prop, true);
			}
		}
		
		// verifica o tipo da chave 
		$keys = $this->oTable->getForeignKeys();
		foreach($keys as $key => $prop) {
			if($prop['type'] == 'many-to-one') {
				$x = &$this->$key;
				// se for um objeto
				if(is_object($x) && is_a($x, 'LumineBase')) {
					// pega o valor
					$f = $x->oTable->getColumnProperties($prop['linkOn']);
					$v = $x->$f['name'];
					if($v == '') {
						if(method_exists($x, 'save')) {
							$x->save();
							$v = $x->$f['name'];
						}
					}
				} else {
					$v = $x;
				}

				$old = $this->$key;
				$this->$key = $v;
				
				$c = &$this->oTable->config->config;
				if($this->$key == '' && isset($c['empty-as-null']) && $c['empty-as-null'] == 1) {
					$fields[] = $prop['column'];
					$values[] = 'null';
					continue;
				}
				if($this->$key != '') {
					$fields[] = $prop['column'];
					$values[] = $this->fromValue($key, $prop, true);
				}
				$this->$key = $old;
			}
		}
		
		$str = '';
		while(list($k, $v) = each($fields)) {
			$str .= $fields[$k] . " = " . $values[$k] . ", ";
		}
		
		$updateStr = substr($str, 0, strlen($str)-2);
		if($updateStr != '') {
			$sql = "UPDATE " . $this->tablename()." SET " . $updateStr . ' ' . $where;
			LumineLog::logger(1, 'Executando a SQL: ' . $sql, __FILE__, __LINE__);
			$rs = $this->conn->Execute( $this->parseSQL($sql) );
			$erro = $this->errorMsg();
			if($erro != '') {
				LumineLog::logger(3, 'Erro de SQL: ' . $erro, __FILE__, __LINE__);
			}
			$this->_saveAttachedObjects();
			return $this->conn->Affected_Rows();
		}
	}
	
	/**
	* Saves the attached objets
	*
	* @author Hugo Ferreira da Silva
	* @access private
	* @return void
	*/
	function _saveAttachedObjects() {
		// pega os relacionamentos many-to-many
		$fks = $this->oTable->getForeignKeys();
		foreach($fks as $fk => $prop) {
			if($prop['type'] == 'many-to-many') {
				// verifica se está setada, se é um array e se contém itens para salvar
				if(isset($this->$fk) && is_array($this->$fk) && count($this->$fk) > 0) {
					// pegamos os dados do campo deste objeto
					$field = $this->oTable->getFieldProperties($fk['linkOn']);
					// se o campo deste objeto estiver vazio e not-null está definido como true
					if($field) {
						if(!isset($this->$field['name']) && $this->$field['name'] == '' && (isset($field['not-null']) && ($field['not-null'] || $field['not-null'] == 'true'))) {
							// passa para o próximo item
							continue;
						}
					}
					// ok, tem itens para salvar, então fazemos um foreach
					
					foreach($this->$fk as $item) {
						// verificamos se é um objeto LumineBase
						if(is_a($item,'LumineBase')) {
							// pega a chave primaria do elemento que acabou de ser salvo
							$key = $item->oTable->sequence_key;
							if($key == '') {
								$k = $item->oTable->getPrimaryKeys();
								$key = $k[0];
								if($key=='') {
									continue;
								}
							}
							$p = $item->oTable->getFieldProperties($key);
							if($item->$key == '') {
								$item->save();
							}
							// verifica se já existe um objeto no relacionamento gravado com os códigos de ambos
							$tp = $this->oTable->getFieldProperties($prop['linkOn']);
							$ip = $item->oTable->getFieldProperties($key);
							
							$v1 = $item->$key == '' ? NULL : is_numeric($item->$key) ? $item->$key : "'" . $this->escape($item->$key) . "'";
							$v2 = $this->$prop['linkOn'] == '' ? NULL : is_numeric($this->$prop['linkOn']) ? $this->$prop['linkOn'] : "'".$this->escape($this->$prop['linkOn'])."'";
							
							$sql = "SELECT * FROM {$prop['table']} WHERE {$ip['column']} ".($v1 === NULL?'is null':'='.$v1)." and {$tp['column']} " . ($v2 === NULL?'is null':'='.$v2);
							LumineLog::logger(1,'Verificando se este itens não estão salvos:'. $sql,__FILE__,__LINE__);
							// efetua a consulta
							$rs = $this->conn->Execute( $this->parseSQL($sql) );
							// se o numero de linhas for = 0, salva o registro
							if($rs->NumRows() == 0) {
								$sql = "INSERT INTO {$prop['table']} ({$ip['column']}, {$tp['column']}) values ({$item->$key}, {$this->$prop['linkOn']})";
								LumineLog::logger(1,'Salvando os itens:'. $sql,__FILE__,__LINE__);
								$this->conn->Execute( $this->parseSQL($sql) );
							}
							
							//se for somente os códigos
						} else { 
							//armazena o valor
							$valor = sprintf('%d',$item);
							// pega a classe de referencia
							$item = Util::Import($prop['class']);

							// pega a chave primaria do elemento que acabou de ser salvo
							$key = $item->oTable->sequence_key;
							if($key == '') {
								$k = $item->oTable->getPrimaryKeys();
								$key = key($k);
								if($key=='') {
									continue;
								}
							}
							
							// propriedades
							$ip = $item->oTable->getFieldProperties( $key );
							$tp = $this->oTable->getFieldProperties( $prop['linkOn'] );
							
						
							$sql = "SELECT * FROM {$prop['table']} WHERE {$ip['column']} = $valor and {$tp['column']} = " . $this->$prop['linkOn'];
							LumineLog::logger(1,'Verificando se este itens não estão salvos:'. $sql,__FILE__,__LINE__);
							// efetua a consulta
							$rs = $this->conn->Execute( $this->parseSQL($sql) );
							// se o numero de linhas for = 0, salva o registro
							if($rs->NumRows() == 0) {
								$sql = "INSERT INTO {$prop['table']} ({$ip['column']}, {$tp['column']}) values ($valor, {$this->$prop['linkOn']})";
								LumineLog::logger(1,'Salvando os itens:'. $sql,__FILE__,__LINE__);
								$this->conn->Execute( $this->parseSQL($sql) );
							}
						}
					}
					
				}
			}
			
			// se for do tipo on-to-many
			if($prop['type'] == 'one-to-many') {
				// tenta pegar a lista de objetos
				$list = $this->$fk;

				// se for um array e se tiver itens
				if(is_array($list) && count($list) > 0) {
					// para cada item no array
					foreach($list as $item) {
						// verifica se é um objeto e se tem o método salvar
						if(is_object($item) && method_exists($item, 'save')) {
							// pega o nome da coluna
							$fp = $item->oTable->getFieldProperties( $prop['linkOn'] );
							// se tiver valor na PROPRIEDADE
							if(isset($this->{$fp['linkOn']})) {
								// pega o valor
								$value = $this->{$fp['linkOn']};
							// do contrário, se encontrar a coluna igual a do elemento
							} else if( ($p = $this->oTable->getColumnProperties($fp['linkOn'])) && isset($this->{$p['name']})) {
								// pega o valor pelo nome 
								$value = $this->{$p['name']};
							// do contrário
							} else {
								// não achou nada
								$value = null;
							}
						
							$lp = $item->oTable->getColumnProperties($prop['linkOn']);
							$item->{$lp['name']} = $value;
							// chama o método salvar
							$item->save();
						}
					}
				}
			}
			
			// se for do tipo many-to-one
			if($prop['type'] == 'many-to-one') {
				// pega o item
				$obj = $this->$fk;
				// se for um objeto e tiver o método save
				if(is_object($obj) && method_exists($obj, 'save')) {
					// tenta salvar
					$obj->save();
				}
			}
		}
	}
	
	// somente um apelido para save
	function update($whereAdd = false) {
		// tenta pegar a chave primaria
		$key = $this->oTable->sequence_key;
		$keys = $this->oTable->getPrimaryKeys();
		if($key == '') {
			if(count($keys) == 0 && $whereAdd === false && (!isset($this->$key) || $this->$key == '')) {
				LumineLog::logger(3, 'Não é possível atualizar sem chave e condições',__FILE__,__LINE__);
				return false;
			}
			$key_list = array_keys($keys);
			$key = $key_list[0];
		}
		// se o cara for usar somente o WHEREADD
		$sql = "update {$this->__tablename} set %s %s";
		// pega os campos para atualização
		$list = $this->table();
		$values = array();
		$fields = array();
		
		// para cada campo encontrado
		foreach($list as $field => $prop) {
			$f='';
			$v='';
			// vertifica se o campo está setado
			if(isset($this->$field)) {
				// se o cara escolheu vazio = nulo
				if($this->$field == '' && isset($this->oTable->config->config['empty-as-null']) && $this->oTable->config->config['empty-as-null'] == 1) {
					// coloca este campo como null
					$f = $prop['column'];
					$v = 'null';
				}
				// se o campo não estiver vazio
				if($this->$field != '') {
					$f = $prop['column'];
					$v = $this->fromValue($field, $prop, true);
				}
				// se este campo for a chave e não for para fazer pelo WhereAdd
				if($field == $key && $whereAdd == false) {
					// coloca isso no whereAdd
					$this->whereAdd($prop['column'] .'='.$this->fromValue($field, $prop, true));
					// pula este campo
				}
			}
			// se o campo não for vazio
			if($f != '') {
				$fields[] = $f;
				$values[] = $v;
			}
		}
		
		// verifica se tem um whereAdd para atualizar
		if(isset($this->query['where']) && $this->query['where'] != '') {
			$strFields = '';
			for($i=0; $i<count($fields); $i++) {
				$strFields .= $fields[$i] . "=" . $values[$i]. ", ";
			}
			$strFields = substr($strFields, 0, strlen($strFields)-2);
			// verifica se tem alguma coisa pra atualizar
			if($strFields != '') {
				$sql = sprintf($sql, $strFields, $this->query['where']);
				$sql = $this->parseSQL($sql);

				LumineLog::logger(1,'Efetuando query: '.$sql,__FILE__,__LINE__);

				$rs = $this->conn->Execute( $sql );
				$erro = $this->errorMsg();
				
				if($erro != '') {
					LumineLog::logger(3,'falha ao executar atualização: '.$rs,__FILE__,__LINE__);
				}
				return $this->conn->_affectedrows();
				// nada pra atualizar
			} else {
				LumineLog::logger(1,'Nenhum campo setado para atualizar',__FILE__,__LINE__);
				return false;
			}
		}
		LumineLog::logger(1,'Não é possível atualizar sem cláusula where',__FILE__,__LINE__);
		return false;
	}
	
	/**
	* Delete the current object from database
	*
	* <code>
	* $person = new Person;
	* $person->get( 22 );
	* $person->delete();
	* </code>
	* @author Hugo Ferreira da Silva
	* @access public
	* @return mixed The formated value
	* @param boolean $whereAdd Use only whereAdd param (true) or not (false)
	*/
	function delete($whereAdd = false) {
		$this->connect();
		$old_query = $this->query;
		
		$pk = $this->oTable->getPrimaryKeys();
		foreach($pk as $name => $prop) {
			if(isset($this->$name) && $this->$name != '') {
				$v = $this->fromValue($name, $prop);
				if(is_numeric($v)) {
					$this->whereAdd($this->tablename().".{$prop['column']} = {$v}");
				} else {
					$this->whereAdd($this->tablename().".{$prop['column']} = '" . $v . "'");
				}
			}
		}

		if($this->query['where'] == '' && $whereAdd === false) {
			$list = $this->table();
			foreach($list as $name => $prop) {
				if(isset($this->$name) && $this->$name != '' && !is_array($this->$name)) {
					$v = $this->fromValue($name, $prop);
					if(is_numeric($v)) {
						$this->whereAdd($this->tablename().".{$prop['column']} = {$v}");
					} else {
						$this->whereAdd($this->tablename().".{$prop['column']} = '" . $this->escape($v)."'");
					}
				}
			}
		}

		$where = $this->query['where'];
		
		// se tiver uma condição WHERE
		if($where) {
			$sql = "DELETE FROM " . $this->tablename() . " $where";

			if($this->query['orderby']) {
				$sql .= " " . $this->query['orderby'];
			}
		
			if($this->query['limit']) {
				$sql .= " " . $this->query['limit'];
			}
			/*
			// se não suportar suportar FK
			if(LUMINE_FK_SUPORT == false) {
				// tenta remover os objetos relacionados
				$result = FKHandle::removeAll( $this );
				// se não conseguiu
				if($result == false) {
					// pára a remoção e retorna false
					LumineLog::logger(2, "Não é possível remover este objeto por que existem objetos relacionados a ele em modo restrict", __FILE__, __LINE__);
					return false;
				}
			}
			*/
			LumineLog::logger(1,'Executando SQL: ' . $sql, __FILE__, __LINE__);
			$rs = $this->conn->Execute( $this->parseSQL($sql) );
			
			$erro = $this->errorMsg();
			if($erro != '') {
				LumineLog::logger(3,'Erro de SQL: ' . $erro, __FILE__, __LINE__);
			}
			
			// se extende uma classe
			$id= $this->oTable->getFieldProperties( $this->oTable->sequence_key );
			if(isset($this->oTable->extends) && $this->oTable->extends != '' && isset($id['linkOn'])) {
				// chama a classe que ele extende
				$clazz = Util::Import($this->oTable->extends);
				if($clazz !== false) {
					$clazz->setFrom( $this->toArray() );
					// remove a classe que ele extende
					$clazz->delete();
				}
			}
			
			return $this->conn->Affected_Rows();
		}
		LumineLog::logger(2, "Não é possível remover objetos sem uma cláusula WHERE", __FILE__, __LINE__);
		return false;
	}
	
	/**
	* Escapes a string from $conn->escaoe
	* @return string The escaped string
	* @para string $string The string to be scaped
	* @author Hugo Ferreira da Sivla
	*/
	function escape($string, $gpc=false) {
		$string = $this->conn->qstr($string, $gpc);
		if(substr($string, 0, 1) == "'" && substr($string, strlen($string)-1,1) == "'") {
			$string = substr($string, 1, strlen($string)-2);
		}
		return $string;
	}
	
	/**
	* Get link from XML Map
	*
	* The return of this methods depends on what kind of relationship you are using:
	* - If many-to-one, an instance of class provided in relationship declaration;
	* - If one-to-many or many-to-many, an array of elements of class in relationship declaration;
	* - false on failure.
	* <code>
	* $person = new Person;
	* $person->get( 22 );
	* $list = $person->getLink("cars");
	* print_r($list); 
	* </code>
	* @param string $linkName The name of link to retrieve
	* @author Hugo Ferreira da Silva
	* @access public
	* @return mixed 
	*/
	function getLink($linkName) {
		if(trim($linkName) == '') {
			LumineLog::logger(1, "Link inválido", __FILE__, __LINE__);
			return false;
		}
		
		// pega as chaves estrangeiras
		$fields = $this->oTable->getForeignKeys();
		if(count($fields) == 0) {
			LumineLog::logger(1,'Não há links nesta entidade (' . $this->oTable->class . ')', __FILE__, __LINE__);
			return false;
		}
		if(!array_key_exists($linkName, $fields)) {
			LumineLog::logger(1, "Link <b>$linkName</b> não encontrado para a entidade <b>".$this->oTable->class."</b>", __FILE__, __LINE__);
			return false;
		}
		
		
		$column = isset($fields[$linkName]['column']) ? $fields[$linkName]['column'] : '';
		$class = Util::Import( $fields[$linkName]['class'] );
		
		if($class === false) {
			LumineLog::logger(1, 'Classe não encontrada: <b>'.$fields[$linkName]['class'], __FILE__, __LINE__);
			return false;
		}

		$fprop = $this->oTable->getLinkProperties($linkName);

		switch($fields[$linkName]['type']) {
			// se for do tipo one-to-many
			case 'one-to-many':
				$results = array();
				// procura pelo link na outra entidade
				$fks = $class->oTable->getForeignKeys();
				$fk = false;
				foreach($fks as $fkn => $prop) {
					if($fkn == $fprop['linkOn']) {
						$fk = $prop;
						$fk['name'] = $fkn;
						break;
					}
				}
				
				// se não encontrou
				if($fk === false) {
					LumineLog::logger(1, "Link {$linkName} não encontrado na entidade {$class->oTable->class}", __FILE__, __LINE__);
					return false;
				}
				
				// mas se encontrou e neste objeto a propriedade não for fazia
				if(isset($this->$fk['linkOn']) && $this->$fk['linkOn'] != '') {
					$value = is_numeric($this->$fk['linkOn']) ? $this->$fk['linkOn'] : "'" . $this->$fk['linkOn'] . "'";
					$class->whereAdd ($fk['column'] . '=' . $value);
					$class->find();

					while($class->fetch( false )) {
						$d = $class->toArray();
						if(is_array($d)) {
							$item = Util::Import($fprop['class']);
							$item->setFrom($d);
							
							$this->_checkForLazy( $item );
							
							$results[] = $item;
						}
					}
				}
				return $results;
			break;
			
			// o outro lado é do tipo one-to-many
			case 'many-to-one';
				//$fkp = $class->oTable->getLinkProperties($fk['name']);
				if($this->$fprop['name'] != '') {
					$value = is_numeric($this->$fprop['name']) ? $this->$fprop['name'] : "'" . $this->$fprop['name'] . "'";
					$p = $class->oTable->getFieldProperties($fprop['linkOn']);
					$class->whereAdd($p['column'] .'='. $value);
					$class->find();
					$class->fetch( false );
				}
				$this->_checkForLazy( $class );
				return $class;
			break;
			
			// tipo many-to-many
			case "many-to-many":
				$ref = &$fields[$linkName];
				$x = $this->oTable->getFieldProperties($ref['linkOn']);
				if($x === false) {
					LumineLog::logger(1, 'Campo não encontrado ('.$ref['column'].')', __FILE__, __LINE__);
					return false;
				}
				if($ref['table'] == '') {
					LumineLog::logger(1, 'Você deve informar o nome da tabela para relacionamento many-to-many entre ' .$this->oTable->class. ' e ' . $ref['class'], __FILE__, __LINE__);
					return false;
				}
				$v = $this->oTable->getColumnProperties($x['column']);
				$value = $this->$v['name'];
				
				if(!is_numeric($value)) {
					$value = "'" . $this->escape($value) . "'";
				}
				$sql = "SELECT * FROM {$ref['table']} WHERE {$x['column']} = $value";
				
				LumineLog::logger(1,'Efeutando consulta: '.$sql,__FILE__,__LINE__);
				$rs = $this->conn->Execute( $this->parseSQL($sql) );
				
				// erro na consulta
				if($rs === false) {
					return false;
				}
				
				// lista com os resultados
				$results = array();
				// pega a chave de sequencia
				$key=$class->oTable->sequence_key;
				//se não achou
				if($key=='') {
					//tenta as chaves primarias
					$keys = $class->oTable->getPrimaryKeys();
					$key=key($keys);
					// se ainda assim não achou, retorna false
					if($key == '') {
						LumineLog::logger(1, 'Chave de ligação não encontrada', __FILE__, __LINE__);
						return false;
					}
				}
				// enquanto houverem resultados
				$cd = $class->oTable->getFieldProperties($key);
				while(!$rs->EOF) {
					$row = &$rs->fields;
					// coloca um novo elemento no array de resultados
					$clazz = array_pop(explode(".", $ref['class']));
					$x = new $clazz;

					$x->get( $row[$cd['column']] );
					$this->_checkForLazy( $x );
					$results[] = $x;
					$rs->MoveNext();
				}
				return $results;
				
			break;
		}
		
		// por algum motivo, não achou nada
		return false;
	}
	
	/**
	* checa existencia de chaves estrangeira e "lazy"
	* @author Hugo Ferreira da Silva
	* @see getLink
	*/
	function _checkForLazy( &$item ) {
		// pega as chaves estrangeiras
		$fks = $item->oTable->getForeignKeys();
		foreach($fks as $fk=>$prop) {
			if(isset($prop['lazy']) && $prop['lazy'] == 'true') {
				$item->$fk = $item->getLink( $fk );
			}
		}
	}
	
	/**
	* Validate the class to insert / update
	* @see LumineValidation
	* @author Hugo Ferreira da Silva
	*/
	function validate() {
		if(!class_exists("LumineValidation")) {
			require_once LUMINE_INCLUDE_PATH . 'LumineValidation.php';
		}
		return LumineValidation::validate($this);
	}
	
	/**
	* Delete a object from database 
	* This is used to remove the object from a database that has relationship with this object
	* @return boolean True on sucess, false on failure
	* @author Hugo Ferreira da Silva
	* @param LumineBase $object The object to remove from this object
	*/
	function remove (&$object) {
		// se for um objeto
		if(is_a($object, 'LumineBase')) {
			// pega a definição da classe passado como referencia
			$clazz = $object->oTable->class;
			$fks = $this->oTable->getForeignKeys();
			
			$this_prop = false;
			
			LumineLog::logger(1,'checando propriedades desta classe: ' . get_class($this),__FILE__,__LINE__);
			foreach($fks as $fkname => $fkdef) {
				if($fkdef['class'] == $clazz) {
					$this_prop = array_merge( $this->oTable->getFieldProperties( $fkdef['linkOn'] ), $fkdef);
					break;
				}
			}
			
			LumineLog::logger(1,'checando propriedades da classe a ser removida: ' . get_class($object),__FILE__,__LINE__);
			$obj_prop = false;
			$fks = $object->oTable->getForeignKeys();
			foreach($fks as $fkname => $fkdef) {
				if($fkdef['class'] == $this->oTable->class) {
					$obj_prop = $fkdef;
					$obj_prop = array_merge( $object->oTable->getFieldProperties( $fkdef['linkOn'] ), $fkdef);
					break;
				}
			}
			
			if($this_prop === false || $obj_prop === false) {
				LumineLog::logger(2,'As referencias de many-to-many devem ser especificadas em ambas entidades para utilizar este recurso',__FILE__,__LINE__);
				return false;
			}
			
			$delete = "DELETE FROM " . $this_prop['table'] . " WHERE ";
			$tcp = $this->oTable->getColumnProperties( $this_prop['column'] );
			$ocp = $object->oTable->getColumnProperties ( $obj_prop['column'] );
			
			if(isset($this->$tcp['name']) && $this->$tcp['name'] != '' && isset($object->$ocp['name']) && $object->$ocp['name'] != '') {
				$delete .= sprintf("%s = %d AND %s = %d", $tcp['column'], $this->$tcp['name'], $ocp['column'], $object->$ocp['name']);
			} else {
				LumineLog::logger(2,'Os dois objetos devem possuir valores em suas colunas',__FILE__,__LINE__);
				return false;
			}
			LumineLog::logger(1,'Executando query: ' . $delete,__FILE__,__LINE__);
			$this->conn->Execute( $this->parseSQL($delete) );
			return true;
		}
		LumineLog::logger(2,'O objeto passado como parâmetro deve extender a classe LumineBase',__FILE__,_LINE__);
		return false;
	}
	
	/**
	* Removes all many-to-many objects from this object
	* @param string $name Name of many-to-many relationship to delete elements
	* @author Hugo Ferreira da Silva
	* @return mixed Number of elements removed or false on failure
	*/
	function removeAll( $name ) {
		$fks = $this->oTable->getForeignKeys();
		$this->connect();
		
		foreach($fks as $fkname => $fkdef) {
			if($name == $fkname && $fkdef['type'] == 'many-to-many') {
				$cp = array_merge($this->oTable->getFieldProperties( $fkdef['linkOn'] ), $fkdef);
				if(isset($this->$fkdef['linkOn']) && $this->$fkdef['linkOn'] != '') {
					$sql = "DELETE FROM " . $fkdef['table'] . " WHERE " . $cp['column'] ." = " . sprintf("%d",$this->$fkdef['linkOn']);
					LumineLog::logger(1,'Executando SQL: ' . $sql,__FILE__,__LINE__);
					$r = $this->conn->Execute( $this->parseSQL($sql) );
					return $this->conn->Affected_Rows();
				}
			}
			if($name == $fkname && $fkdef['type'] == 'one-to-many') {
				// recupera os itens
				$list = $this->getLink($name);
				// se for um array
				if(is_array($list)) {
					// para cada um
					foreach($list as $item) {
						// remove
						$item->delete();
					}
				}
			}
		}
		return false;
	}
	
	/**
	* returns the number of rows found on the last query
	* @return Number The number of rows found
	* @author Hugo Ferreira da Silva
	*/
	function numrows() {
		if(isset($this->rs->_numOfRows)) {
			return $this->rs->_numOfRows;
		}
		return false;
	}
	
	/**
	* Returns an array with all records in an associative array
	* Example:<br>
	* <code>
	* $result = $person->allToArray();
	* print_r($result);
	* // display
	* array (
	*    [0] => array (
	*          [nome] => [hugo]
	*    )
	* )
	* </code>
	* @author Hugo Ferreira da Silva
	* @return Array
	*/
	function allToArray() {
		$ar = array();
		if(!$this->rs) {
			return $ar;
		}
		if($this->rs->NumRows() == 0) {
			return $ar;
		}
		
		$this->rs->MoveFirst();
		while($this->fetch()) {
			$ar[] = $this->toArray();
		}
		$this->rs->MoveFirst();
		$this->fetch();
		return $ar;
	}
	
	/**
	 * Gets the error msg
	 * @author Hugo Ferreira da Silva
	 */
	function errorMsg() {
		return $this->conn->ErrorMsg();
	}

	/**
	 * Initiates a transaction
	 * @author Hugo Ferreira da Silva
	 */
	function begin() {
		return $this->conn->BeginTrans();
	}
	/**
	 * Commits a transaction.
	 * @param boolean $coomit Commit a transaction or rollback
	 * @author Hugo Ferreira da Silva
	 */
	function commit( $commit = true ) {
		return $this->conn->CommitTrans( $commit );
	}
	/**
	 * Rollbacks a transaction
	 * @author Hugo Ferreira da Silva
	 */
	function rollback() {
		return $this->conn->RollbackTrans();
	}
	
	/**
	 * return the database name
	 * @author Hugo Ferreira da Silva
	 * @return string Database name
	 * @access public
	 */
	function database() {
		return $this->oTable->config->config['database'];
	}
	
	/**
	* Recursive method to set a LIKE clause to your query
	*
	* If the $cond is null, so set's the $entity to this object. If the $cond is string<br>
	* so create the LIKE through whereAdd method. If the $str is null, so return false<br>
	* Example:
	* <code>
	* $object->likeAdd(null, 'any word'); // Creates: "table.colnameA LIKE '%any word%' AND 
	*                                                  table.colnameB LIKE '%any word%'...."
	* $object->likeAdd($object, 'any word'); // Creates: "objectTableName.colnameA LIKE '%any word%' AND 
	*                                                     objectTableName.colnameB LIKE '%any word%'...."
	* $object->likeAdd($object->tablename().'.colname', 'any word'); // Creates only: "objectTableName.colname LIKE '%any word%'"
	* $object->likeAdd('colname', 'any word'); // Creates: colname LIKE '%any word%'
	* </code>
	* @param object|null|string $cond Add the condition to LIKE clause
	* @param string $str The string to search
	* @param string $logic Add the logic for your LIKE condition. i.e.: OR | AND (default AND)
	* @param bool $merge Defines if string will be entire or separate
	* @author Marcelo Rodrigues Gonzaga
	* @access public
	*/
	function likeAdd($cond = null, $str = null, $logic = 'AND', $merge = false) {
		if ($str == null) {
			return false;
		}
		if (is_string($cond) || is_array($cond)) {
			$fields = explode(',', $cond);
			$strings = explode(' ', trim($str));
			
			if ($merge == false) {
				foreach($strings as $str) {
					foreach($fields as $field) {
						$this->whereAdd("{$field} LIKE '%{$str}%'", $logic);
					}
				}
			} else {
				foreach($fields as $field) {
					$this->whereAdd("{$field} LIKE '%{$str}%'", $logic);
				}
			}
		} else {
			$entity = $cond;
			if ($cond == null) {
				$entity = &$this;
			}
			if (!is_a($entity, 'LumineBase')) {
				return false;
			}
			
			$fields = $entity->table();
			$s = array();
			
			foreach($fields as $field => $prop) {
				if (!isset($prop['foreign']) || !$prop['foreign'] || ($prop['foreign'] && $prop['type'] == 'many-to-one')) {
					$s[] = $entity->tablename() . "." . $prop['column'];
				}
			}
			$this->likeAdd(implode(",",$s), $str, $logic);
		}
	}
	
	/**
	 * recupera os objetos relacionados a este em uma árvore descendete quando houver
	 * um relacionamento em si próprio
	 * @author Hugo Ferreira da Silva
	 * @return array Lista contendo os dados dos elementos
	 */
	function getTree( $id = false, $type = 'array') {
		$field = strtolower(get_class($this));
		$cl = ucfirst($field);
		$list = array();
		if(!isset($this->$field)) {
			return $list;
		}
		
		if($this->$field == '' && $id === false) {
			return $list;
		}
		if($id === false) {
			$item = $this->getLink( $field );
		} else {
			$item = new $cl;
			$item->get( $id );
		}
		$continua = true;
		
		do {
			if($item->numrows() == 0) {
				$continua = false;
			} else {
				switch($type) {
					case 'object':
						$list[] = $item;
					break;
					case 'array':
					default:
						$list[] = $item->toArray();
				}
				$item = $item->getLink( $field );
			}
		} while( $continua ) ;
		
		return array_reverse($list);
	}
	
	/**
	 * Parses the SQL command and change the entity values to table values
	 * @author Hugo Ferreira da Silva
	 * @param string $sql The SQL statement to change
	 * @return string The SQL changed
	 */
	function parseSQL( $sql ) {
		if(isset($this->oTable->config->config['parse-sql']) && $this->oTable->config->config['parse-sql'] == '0') {
			return $sql;
		}
		$inStr = false;
		$idx = 0;
		$max = strlen($sql);
		$start = '';
		
		$nova = '';
		$tmp = '';
		
		for($i=0; $i<$max; $i++) {
			$char = $sql{$i};
			if(!$inStr && ($char == '"' || $char == "'") && $sql{$i-1} != '\\') {
				$nova .= $this->_changeSQLValues( $tmp );
				
				$inStr = true;
				$start = $sql{$i};
				$tmp = '';
				
				continue;
			} else if($inStr && ($char == '"' || $char == "'") && $sql{$i-1} != '\\' && $char == $start) {
				$nova .= $start . $tmp . $start;
				$tmp = '';
				$inStr = false;
				
				continue;
			}
			
			$tmp .= $sql{$i};
		}

		if($tmp != '') {
			$nova .= $this->_changeSQLValues( $tmp );
		}
		return $nova;
	}
	
	function _changeSQLValues( $sql ) {
		$reg = array();
		preg_match_all('@\{([a-z,A-Z,0-9,_]+|[a-z,A-Z,0-9,_]+\.[a-z,A-Z,0-9,_]+)\}@',$sql, $reg);
		
		$jc = $this->_joinedClasses;
		$jc[array_pop(explode('.', $this->oTable->class))] = &$this;
		
		for($i=0, $max=count($reg[0]); $i<$max; $i++) {
			$p = explode('.', $reg[1][$i]);

			// somente o nome da classe, indicando que quer o nome			
			// da tabela
			if(count($p) == 1) {
				// se estiver unida corretamente
				if(isset($jc[$p[0]])) {
					// troca o nome da tabela
					$sql = str_replace($reg[0][$i], $jc[$p[0]]->tablename(), $sql);
				}
				
				// se não achar pelo nome da classe
				// então vemos se existe este campo nesta classe
				$f = $this->oTable->getFieldProperties( $p[0] );
				if($f !== false) {
					$sql = str_replace($reg[0][$i], $f['column'], $sql);
				}
			}
			// se tiver Classe.campo
			if(count($p) == 2) {
				// procura a classe na lista
				if(isset($jc[$p[0]])) {
					// pega a entidade
					$x = &$jc[$p[0]];
					// procura o campo pelo nome do campo na entidade
					$f = $x->oTable->getFieldProperties( $p[1] );
					// se encontrou o campo
					if($f !== false) {
						// troca na SQL
						$sql = str_replace($reg[0][$i], $x->tablename().'.'.$f['column'], $sql);
					}
				}
			}
			
		}
		unset($jc);
		return $sql;
	}
	
	/**
	 * Decriptografa um valor vindo do banco de dados
	 * @param array $prop Array contendo as propriedades do campo
	 * @param string $value O valor a ser decriptografado
	 * @return string Valor decriptografado
	 * @author Hugo Ferreira da Silva
	 */
	function _getDecryptValue($prop, $value) {
		if(isset($prop['crypt']) && $prop['crypt'] == 'true') {
			LumineLog::Logger(1,'Decriptografando o campo ' . $prop['name'],__FILE__,__LINE__);
			$value = Util::decrypt($value, $this);
			// o valor foi "escapado" antes de inserir/atualizar... tempos que retirar as contra-barras
			if(isset($this->oTable->config->config['escape']) && $this->oTable->config->config['escape'] == 1) {
				$value = stripslashes( $value );
			}
		}
		return $value;
	}
	
	/**
	 * Criptografa o valor para armazenamento no banco
	 * @param string $value O valor a ser criptografado
	 * @param array $prop Propriedades do campo a ser criptografado
	 * @return string Valor criptografado
	 * @author Hugo Ferreira da Silva
	 */
	function _encryptValue( $value, $prop ) {
		if(isset($prop['crypt']) && $prop['crypt'] == 'true') {
			$value = Util::encrypt( $value, $this );
		}
		return $value;
	}
	
	/**
	 * Transforma o registro atual em JSON
	 * @return string Valor em formato JSON
	 * @author Hugo Ferreira da Silva
	 */
	function toJSON($format = '%s', $parseUTF8 = true) {
		$this->_importJSON();
		$result = $this->toArray($format);
		
		if($parseUTF8) {
			$result = Util::toUTF8( $result );
		}
		
		$json = new Services_JSON( SERVICES_JSON_LOOSE_TYPE );
		return $json->encode( $result );
	}
	
	/**
	 * Transforma todos os registros encontrados em JSON
	 */
	function allToJSON( $utf8 = true) {
		$this->_importJSON();
		$cache = $this->allToArray();
		if($utf8) {
			$cache = Util::toUTF8( $cache );
		}
		
		$json = new Services_JSON( SERVICES_JSON_LOOSE_TYPE );
	
		return $json->encode($cache);
	}
	
	/**
	 * converte o registro atual para XML
	 */
	function toXML($format = '%s', $utf8 = true, $includeHeaders = true) {
		if($includeHeaders) {
			$xml = '<?xml version="1.0" encoding="'.($utf8==true?'UTF-8':'ISO-8859-1').'"?>' . PHP_EOL;
		} else {
			$xml = '';
		}
		$result = $this->toArray( $format );
		if($utf8) {
			$result = Util::toUTF8($result);
		}
		
		$xml .= '<row>' . PHP_EOL;
		foreach($result as $key => $value) {
			$xml .= "\t".'<field name="'.$key.'" ';
			
			$prop = $this->oTable->getFieldProperties($key);
		
			if($prop != false) {
				foreach ($prop as $field => $val) {
					if($field != 'name') {
						$xml .= sprintf('%s="%s" ', $field, $val);
					}
				}
			}
			$xml .= '><![CDATA[' . $value . ']]></field>' . PHP_EOL;
		}
		
		$xml .= '</row>'.PHP_EOL;
		
		return $xml;
	}
	
	/**
	 * converte todos os registros para XML
	 */
	function allToXML($format = '%s', $utf8 = true) {
		$xml = '<?xml version="1.0" encoding="'.($utf8==true?'UTF-8':'ISO-8859-1').'"?>' . PHP_EOL;
		
		if(!$this->rs) {
			return $xml;
		}
		if($this->rs->NumRows() == 0) {
			return $xml;
		}
		
		$xml .= '<result-set>'.PHP_EOL;
		
		$this->rs->MoveFirst();
		while($this->fetch()) {
			$xml .= $this->toXML($format, $utf8, false);
		}
		$xml .= '</result-set>';
		
		return $xml;
	}
	/**
	 * faz um setFrom a partir de uma stirng JSON
	 */
	function setFromJSON( $str, $parseUTF8 = true ) {
		$this->_importJSON();
		
		$json = new Services_JSON( SERVICES_JSON_LOOSE_TYPE );
		$arr = $json->decode( $str );
		if($parseUTF8) {
			$arr = Util::fromUTF8( $arr );
		}
		$this->setFrom($arr);
		
	}
	
	/**
	 * importa a classe JSON só quando necessário
	 */
	function _importJSON() {
		if(!class_exists('Services_JSON')) {
			require_once LUMINE_INCLUDE_PATH . 'utils/JSON.php';
		}
	}
}


?>