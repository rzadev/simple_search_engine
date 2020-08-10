<?php 
  ob_start();

  // Connect to the database
  try {
    $con = new PDO( "mysql:dbname=moodle;host=localhost", "root", "" );
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
  }
  catch( PDOException $e ) {
    echo "Connectin failed: " . $e->getMessage();
  }


?>