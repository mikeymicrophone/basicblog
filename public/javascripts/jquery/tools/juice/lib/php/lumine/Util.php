<?php
/**
* @copyright (C) 2005 Hugo Ferreira da Silva. All rights reserved
* @license http://www.gnu.org/copyleft/lesser.html LGPL License
* @author Hugo Ferreira da Silva <eu@hufersil.com.br>
* @link http://www.hufersil.com.br/lumine/ Lumine Project
* Lumine is Free Software
**/

/** 
* Utility class, just for hold the common methods
* @author Hugo Ferreira da Silva
* @access public
* @package Lumine
*/
class Util {
	/**
	* Formats the date  to a given format
	*
	* <code>
	* $my_iso_date = Util::FormatDate('20/10/2005', '%Y-%m-%d'); // or
	* $my_iso_date = Util::FormatDate('10/20/2005', '%Y-%m-%d'); // or
	* $my_iso_date = Util::FormatDate(time(), '%Y-%m-%d'); // or
	* $my_formated_date = Util::FormatDate("2005-10-20", "%d/%m/%Y");
	* </code>
	* @author Hugo Ferreira da Silva
	* @return string The formated date
	* @access public
	* @param mixed $date Or in date format or unix timestamp
	* @param string $format The desired format
	*/
	function FormatDate($date, $format = "%d/%m/%Y") {
		$v = $date;
		if(is_numeric($date)) {
			return strftime($format, $date);
		}
		$formats = array("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/",
						"/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/");
		//$replaces = array(
		if(preg_match($formats[0], $date, $d)) {
			$v = $date;
		}
		if(preg_match($formats[1], $date, $d)) {
			if(checkdate($d[2], $d[1], $d[3])) {
				$v = "$d[3]-$d[2]-$d[1]";
			} else {
				$v = "$d[3]-$d[1]-$d[2]";
			}
		}
		$s = strtotime($v);
		if($s > -1) {
			return strftime($format, $s);
		}
		return $v;
	}
	/**
	* Formats the time to a given format
	*
	* @author Hugo Ferreira da Silva
	* @return string The formated time
	* @access public
	* @param mixed $time Or in time format or unix timestamp
	* @param string $format The desired format
	*/
	function FormatTime($time, $format = "%H:%M:%S") {
		if(is_numeric($time)) {
			return strftime($time, $format);
		}
		$v = $time;
		$t = strtotime($v);
		if($t > -1) {
			$v = strftime($format, $t);
		}
		return $v;
	}
	
	/**
	* Formats the datetime to a given format
	*
	* <code>
	* $my_iso_date = Util::FormatDateTime('20/10/2005 22:33', '%Y-%m-%d %H:%M'); // or
	* $my_iso_date = Util::FormatDateTime('10/20/2005 01:04:07', '%Y-%m-%d :%H:%M:%S'); // or
	* $my_iso_date = Util::FormatDateTime(time(), '%Y-%m-%d %H:%M:%S'); // or
	* $my_formated_date = Util::FormatDate("2005-10-20 10:30", "%d/%m/%Y %H:%M");
	* </code>
	* @author Hugo Ferreira da Silva
	* @return string The formated datetime
	* @access public
	* @param mixed $time Or in date format or unix timestamp
	* @param string $format The desired format
	*/
	function FormatDateTime($time, $format = "%Y-%m-%d %H:%M:%S") {
		if(is_numeric($time)) {
			return strftime($format, $time);
		}
		// 2005-10-15 12:29:32
		if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/", $time, $reg)) {
			return strftime($format, strtotime($time));
		}
		// 2005-10-15 12:29
		if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2})$/", $time, $reg)) {
			return strftime($format, strtotime($time));
		}
		// 2005-10-15 12
		if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2})$/", $time, $reg)) {
			return strftime($format, strtotime($time));
		}
		// 2005-10-15
		if(preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $time, $reg)) {
			return Util::FormatDate($time, $format);
		}
		// 15/10/2005 12:29:32
		if(preg_match("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/", $time, $reg)) {
			$isodate = Util::FormatDate("$reg[1]/$reg[2]/$reg[3]", "%Y-%m-%d");
			return strftime($format, strtotime("$isodate $reg[4]:$reg[5]:$reg[6]"));
		}
		// 15/10/2005 12:29
		if(preg_match("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2}):([0-9]{2})$/", $time, $reg)) {
			$isodate = Util::FormatDate("$reg[1]/$reg[2]/$reg[3]", "%Y-%m-%d");
			return strftime($format, strtotime("$isodate $reg[4]:$reg[5]:00"));
		}
		// 15/10/2005 12
		if(preg_match("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2})$/", $time, $reg)) {
			$isodate = Util::FormatDate("$reg[1]/$reg[2]/$reg[3]", "%Y-%m-%d");
			return strftime($format, strtotime("$isodate $reg[4]"));

		}
		// 15/10/2005
		if(preg_match("/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/", $time, $reg)) {
			return Util::FormatDate($time, $format);
		}
		return $time;
	}
	
	/**
	* Import a class from the current class-path and return a new instance of it
	*
	* @author Hugo Ferreira da Silva
	* @return mixed A  new instance of class or false on failure
	* @access public
	* @param string $class A class to import (like my.package.Classname)
	*/
	function Import($class) {
		$listConf = &$GLOBALS['__LumineConf'];
		
		// primeiro, vemos se é uma entidade
		foreach($listConf as $conf) {
			for($j=0; $j<count($conf->tables); $j++) {
				// achamos a entidade relacionada
				if($conf->tables[$j]->class == $class) {
					$file = $conf->config['class-path'] . "/" . str_replace(".","/",$class) . ".php";
					if(file_exists($file)) {
						require_once $file;
						$className = array_pop(explode(".", $class));
						$x = new $className;
						return $x;
					} else {
						LumineLog::logger(3, 'O arquivo não existe', __FILE__, __LINE__);
						return false;
					}
				}
			}
		}
		// ok, não é uma entidade, então vamos procurar simplesmente por uma classe dentro de todas as
		// class-paths definidas e usaremos a primeira que encontrar
		reset($listConf);
		foreach($listConf as $conf) {
			$file = $conf->config['class-path'] . "/" . str_replace(".","/",$class) . ".php";
			// se existir
			if(file_exists($file)) {
				// importa a classe
				require_once $file;
				$className = array_pop(explode(".", $class));
				$x = new $className;
				return $x;
			}
		}
		
		return false;
	}
	
	/**
	* Create directory recusively
	* @author Hugo Ferreira da Silva
	* @return boolean True on success, false on failure
	* @param strin $dir the directory
	*/
	function mkdir($dir, $dono = false) {
		if(file_exists($dir) && is_dir($dir)) {
			return true;
		}
		$dir = str_replace("\\","/", $dir);
		$pieces = explode("/", $dir);
		
		for($i=0; $i<count($pieces); $i++) {
			$mdir = '';
			for($j=0; $j<=$i; $j++) {
				$mdir .= $pieces[$j] != '' ? $pieces[$j] . "/" : '';
			}
			$mdir = substr($mdir, 0, strlen($mdir)-1);
			if(!file_exists($mdir) && $mdir != '') {
				mkdir($mdir, 0777) or die("Falha ao criar o diretório <strong>$mdir</strong>");
				@chmod($mdir, 0777);
				if($dono !== false) {
					chown($mdir, $dono);
				}
			}
		}
		return true;
	}
	
	/**
	* Write a file with the given content
	* @author Hugo Ferreira da Silva
	* @return void
	* @param string $filename The filename to write
	* @param string $contents The contents of file
	* @access public
	*/
	function write($filename, $contents) {
		$fp = fopen($filename, "wb");
		fwrite($fp, $contents);
		fclose($fp);
	}
	
	/**
	* Validate emali address
	* @author Hugo Ferreira da Silva
	* @return boolean
	* @param string $email The email to validate
	* @access public
	*/
	function validateEmail ($email ) {
		if($email == '') {
			return false;
		}
		return ereg("^([0-9,a-z,A-Z]+)([.,_,-]([0-9,a-z,A-Z]+))*[@]([0-9,a-z,A-Z]+)([.,_,-]([0-9,a-z,A-Z]+))*[.]([0-9,a-z,A-Z]){2}([0-9,a-z,A-Z])?$", $email);
	}
	/**
	 * Cria as options de uma select HTML
	 */
	function buildOptions($class, $value, $label, $selected='', $where=false) {
		if(is_string($class)) {
			$o=Util::Import($class);
			if($o) {
				if($where != false) {
					$o->whereAdd($where);
				}
				$o->orderby("$label asc");
				$o->find();
			}
		} else if(is_a($class, 'luminebase')) {
			$o = &$class;
		} else {
			return false;
		}
		$str='';
		while($o->fetch()) {
			$str .= '<option value="'.$o->$value.'"';
			if($o->$value == $selected) {
				$str .= ' selected="selected"';
			}
			$str .= '>'.$o->$label.'</option>';
		}
		return $str;
	}
	
	/**
	 * Cria um formulário simples de cadastro de itens com a classe passada
	 * como parametro
	 */
	function createForm( $className, $options=array() ) {
		$nl = "\r\n";
		$ref = Util::Import( $className );
		if($ref === false) {
			die('Classe '. $className.' não encontrada');
		}
		
		$list = $ref->table();
		$mtm = $ref->oTable->getForeignKeys();
		
		foreach($mtm as $name => $prop) {
			if($prop['type'] == 'many-to-many') {
				$list[$name] = $prop;
			}
		}
		
		$str = '<form action="'.$_SERVER['PHP_SELF'].'" method="post" enctype="multipart/form-data" id="form1" name="form1">' . $nl;
		$str .= '<table>' . $nl;

		$formats = "/\b(";
		$formats .= "int|integer|float|float4|float8|double|double precision|real";
		$formats .= "|bpchar|text|char|varchar|blob|longblob|tinyblob|longtext|tinytext|mediumtext|mediumblob";
		$formats .= "|date|time|datetime|timestamp";
		$formats .= "|bool|boolean|tinyint";
		$formats .= "|bytea";
		$formats .= "|many-to-one|many-to-many";
		$formats .= ")\b/i";
		
		$labels = array('nome','name','label','descricao','description','login');

		foreach($list as $name => $prop) {
			$reg=array();
			if(!isset($ref->oTable->sequence_key) || (isset($ref->oTable->sequence_key) && $name != $ref->oTable->sequence_key)) {
				if(isset($prop['type']) && preg_match($formats, $prop['type'], $reg)) {
					$default = '';
					if(isset($_POST[$name])) $default = $_POST[$name];
					else if(isset($prop['default'])) $default = $prop['default'];
					$str .= '<tr><td>' . ucfirst($name) . '</td>'.$nl;
					
					$len = str_replace($reg[1], '', preg_replace("'[\(\)]'","",$prop['type']));
					
					switch($reg[1]) {
						case "longblob":
						case 'bytea':
							$str .= '<td><input type="file" name="'.$name.'" />';
							$str .= '(<input type="checkbox" name="'.$name.'_remove_file" /> - Remover)</td></tr>';
						break;
						case "blob":
						case "tinyblob":
						case "longtext":
						case "tinytext":
						case "text":
							$str .= '<td><textarea rows="4" cols="30" name="'.$name.'">';
							$str .= $default;
							$str .= '</textarea></td></tr>'.$nl;
						break;
						case "mediumtext":
						case "varchar":
						case "char":
						case "bpchar":
							$str .= '<td><input type="text" ';
							if(is_numeric($len)) {
								$str .= 'maxlength="'.$len.'" ';
								if($len < 50) {
									$str .= 'size="'.$len.'" ';
								}
							}
							$str .= 'name="'.$name.'" value="'.$default.'" /></td></tr>'.$nl;
						break;
						case "integer":
						case "int":
						case "float":
						case "float4":
						case "float8":
						case "double":
						case "double precision":
						case "real":
						case "date":
						case "time":
						case "timestamp":
						case "datetime":
							$str .= '<td><input type="text" ';
							$str .= 'name="'.$name.'" value="'.$default.'" /></td></tr>'.$nl;
						break;
						case "many-to-one":
							$o = Util::Import($prop['class']);
							$list = $o->table();
							
							$label = $prop['linkOn'];
							
							foreach($list as $n => $p) {
								if(in_array($n, $labels)) {
									$label = $p['column'];
									break;
								}
							}
							$str .= '<td><select name="'.$name.'"><option value=""></option>'.Util::buildOptions($prop['class'], $prop['linkOn'], $label, @$_POST[$name]);
							$str .= '</td></tr>';
						break;
						
						case 'many-to-many':
							$itens_list = Util::Import($prop['class']);
							$itens_list->find();
							
							$pks = $itens_list->oTable->getPrimaryKeys();
							$entity_fields = $itens_list->table();
							
							$label_field = key($pks);

							foreach($entity_fields as $n => $p) {
								if(in_array($n, $labels)) {
									$label_field = $p['column'];
									break;
								}
							}

							$key = key($pks);
							$str .= ' <td>(many-to-many)</td></tr><tr><td colspan="2">';
							
							while($itens_list->fetch()) {
								$ck = isset($_POST[ $name ]) && in_array($itens_list->$key, $_POST[$name]) ? ' checked="checked"' : '';
								$str .= '<input id="'.$name.$itens_list->$key.'" type="checkbox" name="'.$name.'[]" value="'.$itens_list->$key.'"'.$ck.' /> ';
								$str .= '<label for="'.$name.$itens_list->$key.'">'.$itens_list->$label_field . '</label><br />';
							}
							
							$str .= '</td></tr>';
							
						break;
						
						case "boolean":
						case "bool":
						case "tinyint":
							$str .= '<td><input type="checkbox" name="'.$name.'" value="1"';
							if(@$_POST[$name] == 1) {
								$str .= ' checked="checked"';
							}
							$str .= ' /></td></tr>';
						break;
					}
				}
			}
		}
		$value = '';
		if(isset($ref->oTable->sequence_key)) {
			$key = $ref->oTable->sequence_key;
			if($key == '') {
				$keys = $ref->oTable->getPrimaryKeys();
				if(count($keys) > 0) {
					list($key) = each($keys);
				}
			}
			$value = @$_POST[$key];
			$str .= '<input type="hidden" name="_old_'.$key.'" value="'.$value.'" />' . $nl;
		} 
		$str .= '<tr><td colspan="2"><input type="submit" name="lumineAction" value="Save" />
				<input type="button" value="Cancel" onclick="window.location=\''.$_SERVER['PHP_SELF'].'\'" />'.
				($value != '' ? '<input type="submit" name="lumineAction" value="Remove" /><input type="hidden" name="id" value="'.$value.'" />' : '').
				'</td></tr>' . $nl;
		$str .= '</table></form>' . $nl;
		return $str;
	}
	
	/**
	 * Cria uma lista para editar
	 */
	function createEditList($className, $where=false, $max = 20, $offset = 0) {
		$nl = "\r\n";
		$ref = Util::Import($className);
		if($where != false) {
			$ref->whereAdd($where);
		}
		$total = $ref->count();
		$ref->limit($offset, $max);

		$list = $ref->table();
		$ref->selectAs();
		
		$i = 1;

		/* LumineLog::setLevel(3);
		LumineLog::setOutput(); */

		foreach($list as $name => $prop) {
			if(isset($prop['type']) && $prop['type'] == 'many-to-one') {
				$o = Util::Import($prop['class']);
				$l = $o->table();
				foreach($l as $f => $p) {
					if(isset($p['type']) && $p['type'] != 'one-to-many' && $prop['type'] != 'many-to-many') {
						$ref->selectAdd(sprintf("%s.%s as %s", "tabela$i", $p['column'], "{$f}_$name"));
					}
				}
				//$ref->selectAs($o, '%s_'.$name);
				$ref->joinAdd($o, "LEFT", "tabela".($i++));
			}
		}
		reset($list);

		$ref->find();

		$str = '<table width="100%" cellpadding="2" cellspacing="1" bgcolor="#CCCCCC">' . $nl;
		$str .= '<tr>';
		$ar = array();
		$bg = 'bgcolor="white" ';

		foreach($list as $name => $prop) {
			if(!isset($prop['sequence_key']) || $prop['sequence_key'] == false) {
				if(isset($prop['type']) && $prop['type'] != 'many-to-many' && $prop['type'] != 'one-to-many') {
					$str .= '<td bgcolor="#EFEFEF"><b>'.$name.'</b></td>' . $nl;
					$ar[] = $name;
				}
			}
		}
		$key = isset($ref->oTable->sequence_key) ? $ref->oTable->sequence_key : '';
		if($key == '') {
			$keys = $ref->oTable->getPrimaryKeys();
			if(count($keys) > 0) {
				list($key) = each($keys);
			}
		}
		
		$files = array('longblob','bytea');
		
		while($ref->fetch()) {
			$str .= '</tr>' . $nl;
			$keyValue = $ref->$key;

			foreach($ar as $n) {
				$t = $ref->oTable->getFieldProperties( $n );
				if($ref->$n=='') {
					$v = '&nbsp;';
				} else if(in_array($t['type'], $files)) {
					$file = $ref->conn->BlobDecode($ref->$n);
					if(@imagecreatefromstring($file)) {
						$v = '[ arquivo de imagem ]';
					} else {
						$v = '[ arquivo ]';
					}
				} else {
					$v = substr($ref->$n, 0, 20);
				}
				$labels = array('nome','name','label','descricao','description');
				foreach($labels as $l) {
					$x = $l.'_'.$n;
					
					if(isset($ref->$x)) {
						$v = $ref->$x;
						break;
					}
				}
				$str .= '	<td '.$bg.'><a href="?lumineAction=edit&id='.$keyValue.($offset ? "&offset=$offset" :'').'">'.$v.'</a></td>' . $nl;
			}
			$str .= '</tr>' . $nl;
		}
		
		$pg = $total / $max;
		if($pg > 0) {
			$str .= '<tr><td colspan="'.count($ar).'" '.$bg.'> Páginas: |';
			for($i=0; $i<$pg; $i++) {
				$e=$i+1;
				$s=$i*$max;
				$str .= '<a href="?offset='.$s.'"> '.$e.' </a> |';
			}
			$str .= '</td></tr>';
		}
		$str .= '</table>';
		return $str;
	}
	
	

	/**
	 * Manipulate the actions for a given class
	 * <code>
	 * $result = Util::handleAction('entities.Person', $_POST['action']);
	 * </code>
	 * @author Hugo Ferreira da Silva
	 * @param String $class Name of classe to manipulate (include package)
	 * @param String $action Name of action to execute
	 * @return Boolean
	 */
	function handleAction($class, $action) {
		$ref = Util::Import($class);
		switch(strtolower($action)) {
			case "save":
				$key  = @$ref->oTable->sequence_key;
				if($key == '') {
					$lk = $ref->oTable->getPrimaryKeys();
					if(count($lk) > 0) {
						list($key) = each($lk);
					}
				}
				$l = $ref->table();
				foreach($l as $n=>$p) {
					if(isset($p['type']) && preg_match('#\b(bool|boolean|tinyint)\b#', $p['type'])) {
						$_POST[$n] = sprintf('%d', @$_POST[$n]);
					}
					if(isset($_FILES[ $n ]) && is_uploaded_file($_FILES[ $n ]['tmp_name'])) {
						$contents = $ref->conn->BlobEncode( file_get_contents($_FILES[$n]['tmp_name']) ) ;
						$ref->$n = $contents;
					}
					if(isset($_POST[$n.'_remove_file'])) {
						$ref->$n = '';
					}
				}
				
				
				$ref->setFrom($_POST);
				if($key != '' && isset($_POST['_old_'.$key]) && $_POST['_old_'.$key] != '') {
					$ref->$key = @$_POST['_old_'.$key];
				}
				
				if(($x = $ref->validate()) === true) {
					/***************************************************/
					// salva as chaves many-to-many
					$mtm = $ref->oTable->getForeignKeys();
					foreach($mtm as $f_name => $f_prop) {
						if($f_prop['type'] == 'many-to-many') {
							$ref->removeAll( $f_name );
							$ref->$f_name = @$_POST[ $f_name ];
						}
					}
					/***************************************************/

				
					if(@$_POST['_old_'.$key] == '') {
						$ref->insert();
					} else {
						$ref->save();
					}
					
					return true;
				}
				return false;
			break;
			case "edit":
				if($ref->get($_REQUEST['id']) > 0) {
					$_POST = $ref->toArray();
					
					/***************************************************/
					// pega as chaves many-to-many
					$mtm = $ref->oTable->getForeignKeys();
					foreach($mtm as $f_name => $f_prop) {
						if($f_prop['type'] == 'many-to-many') {
							$link = $ref->getLink( $f_name );
							
							foreach($link as $item) {
								$pks = $item->oTable->getPrimaryKeys();
								$key = key($pks);
								
								$_POST[ $f_name ][] = $item->$key;
							}
						}
					}
					/***************************************************/
					return true;
				}
				return false;
			break;
			case "remove":
				if($ref->get($_REQUEST['id']) > 0) {
					$ref->delete();
					return true;
				}
				return false;
			break;
		}
	}
	
	
	/**
	 * criptografia
	 */
	function _get_rnd_iv($iv_len, $pass_len) {
		$iv = '';
		while ($iv_len-- > 0) {
			$iv .= chr($pass_len & 0xff);
		}
		return $iv;
	}
	
	function encrypt($plain_text, $obj = false, $password = false, $iv_len = 16) {
		if($password === false) {
			if(isset($obj->oTable->config->config['crypt-pass'])) {
				$password = $obj->oTable->config->config['crypt-pass'];
			}
		}
		if($password === false) {
			return $plain_text;
		}
		
		$plain_text .= "\x13";
		$n = strlen($plain_text);
		if ($n % 16) $plain_text .= str_repeat("\0", 16 - ($n % 16));
		$i = 0;
		$enc_text = Util::_get_rnd_iv($iv_len, strlen($password));
		$iv = substr($password ^ $enc_text, 0, 512);
		while ($i < $n) {
			$block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
			$enc_text .= $block;
			$iv = substr($block . $iv, 0, 512) ^ $password;
			$i += 16;
		}
		return base64_encode($enc_text);
	}
	
	function decrypt($enc_text, $obj = false, $password = false, $iv_len = 16) {
		if($password === false) {
			if(isset($obj->oTable->config->config['crypt-pass'])) {
				$password = $obj->oTable->config->config['crypt-pass'];
			}
		}
		if($password === false) {
			return $enc_text;
		}
		$enc_text = base64_decode($enc_text);
		$n = strlen($enc_text);
		$i = $iv_len;
		$plain_text = '';
		$iv = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
		while ($i < $n) {
			$block = substr($enc_text, $i, 16);
			$plain_text .= $block ^ pack('H*', md5($iv));
			$iv = substr($block . $iv, 0, 512) ^ $password;
			$i += 16;
		}
		return preg_replace('/\\x13\\x00*$/', '', $plain_text);
	}

	function toUTF8( $o ) {
		if(is_string($o)) {
			//$o = preg_replace('/([^\x09\x0A\x0D\x20-\x7F]|[\x21-\x2F]|[\x3A-\x40]|[\x5B-\x60])/e', '"&#".ord("$0").";"', $o);
			$o = utf8_encode($o);
			//$o = preg_replace('@&([a-z,A-Z,0-9]+);@e','html_entity_decode("&\\1;")',$o);
			return $o;
		}
		if(is_array($o)) {
			foreach($o as $k=>$v) {
				$o[$k] = Util::toUTF8($o[$k]);
			}
			return $o;
		}
		if(is_object($o)) {
			$l = get_object_vars($o);
			foreach($l as $k=>$v) {
				$o->$k = Util::toUTF8( $v );
			}
		}
		// padrão
		return $o;
	}

	function fromUTF8( $o ) {
		if(is_string($o)) {
			//$o = preg_replace('/([^\x09\x0A\x0D\x20-\x7F]|[\x21-\x2F]|[\x3A-\x40]|[\x5B-\x60])/e', '"&#".ord("$0").";"', $o);
			$o = utf8_decode($o);
			//$o = preg_replace('@&([a-z,A-Z,0-9]+);@e','html_entity_decode("&\\1;")',$o);
			return $o;
		}
		if(is_array($o)) {
			foreach($o as $k=>$v) {
				$o[$k] = Util::fromUTF8($o[$k]);
			}
			return $o;
		}
		if(is_object($o)) {
			$l = get_object_vars($o);
			foreach($l as $k=>$v) {
				$o->$k = Util::fromUTF8( $v );
			}
		}
		// padrão
		return $o;
	}
}

?>