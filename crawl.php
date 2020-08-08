<?php
include( "classes/DomDOcumentParser.php" );

function followLinks( $url ) {
  $parser = new DomDocumentParser( $url );

  // Get all the links
  $linkList = $parser->getLinks();

  foreach( $linkList as $link ) {
    $href = $link->getAttribute( "href" );
    echo $href . "<br>";
  }
}

$startUrl = "http://www.apple.com";
followLinks( $startUrl );

?>