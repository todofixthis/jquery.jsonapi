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

/** Interacts with JPUP's Test_Browser to return the HTTP response as a
 *    JsonApi_Response object.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.test.browser.plugin
 */
class Test_Browser_Plugin_JsonApiResponse extends Test_Browser_Plugin
{
  /** Returns the name of the accessor that will invoke this plugin.
   *
   * For example, if this method returns 'getMagic', then the plugin can be
   *  invoked in a test case by calling $this->_browser->getMagic().
   *
   * @return string
   */
  public function getMethodName(  )
  {
    return 'getJsonApiResponse';
  }

  /** Returns a reference to the response content.
   *
   * @return $this
   */
  public function invoke(  )
  {
    if( ! $this->hasEncapsulatedObject() )
    {
      $this->setEncapsulatedObject(JsonApi_Response::factory(
        new JsonApi_Http_Response(
          Zend_Uri::factory($this->getBrowser()->getRequest()->getUri()),
          $this->getBrowser()->getResponse()->getStatusCode(),
          $this->getBrowser()->getResponse()->getContent()
        )
      ));
    }

    return $this;
  }

  /** Returns the class name of the response object (e.g.,
   *    JsonApi_Response_Success).
   *
   * @return string
   */
  public function getType(  )
  {
    return get_class($this->getEncapsulatedObject());
  }

  /** Convenience method for retrieving a sorted array of error keys, used to
   *    verify that all the correct error messages were returned without having
   *    to worry about the order.
   *
   * This only means something when the response is a failure response.
   *
   * @return string[]
   */
  public function getErrorKeys(  )
  {
    $key = JsonApi_Response_Failure::KEY_ERRORS;

    $errors = array_keys((array) $this->getEncapsulatedObject()->$key);
    sort($errors);

    return $errors;
  }
}