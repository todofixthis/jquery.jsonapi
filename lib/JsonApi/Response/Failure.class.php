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

/** A response from the JsonApi server indicating that one or more of the
 *   parameters it received were invalid or malformed.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.response
 */
class JsonApi_Response_Failure extends JsonApi_Response
{
  const
    ERR_BAD_PARAMETERS = 'Bad parameters: [%s]';

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
    $message =
      is_null($message)
        ? sprintf(
            self::ERR_BAD_PARAMETERS,
            implode(', ', array_keys($this->__get(self::KEY_ERRORS)))
          )
        : (string) $message;

    throw new JsonApi_Response_Exception($message, $this->getResponseObject());
  }

  /** Init the response object.
   *
   * @return void
   * @throws JsonApi_Response_Exception if the response is somehow malformed.
   */
  protected function _initialize(  )
  {
    $this->_importPropertiesFromResponse(JsonApi_Base::STATUS_ERROR);
  }
}