<?
session_start();
require_once("../../init.php");
$_SESSION["__user"] = null;
header("Location: ../../?login=0");
?>