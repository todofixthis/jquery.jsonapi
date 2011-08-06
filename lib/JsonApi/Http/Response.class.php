<?php
/** A standardized response from a JsonApi_Http_Client.
 *
 * @package jwt
 * @subpackage lib
 */
class JsonApi_Http_Response
{
  const
    STATUS_OK           = 200,
    STATUS_BAD_REQUEST  = 400;

  protected
    $_status,
    $_content,
    $_uri;

  /** Init the class instance.
   *
   * @param Zend_Uri_Http $Uri
   * @param int           $status  HTTP status code.
   * @param string        $content
   *
   * @return void
   */
  public function __construct( Zend_Uri_Http $Uri, $status, $content = '' )
  {
    $this->_uri     = $Uri;
    $this->_status  = (int) $status;
    $this->_content = (string) $content;
  }

  /** Accessor for $_uri.
   *
   * @return Zend_Uri_Http
   */
  public function getUri(  )
  {
    return $this->_uri;
  }

  /** Accessor for $_status.
   *
   * @param bool $asString
   *
   * @return int
   */
  public function getStatus( $asString = false )
  {
    return
      $asString
        ? Zend_Http_Response::responseCodeAsText($this->_status)
        : $this->_status;
  }

  /** Accessor for $_content.
   *
   * @return string
   */
  public function getContent(  )
  {
    return $this->_content;
  }
}