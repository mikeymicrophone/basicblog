<?

define("SITE_TITLE", "jUIce - The jQuery UI Testing Center");

define("DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"]);

define("LUMINE_DIR", dirname(__FILE__)."/lib/php/lumine/");

define("SITE_PATH", dirname(__FILE__));

/**
 * HTTP links
 */

define("HTTP_HOST", "http://".$_SERVER["HTTP_HOST"]);

define("HTTP_URL", "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]);

?>