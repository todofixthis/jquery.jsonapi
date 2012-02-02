<?php
/**
 * Copyright (c) 2011 J. Walter Thompson dba JWT
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/** A standardized response from a JsonApi_Http_Client.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.http
 */
class JsonApi_Http_Response
{
  /**#@+ Corresponds to matching constant in {@see JsonApi_Response}. */
  const
    STATUS_OK   = 200,
    STATUS_FAIL = 400,
    STATUS_ERR  = 500;
  /**#@-*/

  protected
    $_status,
    $_content;

  /** @var Zend_Uri */
  protected $_uri;

  /** Init the class instance.
   *
   * @param Zend_Uri  $Uri
   * @param int       $status  HTTP status code.
   * @param string    $content
   */
  public function __construct( Zend_Uri $Uri, $status, $content = '' )
  {
    $this->_uri     = $Uri;
    $this->_status  = (int) $status;
    $this->_content = (string) $content;
  }

  /** Accessor for $_uri.
   *
   * @return Zend_Uri
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

  /** Creates a JsonApi_Response object from this HTTP response.
   *
   * @return JsonApi_Response
   */
  public function makeJsonApiResponse(  )
  {
    return JsonApi_Response::factory($this);
  }
}