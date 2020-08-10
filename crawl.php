<?php
include( "config.php" );
include( "classes/DomDOcumentParser.php" );

$alreadyCrawled = array();
$crawling = array();
$alreadyFoundImages = array();


// Check if there are duplicate links
function linkExists( $url ) {
  global $con;

  $query = $con->prepare("SELECT * FROM sites WHERE url = :url");
  
  $query->bindParam(":url", $url);
  $query->execute();

  return $query->rowCount() != 0;
}


// Insert crawl data to the database
function insertLink( $url, $title, $description, $keywords ) {
  global $con;

  // Use prepare, more secure from SQL injection
  $query = $con->prepare("INSERT INTO sites(url, title, description, keywords)
                          VALUES(:url, :title, :description, :keywords)");
  
  $query->bindParam(":url", $url);
  $query->bindParam(":title", $title);
  $query->bindParam(":description", $description);
  $query->bindParam(":keywords", $keywords);

  return $query->execute();
}


// Insert image to database
function insertImage( $url, $src, $alt, $title ) {
  global $con;

  // Use prepare, more secure from SQL injection
  $query = $con->prepare("INSERT INTO images(siteUrl, imageUrl, alt, title)
                          VALUES(:siteUrl, :imageUrl, :alt, :title)");
  
  $query->bindParam(":siteUrl", $url);
  $query->bindParam(":imageUrl", $src);
  $query->bindParam(":alt", $alt);
  $query->bindParam(":title", $title);

  return $query->execute();
}


// Create absolute link from relative link
function createLink( $src, $url ) {
  $scheme = parse_url( $url )["scheme"]; // http
  $host = parse_url( $url )["host"]; // www.url.com

  // Result scenario = //www.url.com -> http://www.url.com
  if ( substr( $src, 0, 2 ) == "//" ) {
    $src = $scheme . ":" . $src;
  }
  // Result scenario = /about/aboutus.php -> http://www.url.com/about/aboutus.php
  else if ( substr( $src, 0, 1 ) == "/" ) {
    $src = $scheme . "://" . $host . $src;
  }
  // Result scenario = ./about/aboutus.php -> http://www.url.com/about/aboutus.php
  else if ( substr( $src, 0, 2 ) == "./" ) {
    $src = $scheme . "://" . $host . dirname( parse_url( $url )["path"] ) . substr( $src, 1 );
  }
  // Result scenario = ../about/aboutus.php -> http://www.url.com/about/aboutus.php
  else if ( substr( $src, 0, 3 ) == "../" ) {
    $src = $scheme . "://" . $host . "/" . $src;
  }
  // Result scenario = about/aboutus.php -> http://www.url.com/about/aboutus.php
  else if ( substr( $src, 0, 5 ) != "https" && substr( $src, 0, 4 ) != "http" ) {
    $src = $scheme . "://" . $host . "/" . $src;
  }

  return $src;
}


// Get website details
function getDetails( $url ) {
  global $alreadyFoundImages;

  $parser = new DomDocumentParser( $url );

  $titleArray = $parser->getTitletags();

  // return If there is no page title
  if ( sizeof($titleArray) == 0 || $titleArray->item(0)  == NULL ) {
    return;
  }

  $title = $titleArray->item(0)->nodeValue;
  $title = str_replace( "\n", "", $title );

  if ( $title == "" ) {
    return;
  }

  // Get the website keywords and description meta
  $description = "";
  $keywords = "";

  $metasArray = $parser->getMetaTags();

  foreach ($metasArray as $meta ) {
    // Get the description meta
    if ( $meta->getAttribute( "name" ) == "description" ) {
      $description = $meta->getAttribute( "content" );
    }
    
    // Get the keywords meta
    if ( $meta->getAttribute( "name" ) == "keywords" ) {
      $keywords = $meta->getAttribute( "content" );
    }
  }

  $description = str_replace( "\n", "", $description );
  $keywords = str_replace( "\n", "", $keywords );

  // echo "URL: $url, Description: $description, Keywords: $keywords <br>";

  // Check if there are duplicate links and insert data to the database 
  if ( linkExists( $url ) ) {
    echo "$url already exists <br>";
  }
  else if( insertLink( $url, $title, $description, $keywords ) ) {
    echo "Success: $url <br>";
  }
  else {
    echo "Error, failed to insertb $url <br>";
  }

  // Get the images
  $imageArray = $parser->getImages();
  foreach ( $imageArray as $image ) {
    $src = $image->getAttribute( "src" );
    $alt = $image->getAttribute( "alt" );
    $title = $image->getAttribute( "title" );

    if ( !$title && !$alt ) {
      continue;
    }

    // Convert the images relative link to absolute link
    $src = createLink( $src, $url );

    if ( !in_array( $src, $alreadyFoundImages ) ) {
      // Insert the image url to the array
      $alreadyFoundImages[] = $src;

      // Insert the image to database
      echo "Insert: " . insertImage( $url, $src, $alt, $title ) ."<br>";
    }
  }
}


function followLinks( $url ) {
  global $alreadyCrawled;
  global $crawling;

  $parser = new DomDocumentParser( $url );

  // Get all the links on the website
  $linkList = $parser->getLinks();

  foreach( $linkList as $link ) {
    $href = $link->getAttribute( "href" );

    // exclude #
    if ( strpos( $href, "#" ) !== false ) {
      continue;
    } 
    // exclude javascript:
    else if ( substr( $href, 0, 11 )  == "javascript:" ) {
        continue;
    }

    $href = createLink( $href, $url );

    // Recursively crawling links
    if ( !in_array( $href, $alreadyCrawled ) ) {
      $alreadyCrawled[] = $href;
      $crawling[] = $href;

      getDetails( $href );
    }
    // else return;  // Limit the results

    // echo $href . "<br>";
  }

  array_shift( $crawling );

  foreach ($crawling as $site) {
    followLinks( $site );
  }
}

$startUrl = "https://www.perropet.com";
followLinks( $startUrl );

// Crawled Links
// http://www.vetstreet.com
// https://www.perropet.com
?>