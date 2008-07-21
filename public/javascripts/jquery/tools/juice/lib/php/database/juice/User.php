<?php
#### START AUTOCODE
/** Created By LumineReverse
 * in 2008-03-13
 * @author Hugo Ferreira da Silva
 * @link http://www.hufersil.com.br/lumine Lumine
 */

class User extends LumineBase {
	var $__tablename = '_user';
	var $__database = 'juice';
	var $id;
	var $username;
	var $password;
	var $email;
	var $team;
	var $createdate;

	// mщtodo estatico de recuperaчуo
	function staticGet($p, $k=false) {
		$cl = new User;
		$cl->get($p, $k);
		return $cl;
	}

	#### END AUTOCODE
}
?>