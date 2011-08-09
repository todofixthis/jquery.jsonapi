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

/** Base class for JsonApi HTTP clients.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.http
 *
 * @todo Add logging functionality.
 * @todo Probably should implement observer pattern for the previous @todos.
 */
abstract class JsonApi_Http_Client
{
  protected
    $_hostname;

  /** Send a request to the server.
   *
   * @param string $method
   * @param string $path
   * @param array  $params
   *
   * @return string server response.
   */
  abstract public function fetch( $method, $path, array $params = array() );

  /** Init the class instance.
   *
   * @param string $hostname
   *
   * @return void
   */
  public function __construct( $hostname = null )
  {
    $this->setHostname($hostname);
  }

  /** Accessor for $_hostname.
   *
   * @return string
   */
  public function getHostname(  )
  {
    return $this->_hostname;
  }

  /** Modifier for $_hostname.
   *
   * @param string $hostname
   *
   * @return JsonApi_Http_Client $this
   */
  public function setHostname( $hostname )
  {
    $this->_hostname = (string) $hostname;
    return $this;
  }

  /** Send a get request.
   *
   * @param string $path
   * @param array  $params
   *
   * @return string server response.
   */
  public function get( $path, array $params = array() )
  {
    return $this->fetch('get', $path, $params);
  }

  /** Send a post request.
   *
   * @param string $path
   * @param array  $params
   *
   * @return string server response.
   */
  public function post( $path, array $params = array() )
  {
    return $this->fetch('post', $path, $params);
  }

  /** Generate the URI to send the request to.
   *
   * @param string $hostname
   * @param string $path
   * @param array  $params
   *
   * @return Zend_Uri_Http
   */
  public function getUri( $path, array $params = array() )
  {
    if( ! $hostname = $this->getHostname() )
    {
      throw new JsonApi_Http_Client_Exception(
        'Specify a target hostname for this client before sending requests.'
      );
    }

    $Uri = Zend_Uri::factory('http://' . $hostname);

    $Uri->setPath($path);
    $Uri->setQuery($params);

    return $Uri;
  }
}