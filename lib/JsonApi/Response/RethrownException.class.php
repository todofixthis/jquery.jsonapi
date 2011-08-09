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

/** An exception that was caught by JsonApi_Response::factory(), but rethrown
 *    in the application code.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.response
 */
class JsonApi_Response_RethrownException extends JsonApi_Response_Exception
{
  protected
    /** @var JsonApi_Http_Response */
    $_response,

    /** @var JsonApi_Exception */
    $_exception;

  /** Init the class instance.
   *
   * @param JsonApi_Http_Response $response
   * @param JsonApi_Exception     $exception
   *
   * @return void
   */
  public function __construct( JsonApi_Http_Response $response, JsonApi_Exception $exception )
  {
    $this->_response  = $response;
    $this->_exception = $exception;

    parent::__construct(
      sprintf(
        'Got %s "%s" when requesting %s (%d %s).',
          get_class($exception),
          $exception->getMessage(),
          $response->getUri(),
          $response->getStatus(),
          $response->getStatus(true)
      ),
      $response->getStatus(),
      $exception
    );
  }

  /** Returns the response object that was the cause of all the trouble.
   *
   * @return JsonApi_Http_Response
   */
  public function getResponseObject(  )
  {
    return $this->_response;
  }

  /** Returns the original exception object that was rethrown.
   *
   * @return JsonApi_Exception
   */
  public function getExceptionObject(  )
  {
    return $this->_exception;
  }
}