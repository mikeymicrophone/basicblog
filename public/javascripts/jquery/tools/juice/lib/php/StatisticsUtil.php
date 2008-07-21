<?php

class StatisticsUtil {
	
	public function exists($stat) {
		return $stat->count() > 0 ? true : false;
	}
	
}


?>