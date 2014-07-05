<?php
/**
 * Class Yupi
 * Yii Unified Parser *i
 *
 * Замена Poet
 */
class Yupi
{
  static private $_instance = null;
  protected $ch = null;

  protected $_enableRetries = false;
  protected $_maxRetries = 3;
  protected $_retryIdleTime = 3;

  /**
   *
   * @return Yupi
   */
  static public function getInstance() {
    if (!self::$_instance) {
      self::$_instance = new Yupi();
    }

    return self::$_instance;
  }

  public function __construct($options = array()) {
    $this->ch = curl_init();
    curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);

    // Browser Imitations
    curl_setopt($this->ch, CURLOPT_USERAGENT,
      (isset($options['browser']))
        ? $options['browser']
        : "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");

    // Timeouts
    curl_setopt($this->ch, CURLOPT_TIMEOUT, (isset($options['timeout'])) ? $options['timeout'] : 20);
    curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, (isset($options['connect_timeout'])) ? $options['connect_timeout'] : 5);

    // SSL Connections
    if (isset($options['sslcert'])) {
      curl_setopt($this->ch, CURLOPT_SSLCERT, $options['sslcert']);
    }
    if (isset($options['sslcertpasswd'])) {
      curl_setopt($this->ch, CURLOPT_SSLCERTPASSWD, $options['sslcertpasswd']);
    }

    // Retries
    if (isset($options['enable_retries']))
      $this->_enableRetries = true;
  }

  public function __destruct() {
    curl_close($this->ch);
  }

  public function setOption($option, $value) {
    curl_setopt($this->ch, $option, $value);
    return $this;
  }

  public function get($url, $charset = false) {
    curl_setopt($this->ch, CURLOPT_POST, 0);
    curl_setopt($this->ch, CURLOPT_URL, $url);

    if ($this->_enableRetries) {
      return $this->_parsePage($this->_retry(), $charset);
    }
    else return $this->_parsePage(curl_exec($this->ch), $charset);
  }

  public function post($url, $postfields, $charset = false) {
    curl_setopt($this->ch, CURLOPT_POST, 1);
    curl_setopt($this->ch, CURLOPT_URL, $url);
    curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postfields);

    if ($this->_enableRetries) {
      return $this->_parsePage($this->_retry(), $charset);
    }
    else return $this->_parsePage(curl_exec($this->ch), $charset);
  }

  protected function _retry() {
    $retryNum = 0;
    $hasResult = false;
    $result = '';

    while (!$hasResult && $retryNum <= $this->_maxRetries) {
      $result = curl_exec($this->ch);
      if (curl_errno($this->ch)) {
        $retryNum++;
        sleep($this->_retryIdleTime);
      } else {
        $http_status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        if ($http_status == 404) {
          $retryNum++;
          sleep($this->_retryIdleTime);
        }
        else {
          $hasResult = true;
        }
      }
    }

    return $result;
  }

  public function captureByCS($capturepoint, $filetype, $fileurl) {
    return $this->post($capturepoint, array(
      'user_id' => 2,
      'hash' => '47bf95af169223616a505b104d3d8519',
      'upload_id' => 1,
      'file' => $fileurl,
      'type' => $filetype,
      'ext' => 'mobile'
    ));
  }

  protected function _parsePage($page, $charset = false) {
    $page = ($charset && $charset != 'utf-8') ? mb_convert_encoding($page, 'utf-8', $charset) : $page;
    return mb_convert_encoding($page, "HTML-ENTITIES", "utf-8");
  }

  protected function _parseHtml($html, $query) {
    $dom = new Zend_Dom_Query($html);
    return $dom->query($query);
  }

  public function decodeEntity($string) {
    return html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');
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
        $result[] = $this->decodeEntity($element->nodeValue);
      }
    }
    else {
      $result = ($elements->current()) ? $this->decodeEntity($elements->current()->nodeValue) : null;
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
}