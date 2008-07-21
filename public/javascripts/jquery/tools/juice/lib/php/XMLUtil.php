<?php

/**
 * @author Eduardo Lundgren (braeker)
 * Converter xml para array
 */

function xml2array($sFilePath) {

	if (!file_exists($sFilePath)) return false;
	
	$options = array(
	                  XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE    => true,
	                  XML_UNSERIALIZER_OPTION_ATTRIBUTES_ARRAYKEY => false
	                );
	
	$unserializer = &new XML_Unserializer($options);
	$status = $unserializer->unserialize($sFilePath, true);  
	
	if (PEAR::isError($status)) {
	    return 'Error: ' . $status->getMessage();
	} else {
	    $arrayData = $unserializer->getUnserializedData();
	    return $arrayData;
	}
	
}

?>