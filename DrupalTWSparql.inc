<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once('common/TWSparql.inc');

/**
 * Defines a logger object for TW Sparql module to provide feedback to
 * users specific to Drupal.
 * @author pattoe 
 * @author pwest
 */
class DrupalTWSparqlLogger implements TWSparqlLogger {
  protected $config;

  public static function init() {
    TWSparql::$logger = new DrupalTWSparqlLogger();
  }
  
  public function __construct() {
    $this->config = \Drupal::config('twsparql.settings');
  }
  
  /**
   * Return whether debugging is turned on or not
   */
  public function shouldDebug() {
    return $this->config->get('enable_debug', FALSE) ;
  }

  /**
   * Set whether or not we should debug
   */
  public function enableDebug($val) {
  }

  /**
   * Logs a string as an error.
   * @param string $str String to log to the error console. 
   */
  public function logError($str) {
    if( $this->shouldDebug() ) {
      drupal_set_message( $str, 'error' ) ;
    }
  }
  
  /**
   * Logs a string as a warning
   * @param string $str String to log to the warning console. 
   */
  public function logWarning($str) {
    if( $this->shouldDebug() ) {
      drupal_set_message( $str, 'warning' ) ;
    }
  }
  
  /**
   * Logs a string as a status
   * @param string $str String to log to the status console. 
   */
  public function logStatus($str) {
    if( $this->shouldDebug() ) {
      #drupal_set_message( $str, 'status' ) ;
      \Drupal::messenger()->addError($str);
    }
  }
}

/**
 * Defines Drupal specific cache object for the TWSparql module to cache intermediate and final results of a query.
 * @author pattoe
 * @author pwest
 */
class DrupalTWSparqlCache implements TWSparqlCache {
  public static function init() {
    TWSparql::$cache = new DrupalTWSparqlCache();
  }
  
  /**
   * Caches a query result document for a particular URI
   * @param string $queryUri A URI that 'executes' the query file to get
   * the query text
   * @param string $results The query text returned by dereferencing $queryUri 
   */
  public function cacheQueryResult($pageid, $queryUri, $endpoint, $results)
  {
    return;
  }
  
  /**
   * Caches the final transformation of a query using an XSL template.
   * @param string $queryUri The URI used to generate the SPARQL results
   * @param string $xsltUri The URI used to dereference the XSL stylesheet
   * @param string $result The final result applying the transformation named by
   * $xsltUri to the document returned by deferencing $queryUri.
   * @param string $expires An optional duration to wait before expiring the
   * result; defaults to TWSparql::getEngine()->getCacheTTL()
   */
  public function cacheFinal($pageid, $queryUri, $endpoint, $xsltUri, $result)
  {
    return;
  }
  
  /**
   * Clears the cached copy of a query result
   *
   * if any of the parameters are specified then clear just the row(s) that
   * contain those values.
   *
   * if none of the parameters are specified then clear  all rows
   *
   * @param string $pageid 
   * @param string $queryUri 
   * @param string $endpoint 
   */
  public function clearQueryResultCache($pageid, $queryUri, $endpoint)
  {
    return;
  }

  public function clearFinalCache($pageid, $queryUri, $endpoint, $xsltUri)
  {
  }
  
  /**
   * See if the date sent plus the expires value is less than now
   *
   * @param string $cachedon when the value was cached
   * @param string $expires length of time the cached value is valid
   * @return boolean true if expired false otherwise
   */
  private function isexpired( $cachedon, $expires )
  {
    if( $expires == 0 || $expires == "0" )
    {
        return true ;
    }

    $breakdown = explode( " ", $expires ) ;
    $num = $breakdown[0] ;
    $unit = $breakdown[1] ;
    switch( $unit )
    {
        case "H":
            $use_unit = "hour" ;
            break ;
        case "D":
            $use_unit = "day" ;
            break ;
        case "M":
            $use_unit = "month" ;
            break ;
    }

    $expire_date = new DateTime( $cachedon ) ;
    if( !$expire_date )
    {
        return true ;
    }

    $expire_date->modify( "+$num $use_unit" ) ;
    $now = new DateTime() ;

    $result = true ;
    if( $now <= $expire_date ) $result = false ;
    return $result ;
  }

  /**
   * Gets the cached content for $queryUri, or NULL if no content is available.
   * @param string $queryUri 
   */
  public function getCachedQueryResult($pageid, $queryUri, $endpoint, $expires)
  {
    return NULL;
  }

  public function getCachedFinal($pageid, $queryUri, $endpoint, $xsltUri,
                                 $expires)
  {
    return NULL;
  }
}

class DrupalTWSparql extends TWSparql {
  /**
   * Object with configuration for geshifilter.
   *
   * @var object
   */
  protected $config;

  public static function init() {
    TWSparql::$engine = new DrupalTWSparql();
  }

  public function __construct() {
    $this->config = \Drupal::config('twsparql.settings');
  }
  
  public function shouldDebug() {
    return TWSparql::getLogger()->shouldDebug() ;
  }
  
  public function enableDebug($val) {
    TWSparql::getLogger()->enableDebug($val) ;
  }
  
  public function shouldEnablePHPExtensions() {
    return $this->config->get('enable_php_xslt_extensions', FALSE);
  }

  public function enablePHPExtensions($val) {
  }
  
  public function getSiteBase() {
    #return $this->config->get('site_base', '/drupal-8.2.3');
    return 'http://localhost/~westp/drupal-8.2.3';
  }

  public function getIbase() {
    return $this->config->get('instances_uri', 'http://tw.rpi.edu/instances');
  }

  public function setIbase($val) {
  }
  
  public function getSbase() {
    return $this->config->get('schema_uri', 'https://tw.rpi.edu/schema.owl');
  }

  public function setSbase($val) {
  }
  
  public function getXsltPath() {
    return $this->config->get('xslt', 'https://tw.rpi.edu/xslt');
  }

  public function setXsltPath($val) {
  }
  
  public function getQueryPath() {
    return $this->config->get('queries', 'https://tw.rpi.edu/queries');
  }

  public function setQueryPath($val) {
  }
  
  public function getDefaultXsl() {
    return $this->config->get('default_transform', 'https://tw.rpi.edu/queries');
  }

  public function setDefaultXsl($val) {
  }
  
  public function getEndpoint() {
    return $this->config->get('endpoint', 'https://tw.rpi.edu:2020/books');
  }

  public function setEndpoint($val) {
  }
  
  public function getCacheTTL() {
    return '1 D';
  }

  public function setCacheTTL($val) {
  }
  
  public function getCacheEnabled() {
    return FALSE;
  }

  public function setCacheEnabled($val) {
  }
}

// Could have just used the DrupalTWSparql static function init to
// initialize all three of these, but could be the case where someone
// wants to use different loggers and caching mechanisms instead of the
// Drupal one here.
DrupalTWSparql::init();
DrupalTWSparqlLogger::init();
DrupalTWSparqlCache::init();

