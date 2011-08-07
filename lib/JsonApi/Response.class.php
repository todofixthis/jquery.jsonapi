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
    ERR_NO_EXCEPTION      = '(no exception message available)',
    ERR_NO_STATUS         = 'No status property returned from API server.',
    ERR_INVALID_STATUS    = 'Invalid status property returned from API server:  "%s" expected, "%s" found',
    ERR_NON_JSON_RESPONSE = 'Server returned %d status, but response is not JSON-encoded.',

    KEY_STATUS    = 'status',
    KEY_ERRORS    = 'errors',
    KEY_EXCEPTION = '_exception';

  private
    $_response,
    $_props;

  /** Generate a response object from the API call response.
   *
   * @param JsonApi_Http_Response $Response
   *
   * @return JsonApi_Response
   */
  static public function factory( JsonApi_Http_Response $Response )
  {
    switch( $Response->getStatus() )
    {
      case JsonApi_Http_Response::STATUS_OK:
        $class = 'JsonApi_Response_Success';
      break;

      case JsonApi_Http_Response::STATUS_BAD_REQUEST:
        $class = 'JsonApi_Response_Failure';
      break;

      default:
        $class = 'JsonApi_Response_Error';
      break;
    }

    return new $class($Response);
  }

  /** Init the class instance.
   *
   * @param JsonApi_Http_Response $Response
   *
   * @return void
   * @access protected use factory() to create a new instance.
   */
  final protected function __construct( JsonApi_Http_Response $Response )
  {
    $this->_response = $Response;
    $this->_props    = new sfParameterHolder();

    $this->_initialize();
  }

  /** Post-constructor initialization.  Override this method in subclasses.
   *
   * @return void
   */
  protected function _initialize(  )
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

  /* Note:  __set() and __unset() excluded purposely. */

  /** Converts the response object into an exception and throws it.
   *
   * @param string $message Custom error message to specify (default is keys
   *  of $this->errors).
   *
   * @return void
   * @throws JsonApi_Response_Exception
   */
  public function throwException( $message = null )
  {
    $errors = (array) $this->getPropertiesObject()->get(self::KEY_ERRORS);

    throw new JsonApi_Response_Exception(
      is_null($message)
        ? isset($errors[self::KEY_EXCEPTION])
            ? $errors[self::KEY_EXCEPTION]
            : self::ERR_NO_EXCEPTION
        : (string) $message,
      $this->getResponseObject()
    );
  }

  /** Extracts the JSON-encoded object from the response body and populates the
   *   properties object.
   *
   * @param string $expected Expected value of 'status' in encoded object.
   *
   * @return void
   */
  protected function _importPropertiesFromResponse( $expected = null )
  {
    /* Verify valid JSON content. */
    if( $decoded = json_decode($this->getResponseObject()->getContent()) )
    {
      /* Validate status value. */
      if( empty($decoded->{self::KEY_STATUS}) )
      {
        $decoded = $this->_genExceptionResponse(self::ERR_NO_STATUS);
      }
      elseif( $expected and $decoded->{self::KEY_STATUS} != $expected )
      {
        $decoded = $this->_genExceptionResponse(
          sprintf(
            self::ERR_INVALID_STATUS,
              $expected,
              $decoded->{self::KEY_STATUS}
          )
        );
      }
    }
    else
    {
      $decoded = $this->_genExceptionResponse(
        sprintf(
          self::ERR_NON_JSON_RESPONSE,
            $this->getResponseObject()->getStatus()
        )
      );
    }

    $decoded = (array) $decoded;
    if( $decoded[self::KEY_STATUS] == JsonApi_Base::STATUS_ERROR )
    {
      $decoded[self::KEY_ERRORS] =
        isset($decoded[self::KEY_ERRORS])
          ? (array) $decoded[self::KEY_ERRORS]
          : array();
    }

    $this->getPropertiesObject()->add($decoded);
  }

  /** Generates a simulated exception response.
   *
   * @param string $message The exception message.
   *
   * @return array
   */
  protected function _genExceptionResponse( $message )
  {
    return array(
      'status'  => JsonApi_Base::STATUS_ERROR,
      'errors'  => array(
        '_exception' => (string) $message
      )
    );
  }
}