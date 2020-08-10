<?php
  class DomDocumentParser {

    private $doc;

    public function __construct( $url ) {
      $options = array(
        'http'=>array( 'method'=>"GET", 'header'=>"User-Agent: moodleBot/0.1\n" )
      );
      $context = stream_context_create( $options );

      $this->doc = new DomDocument();
      @$this->doc->loadHTML(file_get_contents( $url, false, $context ));
    }

    // get all the links (a tags)
    public function getlinks() {
      return $this->doc->getElementsByTagName("a");
    }

    // get the website title
    public function getTitletags() {
      return $this->doc->getElementsByTagName("title");
    }

    // get the website meta tags
    public function getMetaTags() {
      return $this->doc->getElementsByTagName("meta");
    }

    // get the images on the website
    public function getImages() {
      return $this->doc->getElementsByTagName("img");
    }
  }

?>