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

/** A wrapper for Zend_Http_Client for use by JsonApi.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.http.client
 *
 * @todo Add observer so that we can inject logging.
 */
class JsonApi_Http_Client_Zend extends JsonApi_Http_Client
{
  private
    $_config;

  /** Init the class instance.
   *
   * @param string  $hostname
   * @param array   $config   Configuration for Zend_Http_Client instances.
   *
   * @return void
   */
  public function __construct( $hostname = null, array $config = array() )
  {
    parent::__construct($hostname);

    $this->_config = $config;
  }

  /** Send a request to the server.
   *
   * @param string  $method
   * @param string  $path
   * @param array   $params
   * @param array   $config Override Zend_Http_Client config for this request.
   *
   * @return string server response.
   */
  public function fetch( $method, $path, array $params = array(), array $config = array() )
  {
    $Uri = $this->getUri($path, $params, $method);

    $Client = new Zend_Http_Client($Uri, array_merge($this->_config, $config));

    $method = strtoupper($method);

    if( $params )
    {
      $meth = 'setParameter' . ucfirst(strtolower($method));

      foreach( $params as $key => $val )
      {
        $Client->$meth($key, $val);
      }
    }

    try
    {
      $Response = $Client->request($method);
    }
    catch( Exception $e )
    {
      /* Add more information to the exception message. */
      throw new JsonApi_Http_Client_Exception(
        sprintf(
          'Got %s when requesting %s via %s:  "%s"',
            get_class($e),
            (string) $Uri,
            $method,
            $e->getMessage()
        ),
        $e->getCode()
      );
    }

    return new JsonApi_Http_Response(
      $Uri,
      $Response->getStatus(),
      $Response->getBody()
    );
  }
}
