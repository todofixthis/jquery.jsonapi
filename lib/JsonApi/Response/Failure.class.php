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
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.response
 *
 * @property array $errors
 */
class JsonApi_Response_Failure extends JsonApi_Response
{
  const
    KEY_ERRORS = 'errors';

  /** Init the response object.
   *
   * @throws JsonApi_Response_Exception If response JSON is malformed.
   * @return void
   */
  protected function _initialize(  )
  {
    parent::_initialize();

    if( $this->getDecodedJson()->status != self::STATUS_FAIL )
    {
      throw new JsonApi_Response_Exception(sprintf(
        'Received unexpected "%s" status value for failure message.',
          $this->getDecodedJson()->status
      ));
    }

    $this->_initDetail();

    /* Convert errors into an array. */
    $props = $this->getPropertiesObject();
    $props->set(self::KEY_ERRORS, (array) $props->get(self::KEY_ERRORS));
  }

  /** Throws an exception based off the response.
   *
   * @return void
   * @throws JsonApi_Response_RethrownException
   */
  public function throwException(  )
  {
    $key = self::KEY_ERRORS;

    throw new JsonApi_Response_RethrownException(
      $this->getResponseObject(),
      new JsonApi_Response_Exception(sprintf(
        'Bad request parameters (%s).',
          implode(', ', array_keys($this->$key))
      ))
    );
  }
}