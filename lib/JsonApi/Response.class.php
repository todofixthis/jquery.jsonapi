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

/** Generates a response object for JsonApi calls.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi
 */
abstract class JsonApi_Response
{
  const
    STATUS_OK   = 'ok',
    STATUS_FAIL = 'fail',
    STATUS_ERR  = 'err',

    KEY_STATUS  = 'status',
    KEY_DETAIL  = 'detail';

  private
    $_response,
    $_props;

  /** Generate a response object from the API call response.
   *
   * @param JsonApi_Http_Response $response
   *
   * @return JsonApi_Response
   */
  static public function factory( JsonApi_Http_Response $response )
  {
    try
    {
      switch( $response->getStatus() )
      {
        case JsonApi_Http_Response::STATUS_OK:
          $class = 'JsonApi_Response_Success';
        break;

        case JsonApi_Http_Response::STATUS_BAD_REQUEST:
          $class = 'JsonApi_Response_Failure';
        break;

        default:
          throw new JsonApi_Response_Exception(
            sprintf(
              'Unrecognized HTTP status code:  %s (%s).',
                $response->getStatus(),
                $response->getStatus(true)
            ),
            $response
          );
        break;
      }

      return new $class($response);
    }
    catch( JsonApi_Response_Exception $e )
    {
      $result = new JsonApi_Response_Error($response);
      $result->attachException($e);

      return $result;
    }
  }

  /** Init the class instance.
   *
   * @param JsonApi_Http_Response $response
   *
   * @return void
   * @access protected use factory() to create a new instance.
   *
   * @final Override {@see _initialize()} to customize subclass initialization.
   */
  final protected function __construct( JsonApi_Http_Response $response )
  {
    $this->_response = $response;
    $this->_props    = new sfParameterHolder();

    /* Decode the JSON-encoded content and assign detail parameters. */
    $decoded = $this->_decodeJson($response->getContent());

    $key = self::KEY_DETAIL;
    if( ! empty($decoded->$key) )
    {
      $this->getPropertiesObject()->add((array) $decoded->$key);
    }

    /* Perform subclass-specific initialization. */
    $this->_initialize($decoded);
  }

  /** Post-constructor initialization.  Override this method in subclasses.
   *
   * @param stdClass $response Decoded JSON response.
   *
   * @return void
   */
  protected function _initialize( stdClass $response )
  {
  }

  /** Accessor for $_response.
   *
   * @return JsonApi_Http_Response
   */
  public function getResponseObject(  )
  {
    return $this->_response;
  }

  /** Shortcut for calling getUri() on $_response.
   *
   * @return Zend_Uri_Http
   */
  public function getUri(  )
  {
    return $this->getResponseObject()->getUri();
  }

  /** Accessor for $_props.
   *
   * @return sfParameterHolder
   * @access protected only subclasses should be able to interface with $_props
   *  directly.
   */
  protected function getPropertiesObject(  )
  {
    return $this->_props;
  }

  /** Returns all properties from the response.
   *
   * @return array
   */
  public function getAllProperties(  )
  {
    return $this->getPropertiesObject()->getAll();
  }

  /** Generic accessor for $_props.
   *
   * @param string $key
   *
   * @return mixed
   */
  public function __get( $key )
  {
    return $this->getPropertiesObject()->get($key);
  }

  /** Generic isset() handler for $_props.
   *
   * @param string $key
   *
   * @return mixed
   */
  public function __isset( $key )
  {
    return $this->getPropertiesObject()->has($key);
  }

  /** Decodes JSON from a JsonApi response.
   *
   * @param string $content
   *
   * @return stdClass
   * @throws JsonApi_Response_Exception if $content is not well-formed.
   */
  protected function _decodeJson( $content )
  {
    if( ! $decoded = json_decode($content) )
    {
    }

    return $decoded;
  }
}