<?
/**
 * Starting session
 */
session_start();

/**
 * PHP settings
 */

setlocale (LC_TIME,"en");
ini_set('error_reporting',E_ALL & ~E_NOTICE);

/**
 * User settings
 */
require("settings.php");

/**
 * Include paths
 */
set_include_path(
		get_include_path().PATH_SEPARATOR.
		SITE_PATH.'/'.PATH_SEPARATOR.
		SITE_PATH.'/lib/'.PATH_SEPARATOR.
		SITE_PATH.'/lib/php/'.PATH_SEPARATOR.
		SITE_PATH.'/lib/php/database/'.PATH_SEPARATOR.
		SITE_PATH.'/lib/php/PEAR/'.PATH_SEPARATOR.
		SITE_PATH.'/lib/php/PEAR/XML_Serializer/'.PATH_SEPARATOR.
		SITE_PATH.'/lib/php/PEAR/XML_Util/'.PATH_SEPARATOR.
		SITE_PATH.'/lib/php/PEAR/XML_Parser/'.PATH_SEPARATOR
);

/**
 * PHP Libraries
 */
require_once(LUMINE_DIR."LumineConfiguration.php");
require_once("lumine-conf.php");
require_once("UserUtil.php");
require_once("StatisticsUtil.php");
require_once("XMLUtil.php");
require_once("RenderPortlet.php");
require_once("Serializer.php");
require_once("Unserializer.php");
require_once("Util.php");
require_once("Parser.php");
/**
 * Lumine config
 */
$conf = new LumineConfiguration( $lumineConfig );

Util::Import('juice.User');
Util::Import('juice.Statistics');
Util::Import('juice.Tests');

/**
 * Render Layout
 */
$Render = new RenderPortlet("content-mapping.xml");
?>