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

/** A response from the JsonApi server that we can't make heads or tails of.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.response
 */
class JsonApi_Response_Error extends JsonApi_Response
{
  protected
    /** @var JsonApi_Exception */
    $_exception;

  /** Initialize response properties.
   *
   * @return void
   */
  protected function _initialize(  )
  {
    /* Do nothing.  Note that we are overriding the parent _initialize() method,
     *  which does a lot more than nothing.
     */
  }

  /** Attach an exception to the object.
   *
   * @param JsonApi_Exception $exception {@see factory()}
   *
   * @return JsonApi_Response_Error($this)
   */
  public function attachException( JsonApi_Exception $exception )
  {
    $this->_exception = $exception;
    return $this;
  }

  /** Throw the exception that we caught.
   *
   * @return void
   * @throws JsonApi_Response_Exception
   */
  public function throwException(  )
  {
    throw new JsonApi_Response_RethrownException(
      $this->getResponseObject(),
      $this->_exception
    );
  }
}