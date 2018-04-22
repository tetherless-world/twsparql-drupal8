<?php

namespace Drupal\twsparql\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

include_once('DrupalTWSparql.inc');
include_once('HttpUri.inc');

class TWSparqlController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   */
  public function content(Request $request) {
    $queryStr =  $request->getQueryString();
    parse_str($queryStr, $query);

    $copy = $_GET;
    $queryFile = $copy["query"];
    $xsltFile = $copy["xslt"];

    if(preg_match("/SELECT/i", $queryFile)>0) {
      $url = TWSparql::$engine->getEndpoint()."?query=".urlencode($queryFile);
      $content = file_get_contents($url);
      return Response::create($content, 200, ['Content-Type' => 'application/sparql-results+xml']);
    }
    else if(preg_match("/CONSTRUCT/i", $queryFile)>0) {
      $url = TWSparql::$engine->getEndpoint()."?query=".urlencode($queryFile);
      $content = file_get_contents($url);
      return Response::create($content, 200, ['Content-Type' => 'application/rdf+xml']);
    }
    else if(preg_match("/DESCRIBE/i", $queryFile)>0) {
      $url = TWSparql::$engine->getEndpoint()."?query=".urlencode($queryFile);
      $content = file_get_contents($url);
      return Response::create($content, 200, ['Content-Type' => 'application/rdf+xml']);
    }
    else if(preg_match("/ASK/i", $queryFile)>0) {
      $url = TWSparql::$engine->getEndpoint()."?query=".urlencode($queryFile);
      $content = file_get_contents($url);
      return Response::create($content, 200, ['Content-Type' => 'text/plain']);
    }

    $doc = new \DOMDocument();
    $xslt = new \XSLTProcessor();

    $xsltPath = \TWSparql::$engine->getXsltPath();
    $str = $this->twsparql_rfc_2396($xsltPath,$xsltFile)."?r=".urlencode($copy["uri"]);
    $doc->substituteEntities = TRUE;
    $doc->load($str);
    $xslt->importStylesheet($doc);

    $queryBase = \TWSparql::$engine->getQueryPath();
    $queryPath = $this->twsparql_rfc_2396($queryBase,$queryFile)."?";
    unset($copy["q"]);
    unset($copy["query"]);
    unset($copy["xslt"]);
    $first = TRUE;
    foreach($copy as $idx => $val) {
      $queryPath .= ($first ? "" : "&")."$idx=".urlencode($val);
      $first = FALSE;
    }

    $queryText = file_get_contents($queryPath);
    $endpoint = \TWSparql::$engine->getEndpoint();
    $doc->substituteEntities = TRUE;
    $doc->load($endpoint."?query=".urlencode($queryText));
    $result = $xslt->transformToXML($doc);
    file_put_contents("/tmp/something", $result);

    if(strpos($result,"<sparql")!==FALSE)
      return Response::create($result, 200, ['Content-Type' => 'application/sparql-results+xml']);
    else if(strpos($result,"<rdf:RDF")!==FALSE)
      return Response::create($result, 200, ['Content-Type' => 'application/rdf+xml']);
    else if(strpos($result,"<?xml")!==FALSE)
      return Response::create($result, 200, ['Content-Type' => 'text/xml']);
    else if(strpos($result,"<html")!==FALSE)
      return Response::create($result, 200, ['Content-Type' => 'text/html']);
    else
      return Response::create($result, 200, ['Content-Type' => 'text/plain']);
  }

  /**@brief Construct HTTPURI path
   * Construct the HTTPUri path from a base and a path that is then returned.
   * @param  string $base Base path 
   * @param  string $path Path to be used for the URI
   * @return string HTTPURI path
   */
  private function twsparql_rfc_2396($base,$path) {
    if(0===strpos($path,"doi:")) {
      $path = "http://dx.doi.org/".substr($path, 4);
    }
    return \HttpUri::parse($base)->resolve($path)->serialize();
  }
}
