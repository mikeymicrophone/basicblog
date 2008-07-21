<?php
#### START AUTOCODE
/** Created By LumineReverse
 * in 2008-03-13
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine Lumine
 */

class Statistics extends LumineBase {
	var $__tablename = 'statistics';
	var $__database = 'juice';
	var $id;
	var $testid;
	var $userid;
	var $useragent;
	var $platform;
	var $engine;
	var $engineversion;
	var $version;
	var $result;
	var $createdate;
	var $ip;

	// mщtodo estatico de recuperaчуo
	function staticGet($p, $k=false) {
		$cl = new Statistics;
		$cl->get($p, $k);
		return $cl;
	}

	#### END AUTOCODE
}
?>