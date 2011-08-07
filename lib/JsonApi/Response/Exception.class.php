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

/** An Exception indicating that something is wrong with the response we
 *   received from the JsonApi server.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.response
 */
class JsonApi_Response_Exception extends JsonApi_Exception
{
  const
    PREVIEW_LENGTH = 80;

  protected
    $_response;

  /** Init the class instance.
   *
   * @param string                  $message
   * @param JsonApi_Http_Response $Response
   *
   * @return void
   */
  public function __construct( $message, JsonApi_Http_Response $Response )
  {
    $this->_response = $Response;

    /* Symfony's error handler does not output any information about the
     *  Exception except for its message, so we will try to make the message as
     *  helpful as possible.
     */
    parent::__construct(
      sprintf(
        '[%d] API Server returned error:  %s from %s ("%s")',
          $Response->getStatus(),
          $message,
          $Response->getUri(),
          UtilityClass::truncate($Response->getContent(), self::PREVIEW_LENGTH)
      ),
      $Response->getStatus()
    );
  }

  /** Accessor for $_response.
   *
   * @return JsonApi_Http_Response
   */
  public function getResponseObject(  )
  {
    return $this->_response;
  }

  /** Returns an array with additional information about the exception.
   *
   * @return array
   */
  public function toArray(  )
  {
    return array(
      'response' => $this->getResponseObject()
    );
  }

  /** Return errors from a throwException() call on WidgetApi_Response_Failure.
   *
   * @return array
   */
  public function getErrors(  )
  {
    $decoded = json_decode($this->getResponseObject()->getContent());

    return
      ($decoded and ! empty($decoded->{JsonApi_Response::KEY_ERRORS}))
        ? (array) $decoded->{JsonApi_Response::KEY_ERRORS}
        : array();
  }
}