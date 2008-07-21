<?php

class UserUtil {
	
	public function exists($user) {
		return $user->count() > 0 ? true : false;
	}
	
}


?>