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

/** Base functionality for JsonApi API classes.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi
 */
abstract class JsonApi_Base
{
  protected
    /** @var JsonApi_Http_Client */
    $_client;

  static private

    /** @kludge PHP 5.2 does not support late static binding, but we need a way
     *   for subclasses to override the default HTTP Client (e.g., to set the
     *   hostname).
     *
     * To get around this, we'll set up a pseudo-singleton interface and make
     *  the 'getDefaultHttpClient' function an instance method.
     *
     * @todo Rewrite this a little more sanely if/when we move to PHP 5.3.
     */
    $_instances = array();

  /** Returns the HTTP client for the API call if one has not already been set.
   *
   * Subclasses should override this to specify default parameters such as
   *  hostname or custom HTTP client subclass.
   *
   * @return JsonApi_Http_Client
   */
  public function getDefaultHttpClient(  )
  {
    return new JsonApi_Http_Client_Zend();
  }

  /** Returns the HTTP client for the API call.
   *
   * @return JsonApi_Http_Client
   */
  public function getHttpClient(  )
  {
    if( ! $this->_client )
    {
      $this->_client = $this->getDefaultHttpClient();
    }

    return $this->_client;
  }

  /** Modifier for the HTTP client for the API call.
   *
   * This is generally only used to inject a mock adapter for testing.
   *
   * @param JsonApi_Http_Client $client
   *
   * @return JsonApi_Base($this)
   */
  public function setHttpClient( JsonApi_Http_Client $client )
  {
    $this->_client = $client;
    return $this;
  }

  /** Generates/returns an API instance for a given class name.
   *
   * @param string $class
   *
   * @return JsonApi_Base
   */
  static public function getInstance( $class )
  {
    if( ! isset(self::$_instances[$class]) )
    {
      $Api = new $class();

      if( ! $Api instanceof self )
      {
        throw new InvalidArgumentException(sprintf(
          '%s is not a valid JsonApi class name.',
            $class
        ));
      }

      self::$_instances[$class] = $Api;
    }

    return self::$_instances[$class];
  }

  /** Returns the URI that would be called by invoking a particular API call.
   *
   * @param string  $classname
   * @param string  $method
   * @param array   $args
   *
   * @return string
   */
  static public function getUriFor( $classname, $method, array $args = array() )
  {
    $instance = self::getInstance($classname);
    $client   = $instance->getHttpClient();

    try
    {
      /* Inject mock HTTP adapter. */
      $mock = new JsonApi_Http_Client_Mock($client->getHostname());
      $instance->setHttpClient($mock);

      /* This will generate a 404 response, but that's OK; we just need to pull
       *  out the request URL.
       */
      call_user_func_array(array($classname, $method), $args);

      $urls = $mock->getRequests();
      $uri = reset($urls);
    }
    catch( Exception $e )
    {
      /* Do not leave mock adapter installed. */
      $instance->setHttpClient($client);
      throw $e;
    }

    /* Restore HTTP client. */
    $instance->setHttpClient($client);

    /* And we're done. */
    return $uri;
  }

  /** Fire off an API call and return the JSON-decoded response.
   *
   * @param string $class Class name of the API instance to retrieve.
   * @param string $path
   * @param array  $args
   * @param string $meth 'get' or 'post'
   *
   * @return JsonApi_Response
   *
   * @todo Remove no-late-static-binding cruft if/when we port this plugin to
   *  PHP 5.3.
   */
  static protected function _doApiCall(
          $class,
          $path,
    array $args,
          $meth   = 'post'
  )
  {
    return self::getInstance($class)->getHttpClient()
      ->$meth($path, $args)
        ->makeJsonApiResponse();
  }

  /** Init the class instance.
   *
   * @access protected Use getInstance() instead.
   */
  protected function __construct(  )
  {
  }
}