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
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi
 */
abstract class JsonApi_Base
{
  const
    STATUS_OK     = 'OK',
    STATUS_ERROR  = 'ERROR';

  private
    $_httpClient;

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

  /** Sets the default HTTP client and hostname.
   *
   * Most every subclass will `return new JsonApi_Http_Client_Zend($hostname)`,
   *  where $hostname is the hostname of the JsonApi server for that service.
   *
   * @return JsonApi_Http_Client
   */
  abstract public function getDefaultHttpClient(  );

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

  /** Accessor for $_httpClient.
   *
   * @return JsonApi_Http_Client
   */
  public function getHttpClient(  )
  {
    if( ! isset($this->_httpClient) )
    {
      $this->_httpClient = $this->getDefaultHttpClient();
    }

    return $this->_httpClient;
  }

  /** Modifier for $_httpClient.
   *
   * @param JsonApi_Http_Client $Client
   *
   * @return null|JsonApi_Http_Client previous HTTP client.
   */
  public function setHttpClient( JsonApi_Http_Client $Client )
  {
    $old = $this->_httpClient;
    $this->_httpClient = $Client;
    return $old;
  }

  /** Generates a signature for an array of parameters.
   *
   * A signature is composed of 2 or 3 elements:
   *  - _timestamp: Limits the window of opportunity for an attacker to re-use
   *     or exploit an intercepted API request.
   *  - _signature: The actual signature.
   *  - _salt:      Optional (but recommended) random string increases entropy.
   *
   * @param array   $params
   * @param string  $publicKey
   * @param bool    $returnAll If true, returns the entire array with signature
   *  element.  If false, only returns the signature value.
   * @param bool    $addSalt   If true, a salt element will be added to increase
   *  entropy.  Ignored if $returnAll is false.
   *
   * Note that a timestamp is always required unless %APP_JSONAPI_REQUEST_TTL%
   *  is 0.
   *
   * For best results, the value of %APP_JSONAPI_REQUEST_TTL% should be
   *  identical on all servers that use JsonApi, but it is not technically
   *  required for the API to function properly.
   *
   * @return array|string
   * @throws DomainException if $returnAll is false, and the request contains
   *  an invalid (or missing) timestamp, depending on the value of
   *  %APP_JSONAPI_REQUEST_TTL%.
   */
  static public function generateSignature(
    array $params,
          $publicKey,
          $returnAll = true,
          $addSalt   = true
  )
  {
    /* Convert each value to a string before generating the signature; that is
     *  how the values will be evaluated on the other server.
     */
    array_walk_recursive(
      $params,
      create_function('&$val, $key', '$val = (string) $val;')
    );

    /* Operate on a copy of $params (to avoid returning an incorrectly-
     *  truncated $params if $returnAll is true.
     */
    $copy = $params;

    /* Remove Symfony-specific values and any existing signature. */
    unset($copy['_signature'], $copy['action'], $copy['module']);
    foreach( array_keys($copy) as $key )
    {
      if( substr($key, 0, 3) == 'sf_' )
      {
        unset($copy[$key]);
      }
    }

    /* Add salt if applicable. */
    if( $returnAll and $addSalt )
    {
      $params['_salt']  =
      $copy['_salt']    = sha1(uniqid('', true));
    }

    /* Add/validate timestamp, if applicable. */
    if( $returnAll )
    {
      $params['_timestamp'] =
      $copy['_timestamp']   = (string) time();
    }
    elseif( $maxAge = max(0, (int) sfConfig::get('app_jsonapi_request_ttl')) )
    {
      if( empty($copy['_timestamp']) )
      {
        throw new DomainException('Request does not have a timestamp.');
      }
      elseif( ! ctype_digit($copy['_timestamp']) )
      {
        throw new DomainException('Request timestamp is invalid.');
      }
      elseif( (time() - $copy['_timestamp']) > $maxAge )
      {
        throw new DomainException('Request timestamp is expired.');
      }
    }

    /* Sort keys so that key order does not affect signature. */
    ksort($copy);

    /* Generate the signature. */
    $params['_signature'] = sha1(trim($publicKey) . serialize($copy));

    return $returnAll ? $params : $params['_signature'];
  }

  /** Fire off an API call and return the JSON-decoded response.
   *
   * @param string $class Class name of the API instance to retrieve.
   * @param string $path
   * @param array  $args
   * @param string $meth 'get' or 'post'
   *
   * @return JsonApi_Response
   */
  static protected function _doApiCall( $class, $path, array $args, $meth )
  {
    return JsonApi_Response::factory(
      self::getInstance($class)->getHttpClient()->$meth($path, $args)
    );
  }

  /** Init the class instance.
   *
   * @return void
   * @access protected Use getInstance() instead.
   */
  protected function __construct(  )
  {
  }
}