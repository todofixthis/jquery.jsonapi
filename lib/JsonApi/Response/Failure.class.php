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
    KEY_ERRORS = 'errors';

  /** Init the response object.
   *
   * @param stdClass $response
   *
   * @return void
   */
  protected function _initialize( stdClass $response )
  {
    /* Convert errors into an array. */
    $props = $this->getPropertiesObject();
    $props->set(self::KEY_ERRORS, (array) $props->get(self::KEY_ERRORS));
  }
}