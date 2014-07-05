<?php

class Poet {
  static private $_instance = null;
  protected $http = null;
  
  /**
   *
   * @return Poet
   */
  static public function getInstance() {
    if (!self::$_instance) {
      self::$_instance = new Poet();
    }
    
    return self::$_instance;
  }
  
  public function __construct() {
    $this->http = new Zend_Http_Client();
    $this->http->setCookieJar();
    $this->http->setConfig(array(
      'timeout' => 20,
      'keepalive' => true,
    ));

    $this->http->setHeaders(array(
        'User-Agent'      =>  'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)',
        'Accept-encoding' =>  'gzip,deflate',
        'Accept'          =>  'text/html, application/xhtml+xml, */*',
        'Referer'         =>  'http://zakupki.gov.ru/pgz/public/action/search/simple/run',
    ));
  }

  public function reset() {
    $this->http->resetParameters();
  }

  public function get($uri, $charset = false, $nohtml = true) {
    $this->http->setUri($uri);
    
    $body = ($charset) ? mb_convert_encoding($this->http->request(Zend_Http_Client::GET)->getBody(), "utf-8", $charset) : $this->http->request(Zend_Http_Client::GET)->getBody();
    //@Mark: Zend_Dom_Query херово обрабатывает русские символы, поэтому необходима такая надстройка
    return ($nohtml) ? mb_convert_encoding($body, "HTML-ENTITIES", "utf-8") : mb_convert_encoding($body, $charset, "utf-8");
  }
  
  public function post($uri, $data = array()) {
    $this->http->setUri($uri);
    
    foreach ($data as $key => $value) {
      $this->http->setParameterPost($key, $value);
    }
    
    return mb_convert_encoding($this->http->request(Zend_Http_Client::POST)->getBody(), "HTML-ENTITIES", "utf-8");
  }

  public function upload($uri, $filename, $filedata, $data = array()) {
    $this->http->setUri($uri);

    foreach ($data as $key => $value) {
      $this->http->setParameterPost($key, $value);
    }

    $this->http->setFileUpload($filename, 'Filedata', $filedata);

    return mb_convert_encoding($this->http->request(Zend_Http_Client::POST)->getBody(), "HTML-ENTITIES", "utf-8");
  }

  public function convert($html) {
    return mb_convert_encoding($html, "HTML-ENTITIES", "utf-8");
  }
  
  protected function _parseHtml($html, $query) {
    $dom = new Zend_Dom_Query($html);
    return $dom->query($query);
  }
  
  public function parseToElement($html, $query) {
    $elements = $this->_parseHtml($html, $query);
    $result = array();
    
    if (count($elements) > 1) {
      foreach ($elements as $element) {
        $result[] = $element;
      }
    }
    else $result = $elements->current();
    
    return $result;
  }
  
  public function parseToValue($html, $query) {
    $elements = $this->_parseHtml($html, $query);
    $result = array();
    
    if (count($elements) > 1) {
      foreach ($elements as $element) {
        $result[] = $element->nodeValue;
      }
    }
    else {
        $result = ($elements->current()) ? $elements->current()->nodeValue : null;
    }
    
    return $result;
  }
  
  public function parseToHtml($html, $query) {
    $elements = $this->_parseHtml($html, $query);
    $result = array();
    
    if (count($elements) > 1) {
      foreach ($elements as $element) {
        $doc = new DOMDocument();
        $doc->appendChild($doc->importNode($element, true));

        $result[] = $doc->saveHTML();
      }
    }
    else {
      $doc = new DOMDocument();

      if ($elements->current()) {
          $doc->appendChild($doc->importNode($elements->current(), true));
          $result = $doc->saveHTML();
      }
      else $result = null;
    }
    
    return $result;
  }

    public function morphPlural($int, $forms)
    {
        $int = abs($int) % 100;
        $int1 = $int % 10;
        if ($int > 10 && $int < 20) return $forms[2];
        if ($int1 > 1 && $int1 < 5) return $forms[1];
        if ($int1 == 1) return $forms[0];
        return $forms[2];
    }
}

function html_entity_decode_utf8($string)
{
  static $trans_tbl;

  // replace numeric entities
  $string = preg_replace('~&#x([0-9a-f]+);~ei', 'code2utf(hexdec("\\1"))', $string);
  $string = preg_replace('~&#([0-9]+);~e', 'code2utf(\\1)', $string);

  // replace literal entities
  if (!isset($trans_tbl))
  {
    $trans_tbl = array();
    foreach (get_html_translation_table(HTML_ENTITIES) as $val=>$key)
      $trans_tbl[$key] = utf8_encode($val);
  }
  return strtr($string, $trans_tbl);
}

function code2utf($num)
{
  if ($num < 128) return chr($num);
  if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
  if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
  if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
  return '';
}