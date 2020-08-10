<?php
class SiteResultsProvider {
  private $con;

  public function __construct( $con ) {
    $this->con = $con;
  }

  public function getNumResults( $term ) {

    // Get the total query results and store it in a column called total
    $query = $this->con->prepare("SELECT COUNT(*) as total
                                  FROM sites WHERE title LIKE :term
                                  OR url LIKE :term
                                  OR keywords LIKE :term
                                  OR description LIKE :term");

    // Search for term with text before and/or after the term (using the %)
    $searchTerm = "%" . $term . "%";
    $query->bindParam(":term", $searchTerm);
    $query->execute();

    // Store results in associative array
    $row = $query->fetch(PDO::FETCH_ASSOC);
    return $row["total"];
  }

  public function getResultsHtml( $page, $pageSize, $term ) {
    // Return results in order
    $query = $this->con->prepare("SELECT *
    FROM sites WHERE title LIKE :term
    OR url LIKE :term
    OR keywords LIKE :term
    OR description LIKE :term
    ORDER BY clicks DESC");

    // Search for term with text before and/or after the term (using the %)
    $searchTerm = "%" . $term . "%";
    $query->bindParam(":term", $searchTerm);
    $query->execute();

    $resultsHtml = "<div class='siteResults'>";
    while($row = $query->fetch(PDO::FETCH_ASSOC)) {
      // Get the id, url, title, and description from database 
      $id = $row["id"];
      $url = $row["url"];
      $title = $row["title"];
      $description = $row["description"];

      // Trim the text if it's too long
      $title = $this->trimField( $title, 75 );
      $description = $this->trimField( $description, 200 );

      $resultsHtml .= "<div class='resultContainer'>
                        <h3 class='title'>
                          <a class='result' href='$url'>
                            $title
                          </a>
                        </h3>
                        <span class='url'>$url</span>
                        <span class='description'>$description</span>
                      </div>";
    }
    $resultsHtml .= "</div>";

    return $resultsHtml;
  }

  // Trim the text if it's too long
  private function trimField( $string, $characterLimit ) {
    $dots = strlen( $string ) > $characterLimit ? "..." : "";
    return substr( $string, 0, $characterLimit ) . $dots;
  }

}

?>