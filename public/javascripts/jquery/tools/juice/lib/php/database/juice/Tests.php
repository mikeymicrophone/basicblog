<?php
#### START AUTOCODE
/** Created By LumineReverse
 * in 2008-03-13
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine Lumine
 */

class Tests extends LumineBase {
	var $__tablename = 'tests';
	var $__database = 'juice';
	var $id;
	var $title;
	var $category;
	var $code;
	var $template;
	var $enabled;
	var $createdate;

	// mщtodo estatico de recuperaчуo
	function staticGet($p, $k=false) {
		$cl = new Tests;
		$cl->get($p, $k);
		return $cl;
	}

	#### END AUTOCODE
}
?>