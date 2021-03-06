<?php
  include( "config.php" );
  include( "classes/siteResultsProvider.php" );

  if ( isset( $_GET["term"] ) ) {
    $term = $_GET["term"];
  } else {
    exit( 'Enter a search term' );
  }

  // if ( isset( $_GET["type"] ) ) {
  //   $type = $_GET["type"];
  // } else {
  //   $type="sites";
  // }

  // Ternary version
  $type = isset( $_GET["type"] ) ? $_GET["type"] : "sites";

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to Moodle</title>
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body>
  
  <div class="wrapper">
    <div class="header">
      <div class="headerContent">
        <div class="logoContainer">
          <a href="index.php"><img src="assets/img/moodle-logo.png" alt="Moodle Logo"></a>
        </div>

        <div class="searchContainer">
          <form action="search.php" method="GET">
            <div class="searchBarContainer">
              <input type="text" class="searchBox" name="term" value="<?php echo $term ?>">
              <button class="searchButton">
                <img src="assets/img/search.png" alt="Search Icon">
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="tabsContainer">
        <ul class="tabList">
          <li class="<?php echo $type == 'sites' ? 'active' : ''; ?>">
            <a href='<?php echo "search.php?term=$term&type=sites"; ?>'>
              Sites
            </a>
          </li>
          <li class="<?php echo $type == 'images' ? 'active' : ''; ?>">
            <a href='<?php echo "search.php?term=$term&type=images"; ?>'>
              Images
            </a>
          </li>
        </ul>
      </div>
    </div>

    <div class="mainResultsSection">
      <?php 
        // Get the total results
        $resultsProvider = new SiteResultsProvider( $con );
        $numResults = $resultsProvider->getNumResults( $term );

        // Show the total results
        echo "<p class='resultsCount'>$numResults results found</p>";

        // Show the search results
        echo $resultsProvider->getResultsHtml(1, 20, $term);

      ?>
    </div>

  </div>

</body>
</html>