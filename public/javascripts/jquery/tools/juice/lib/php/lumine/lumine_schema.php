<?
      require_once 'LumineSchema.php';
      
      require_once("LumineConfiguration.php");
require_once("../../database/lumine-conf.php");

/**
 * Lumine conf
 */
$conf = new LumineConfiguration( $lumineConfig );

      $schema = new LumineSchema( '../../seu-arquivo-conf.xml' );
      $schema->createSchema();
?>